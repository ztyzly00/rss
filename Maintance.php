<?php

require_once 'autoload.php';

use Core\MySql\Mysql_Model\XmMysqlObj;

$xm_mysql_obj = XmMysqlObj::getInstance();

$time = time();
$time = $time - (86400 * 3);

$query = "delete from rs_news where time < $time";

$xm_mysql_obj->exec_query($query);
