<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$error = false;
$errorMsg = '';
$file = '';
$fileName = '';
$ioFileAry = array();
$inputFileAry = array();

if(isset($_POST['software'])) {
    $cu_software = $_POST['software'];
    if(!CheckSoftware($cu_software)) {
        COMRedirect(SITE_URL);
    }
} else {
    COMRedirect(SITE_URL);
}
$project = GetProject($cu_software);

if(isset($_FILES['run_file']) == true)
{
	if($_FILES['run_file']['error'] == UPLOAD_ERR_OK)
	{

	}
	else if($_FILES['run_file']['error'] == UPLOAD_ERR_NO_FILE)
	{
		$errorMsg = 'ファイルを選択してください。';
		$error = true;
	}
	else
	{
		$errorMsg = 'ファイルアップロードでエラーが発生しました。';
		$error = true;
	}

	if($error === false)
	{
		$fileName = $_FILES['run_file']['name'];
        $file = file_get_contents($_FILES['run_file']['tmp_name']);
        $file = stripslashes($file);
		$ioFileAry = GetIOFileNameByScript($file);
		$inputFileAry = CheckInputFile($db, $ioFileAry['input']);
	}
}

if((isset($_POST['run_file']) == true) && (isset($_POST['file_name']) == true))
{
	$fileName = $_POST['file_name'];
	$file = stripslashes($_POST['run_file']);
	$ioFileAry = GetIOFileNameByScript($file);
	$inputFileAry = CheckInputFile($db, $ioFileAry['input']);
}

$smarty = new Smarty();
$smarty->left_delimiter = '{{{';
$smarty->right_delimiter = '}}}';
$smarty->assign('error', $error);
$smarty->assign('errorMsg', $errorMsg);
$smarty->assign('cu_software', $cu_software);
$smarty->assign('project', $project);
$smarty->assign('ioFileAry', $ioFileAry);
$smarty->assign('inputFileAry', $inputFileAry);
$smarty->assign('inputFileErrCount', CountInputFileErr($inputFileAry));
$smarty->assign('fileName', $fileName);
$smarty->assign('file', $file);
$smarty->display(TEMPLATE.'/upload_script.html');
