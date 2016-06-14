<?php

require_once 'autoload.php';
require_once 'vendor/autoload.php';

use Core\MySql\Mysql_Model\XmMysqlObj;

set_time_limit(0);

$rssid = $argv[1];
if (!(isset($argv[1]))) {
    echo "没写参数" . "\n";
    exit();
}

$xm_mysql_obj = XmMysqlObj::getInstance();
$query = "select * from rs_category_map where rssid=$rssid ";
$fetch_array = $xm_mysql_obj->fetch_assoc($query);
for ($i = 0; $i < count($fetch_array); $i++) {
    $catid = $fetch_array[$i]['categoryid'];

    exec('php GrapByCatId.php ' . $catid . ' > /dev/null &');

    echo $catid . "\n";
}