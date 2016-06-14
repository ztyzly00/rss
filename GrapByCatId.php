<?php

require_once 'NewsInfo.php';
require_once 'RssList.php';

set_time_limit(0);

$catid = $argv[1];
if (!(isset($argv[1]))) {
    echo "没写参数" . "\n";
    exit();
}

RssList::getNewsInfoByCatId($catid);
