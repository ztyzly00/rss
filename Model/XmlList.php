<?php

namespace Model;

/**
 * xml操作类
 * 
 * @author         zanuck<ztyzly00@126.com> 
 * @since          1.0 
 */
class XmlList {

    /**
     * 读取xml的url将xml转换为数组
     */
    public static function getArrayByXml($url) {
        $xml_string = file_get_contents($url);
        $xml_obj = simplexml_load_string($xml_string);
        $return_array = array();

        foreach ($xml_obj->channel->item as $news) {
            $news_info['link'] = (string) $news->link;
            $news_info['time'] = strtotime(str_replace("GMT", "", $news));
            $news_info['description'] = (string) $news->description;
            $desa = (string) ($news->title) . "\n";
            $title = preg_replace("/<\/{0,1}a[^>]*>/", "", $desa);
            $news_info['title'] = $title;
            $return_array[] = $news_info;
        }

        return $return_array;
    }

}
