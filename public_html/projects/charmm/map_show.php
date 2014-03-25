<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$ru_id = 0;
if(isset($_GET['run']) === true) { $ru_id = intval($_GET['run']); }

$varAry = array();

$ruRow = getRunInfo($db, $ru_id);
//$template_path = "..".DIRECTORY_SEPARATOR.DIR_PROJECT.DIRECTORY_SEPARATOR.$ruRow['ru_software'].DIRECTORY_SEPARATOR."templates";
$template_path = "templates";

if($ruRow !== false)
{
	$sql = 'SELECT * FROM divided_run WHERE di_ru_id ='.$ru_id.'ORDER BY di_id ASC';
	$diRow = $db->fetchAll($sql);
	if($diRow !== false)
	{
		$varAry = SpritVariable($diRow[0]['di_variable']);
	}

//	$sql = 'SELECT * FROM map WHERE ma_ru_id ='.$ru_id.' ORDER BY ma_id ASC';
//	$diRow = $db->fetchAll($sql);
//	if($maRow === false)
//	{
//		$maRow = array();
//	}

	// 全件数
	$alldi = DiGetAllCount($db, $ru_id);

	// 終了件数
	$enddi = DiGetCountByStatus($db, $ru_id, 2);

	// 総計算時間
	$allTime = DiGetAllTime($db, $ru_id);

	// 計算開始時間
	$firstStartDateTime = DiGetFirstStartDateTime($db, $ru_id);

	// 最新計算終了時間
	$lastEndDateTime = DiGetLastEndDateTime($db, $ru_id);

	// 計算開始からの経過時間
	$keikaTime = COMGetDateTimeOffset($firstStartDateTime, $lastEndDateTime);

	if($enddi == 0)
	{
		$averageTime = 0;	// 平均計算時間
		$averageKeikaTime = 0;	// 平均経過時間
		$nokoriKeikaTime = 0;
	}
	else
	{
		$averageTime = $allTime / $enddi;	// 平均計算時間
		$averageKeikaTime = round($keikaTime / $enddi);	// 平均経過時間
		$nokoriKeikaTime = ($alldi - $enddi) * $averageKeikaTime;
		$endDateTime = date('YmdHis', (mktime() + $nokoriKeikaTime));
	}

	// 残り計算時間
	$estimateTime = round(($alldi - $enddi) * $averageTime) + ($alldi - $enddi);

	$smarty = new Smarty();
	$smarty->left_delimiter = '{{{';
	$smarty->right_delimiter = '}}}';
	$smarty->assign('ru_id', $ru_id);
	$smarty->assign('ruRow', $ruRow);
	$smarty->assign('alldi', $alldi);
	$smarty->assign('allTime', $allTime);
	$smarty->assign('averageTime', $averageTime);
	$smarty->assign('estimateTime', $estimateTime);
	$smarty->assign('nokoriKeikaTime', $nokoriKeikaTime);
	$smarty->assign('endDateTime', $endDateTime);
	$smarty->assign('enddi', $enddi);
//	$smarty->assign('maRow', $maRow);
	$smarty->assign('diRow', $diRow);
	$smarty->assign('varAry', $varAry);
  //$smarty->display($template_path.'/map_show.html');
  $smarty->display('map_show.html');
}

