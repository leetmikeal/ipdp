<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

$smarty = new Smarty();
$smarty->left_delimiter = '{{{';
$smarty->right_delimiter = '}}}';
$smarty->display(TEMPLATE.'/configure.html');
