<?php

/**
 * 卸载钉钉登录
 * Skiychan <dev@skiy.net>
 * https://www.skiy.net/201804025057.html
 */

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "DROP TABLE IF EXISTS `{$tablepre}sq_dduser`";

db_exec($sql);
