<?php

namespace Model;

use Core\MySql\Mysql_Model\XmMysqlObj;

/**
 * list操作类 （不推荐，最好是做成实例）
 * 
 * @author         zanuck<ztyzly00@126.com> 
 * @since          1.0 
 */
class RssList {

    /**
     * 根据rssid来抓取新闻（不推荐，速度太慢，尽量用exec模拟多线程）
     * @param type $rssid
     */
    public static function getNewsInfoByRssId($rssid) {
        $xm_mysql_obj = XmMysqlObj::getInstance();
        $query = "select * from rs_category_map where rssid=$rssid ";
        $fetch_array = $xm_mysql_obj->fetch_assoc($query);
        for ($i = 0; $i < count($fetch_array); $i++) {
            $catid = $fetch_array[$i]['categoryid'];
            self::getNewsInfoByCatId($catid);
            echo $catid . "\n";
        }
    }

    /**
     * 根据catid来抓取新闻
     * @param type $catid
     */
    public static function getNewsInfoByCatId($catid) {
        $xm_mysql_obj = XmMysqlObj::getInstance(1);
        $query = "select * from rs_category_map where categoryid=$catid limit 1";
        $row = $xm_mysql_obj->fetch_assoc_one($query);
        $href = $row['href'];
        $rssid = $row['rssid'];
        $xml_array = XmlList::getArrayByXml($href);

        /* 一次性抓取mysql，防止高并发select */
        $query = "select link from rs_news where catid=$catid order by time desc limit 500";
        $link_list = $xm_mysql_obj->fetch_assoc($query);
        for ($i = 0; $i < count($link_list); $i++) {
            for ($j = 0; $j < count($xml_array); $j++) {
                if ($xml_array[$j]['link'] == $link_list[$i]['link']) {
                    unset($xml_array[$j]);
                    $xml_array = array_values($xml_array);
                }
            }
        }

        /* 进程池 */
        $pids = array();

        for ($i = 0; $i < count($xml_array); $i++) {

            $pids[$i] = pcntl_fork();

            switch ($pids[$i]) {
                case -1:
                    echo "fork error:{$i}\r\n";
                    exit;
                case 0: /* 子进程 */
                    $info = $xml_array[$i];
                    $info['catid'] = $catid;
                    $info['rssid'] = $rssid;
                    $news_info = new NewsInfo($info);
                    $news_info->saveToDb();
                    while ($news_info = $news_info->nextPage()) {
                        $news_info->saveToDb();
                    }
                    exit;
                default : /* 父进程 */
                    /* 单catid控制100进程左右,总共2400进程 */
                    if ($i % 1000 == 0) {
                        foreach ($pids as $i => $pid) {
                            if ($pid) {
                                pcntl_waitpid($pid, $status);
                                unset($pids[$i]);
                            }
                        }
                    }
                    break;
            }
        }

        foreach ($pids as $i => $pid) {
            if ($pid) {
                pcntl_waitpid($pid, $status);
            }
        }
    }

}
