<?php

require_once 'autoload.php';
require_once 'vendor/autoload.php';

use Model\RssList;

set_time_limit(0);

$catid = $argv[1];
if (!(isset($argv[1]))) {
    echo "没写参数" . "\n";
    exit();
}

RssList::getNewsInfoByCatId($catid);
