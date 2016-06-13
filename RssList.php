<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'XmlList.php';
require_once 'autoload.php';
require_once 'NewsInfo.php';

use Core\MySql\Mysql_Model\XmMysqlObj;

class RssList {

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

    public static function getNewsInfoByCatId($catid) {
        $xm_mysql_obj = XmMysqlObj::getInstance();
        $query = "select * from rs_category_map where categoryid=$catid limit 1";
        $fetch_array = $xm_mysql_obj->fetch_assoc($query);
        $href = $fetch_array[0]['href'];
        $rssid = $fetch_array[0]['rssid'];

        $xml_array = XmlList::getArrayByXml($href);

        for ($i = 0; $i < count($xml_array); $i++) {
            $info = $xml_array[$i];
            $info['catid'] = $catid;
            $info['rssid'] = $rssid;

            $news_info = new NewsInfo($info);
            $news_info->saveToDb();

            $next_page = $news_info;
            while ($next_page = $next_page->nextPage()) {
                $next_page->saveToDb();
            }
        }

        echo $href;
    }

}
