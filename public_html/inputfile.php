<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$errorMsg = '';
$ruAry = array();
$psAry = array();
$executerNum = 0;
$executerAry = array();

if(isset($_FILES['input_file']) == true)
{
	if(InsertInput($db, $_FILES['input_file'], $errorMsg) === true)
	{
		COMRedirect('inputfile.php');
	}
}

#$sql = 'SELECT in_id, in_file_name FROM input_file ORDER BY in_file_name ASC';
$sql = 'SELECT in_id, in_file_name FROM input_file ORDER BY in_id DESC';
$inAry = $db->fetchAll($sql);

$smarty = new Smarty();
$smarty->left_delimiter = '{{{';
$smarty->right_delimiter = '}}}';
$smarty->assign('errorMsg', $errorMsg);
$smarty->assign('inAry', $inAry);
$smarty->display(TEMPLATE.DIRECTORY_SEPARATOR.'inputfile.html');

function InsertInput($db, $fileAry, &$errorMsg)
{
	$error = false;
	$errorMsg = '';

	if($fileAry['error'] == UPLOAD_ERR_OK)
	{

	}
	else if($fileAry['error'] == UPLOAD_ERR_NO_FILE)
	{
		$errorMsg = 'ファイルを選択してください。';
		$error = true;
	}
	else
	{
		$errorMsg = 'ファイルアップロードでエラーが発生しました。';
		$error = true;
	}

	if(InFileExists($db, $fileAry['name']) === true)
	{
		$errorMsg = 'この名前のファイルはすでにアップロードされています。';
		$error = true;
	}

	if($error === false)
	{
		$id = COMGetNextId($db, 'in_id', 'input_file');

        $file = file_get_contents($fileAry['tmp_name']);

        $file = stripslashes($file);

		$row = array(	'in_id' => $id,
						'in_file_name' => $fileAry['name'],
						'in_contents' => $file);

		$db->insert('input_file', $row);
	}

	return !$error;
}

