<?php
set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

if((isset($_FILES['out_file']) === true) && (isset($_POST['id']) === true))
{
	if($_FILES['out_file']['error'] == UPLOAD_ERR_OK)
	{
		$num = intval($_POST['id']) / 1000;
		$num = floor($num);
		$num = intval($num) + 1;

		$dirPath = 'out_files';

		if(is_dir($dirPath) === false)
		{
			mkdir($dirPath, 0755);
		}

		$dirPath .= '/'.strval($num);

		if(is_dir($dirPath) === false)
		{
			mkdir($dirPath, 0755);
		}

		move_uploaded_file($_FILES['out_file']['tmp_name'], $dirPath.'/'.$_FILES['out_file']['name']);

		echo 'OK';
	}
	else
	{
		echo 'NG';
	}
}
else
{
	echo 'NG';
}

function InsertRun($db, $fileAry, &$errorMsg)
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

	if($error === false)
	{
		$id = COMGetNextId($db, 'ru_id', 'run');

		$file = file_get_contents($fileAry['tmp_name']);

		$row = array(	'ru_id' => $id,
						'ru_file_name' => $fileAry['name'],
						'ru_original_script' => $file,
						'ru_analyzed_script' => '',
						'ru_divided' => 0,
						'ru_datetime' => date('YmdHis'));

		$db->insert('run', $row);
	}

	return !$error;
}

