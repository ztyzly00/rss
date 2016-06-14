<?php

require_once 'NewsInfo.php';
require_once 'RssList.php';

set_time_limit(0);

//RSS::getArrayByXml("http://www.xinhuanet.com/politics/news_politics.xml");
//$news_info = new NewsInfo("http://news.xinhuanet.com/politics/2016-06/07/c_1119005849.htm",1,1);

$info['link'] = 'http://news.xinhuanet.com/world/2016-06/08/c_129046320_22.htm';
$info['catid'] = 1;
$info['time'] = '123123123';
$info['title'] = 'title';
$info['rssid'] = 2;

RssList::getNewsInfoByCatId(1);

//RssList::getNewsInfoByRssId(1);
//print_r(XmlList::getArrayByXml("http://www.xinhuanet.com/politics/news_politics.xml"));
//$news_info = new NewsInfo($info);
//$news_info->grabHtml();
//
//$next_page = $news_info->nextPage();
//
//if ($next_page) {
//    echo "f";
//}
//
//print_r($next_page);
//$next_page->grabHtml();
//$next_page->printInfo();
//$news_info->printInfo();
//$news_info->saveToDb();
?>