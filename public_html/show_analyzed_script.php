<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$id = 0;
if(isset($_GET['id']) == true) { $id = intval($_GET['id']); }

$sql = 'SELECT * FROM run WHERE ru_id = '.$id;
$row = $db->fetchRow($sql);
if($row !== false)
{
	$smarty = new Smarty();
	$smarty->left_delimiter = '{{{';
	$smarty->right_delimiter = '}}}';
	$smarty->assign('file_name', '解析済 '.$row['ru_file_name']);
	$smarty->assign('script', $row['ru_analyzed_script']);
    $smarty->display(TEMPLATE.'/show_script.html');
}

