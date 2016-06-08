<?php

require_once 'vendor/autoload.php';

use Goutte\Client;

class RSS {

    public static $news_array;
    public static $content;

    /**
     * 从xml的url中获取xml并获取相应内容放入数组
     * @param type $url
     * @return array 返回带有基本无内容信息数组
     */
    public static function getArrayByXml($url) {
        $xml_string = file_get_contents($url);
        $xml_obj = simplexml_load_string($xml_string);
        $return_array = array();
        foreach ($xml_obj->channel->item as $news) {
            $news_info['link'] = (string) $news->link;
            $news_info['time'] = strtotime(str_replace("GMT", "", $news));
            $desa = (string) ($news->title) . "\n";
            $title = preg_replace("/<\/{0,1}a[^>]*>/", "", $desa);
            $news_info['title'] = $title;
            $return_array[] = $news_info;
        }
        self::$news_array = $return_array;
    }

    /**
     * 根据url获取新闻内容信息
     * @param type $url
     */
    public static function getContent($url) {
        $client = new Client();
        $crawler = $client->request('GET', "http://news.xinhuanet.com/politics/2016-06/07/c_1119005849.htm");
        $crawler->filter('.article')->each(function($node) {
            self::$content = $node->html();
        });
        return self::$content;
    }

}
