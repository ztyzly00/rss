<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'RSS.php';
require_once 'NewsInfo.php';
require_once 'RssList.php';

//RSS::getArrayByXml("http://www.xinhuanet.com/politics/news_politics.xml");
//$news_info = new NewsInfo("http://news.xinhuanet.com/politics/2016-06/07/c_1119005849.htm",1,1);

$info['link'] = 'http://news.xinhuanet.com/world/2016-06/08/c_1119011691.htm';
$info['catid'] = 1;
$info['time'] = '123123123';
$info['title'] = 'title';
$info['rssid'] = 2;
$info['description'] = "fff";

$news_info = new NewsInfo($info);

$news_info->printInfo();
