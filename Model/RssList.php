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
        $xm_mysql_obj = XmMysqlObj::getInstance();
        $query = "select * from rs_category_map where categoryid=$catid limit 1";
        $row = $xm_mysql_obj->fetch_assoc_one($query);
        $href = $row['href'];
        $rssid = $row['rssid'];

        $xml_array = XmlList::getArrayByXml($href);

        for ($i = 0; $i < count($xml_array); $i++) {
            $info = $xml_array[$i];
            $info['catid'] = $catid;
            $info['rssid'] = $rssid;

            $query = "select link from rs_news where link='{$info['link']}'";
            $num_rows = $xm_mysql_obj->num_rows($query);

            //不重复链接的新闻才抓取
            if (!$num_rows) {
                $news_info = new NewsInfo($info);
                $news_info->saveToDb();
                while ($news_info = $news_info->nextPage()) {
                    $news_info->saveToDb();
                }
            }
        }
    }

}
