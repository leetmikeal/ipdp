<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$limit = 1000;

$ru_id = 0;
if(isset($_GET['run']) === true) { $ru_id = intval($_GET['run']); }

$offset = 0;
if(isset($_GET['offset']) === true) { $offset = intval($_GET['offset']); }
if($offset < 0)
{
  $offset = 0;
}

$sql = 'SELECT ru_id, ru_software, ru_project, ru_file_name, ru_datetime, ru_out FROM run WHERE ru_id ='.$ru_id;
$ruRow = $db->fetchRow($sql);
if($ruRow !== false)
{
  $sql = 'SELECT di_id, di_start_datetime, di_end_datetime, di_calc_time, di_variable, di_min FROM divided_run WHERE di_ru_id ='.$ru_id.' ORDER BY di_id ASC LIMIT '.$limit.' OFFSET '.$offset;
  $diRow = $db->fetchAll($sql);

  for($i = 0; $i < count($diRow); $i++)
  {
    $diRow[$i]['no'] = $offset + $i + 1;
  }

  $prevOffset = $offset - $limit;
  $nextOffset = $offset + $limit;

  // 全件数
  $alldi = DiGetAllCount($db, $ru_id);

  // 終了件数
  $enddi = DiGetCountByStatus($db, $ru_id, 2);
  $runningdi = DiGetCountByStatus($db, $ru_id, 1);

  // 総計算時間
  $passCpuTime = DiGetAllTime($db, $ru_id);

  // 計算開始時間
  $firstStartDateTime = DiGetFirstStartDateTime($db, $ru_id);

  // 最新計算終了時間
  $lastEndDateTime = DiGetLastEndDateTime($db, $ru_id);

  // 計算開始からの経過時間
  $passTime = COMGetDateTimeOffset($firstStartDateTime, $lastEndDateTime);

  if($enddi == 0)
  {
    $averageTime = 0;  // 平均計算時間
    $averageKeikaTime = 0;  // 平均経過時間
    $estimateTime = 0;
    $endDateTime = 0;
    $passTime = 0;
    $allTime = 0;
  }
    elseif($enddi == $alldi)  
  {
    $averageTime = $passCpuTime / $enddi;  // 平均計算時間
    $averageKeikaTime = round($passTime / $enddi);  // 平均経過時間
    $estimateTime = ($alldi - $enddi) * $averageKeikaTime;
    $endDateTime = $lastEndDateTime;
    $passTime = 0;
    $allTime = COMGetDateTimeOffset($firstStartDateTime, $lastEndDateTime);
  }
  else
  {
    $averageTime = $passCpuTime / $enddi;  // 平均計算時間
    $averageKeikaTime = round($passTime / $enddi);  // 平均経過時間
    $estimateTime = ($alldi - $enddi) * $averageKeikaTime;
    $endDateTime = date('YmdHis', (time() + $estimateTime));
    $allTime = $passTime + $estimateTime;
  }

  // 残り計算時間
  $estimateCpuTime = ($alldi - $enddi) * $averageTime;

  // project
  $sql = 'SELECT ru_software, ru_project FROM run WHERE ru_id = '.$ru_id;
  $prRow = $db->fetchRow($sql);
  // e.g.) projects/charmm/docking.php
  $project_path = DIR_PROJECT.DIRECTORY_SEPARATOR.$prRow['ru_software'].DIRECTORY_SEPARATOR.$prRow['ru_project'].'.php';
  $project_html = '';

  if(file_exists($project_path) && !is_dir($project_path)) {
    require_once($project_path);
    $project_html = prjGetHtml($db, $ruRow, $diRow);
    $project_html = "This project is '".$project_path."'. <br />".$project_html;
  } else {
    $project_html = 'This project does not have HTML statement.<br />';
  }
  /*
  $sql = 'SELECT COUNT(*) FROM map WHERE ma_ru_id ='.$ru_id;
  $row = $db->fetchRow($sql);
  $maCount = intval($row['count']);
    */
  $pageNum = floor($alldi / 1000) + 1;
  $pageNum = intval($pageNum);
  $linkOffsetAry = array();
  for($i = 0; $i < $pageNum; $i++)
  {
    $linkOffsetAry[] = array(  'page' => $i + 1,
                  'offset' => $i * 1000 );
  }

  $smarty = new Smarty();
  $smarty->left_delimiter = '{{{';
  $smarty->right_delimiter = '}}}';
  $smarty->assign('ru_id', $ru_id);
  $smarty->assign('offset', $offset);
  $smarty->assign('prevOffset', $prevOffset);
  $smarty->assign('nextOffset', $nextOffset);
  $smarty->assign('ruRow', $ruRow);
  $smarty->assign('alldi', $alldi);
  $smarty->assign('passCpuTime', $passCpuTime);
  $smarty->assign('estimateCpuTime', $estimateCpuTime);
  $smarty->assign('averageTime', $averageTime);
  $smarty->assign('passTime', $passTime);
  $smarty->assign('estimateTime', $estimateTime);
  $smarty->assign('endDateTime', $endDateTime);
  $smarty->assign('allTime', $allTime);
  $smarty->assign('enddi', $enddi);
  $smarty->assign('runningdi', $runningdi);
  $smarty->assign('diRow', $diRow);
  #$smarty->assign('maCount', $maCount);
  #$smarty->assign('varAry', $varAry);
  $smarty->assign('linkOffsetAry', $linkOffsetAry);
  $smarty->assign('projectHtml', $project_html);
  $smarty->display(TEMPLATE.'/run.html');
}

