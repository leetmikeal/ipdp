<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

$di_id = 0;
if(isset($_GET['di']) === true) { $di_id = intval($_GET['di']); }

// DB接続
$db = DbManager::getConnection();

$runFileName = '';
$mapFileName = '';
$datFileName = '';
$pdbFileName = '';
$minFileName = '';

$ru_id = 0;
if(isset($_GET['run'])) $ru_id = intval($_GET['run']);
$sql = 'SELECT ru_id, ru_file_name FROM run WHERE ru_id ='.$ru_id;
$ruRow = $db->fetchRow($sql);

$sql = 'SELECT * FROM divided_run WHERE di_id = '.$di_id;
$row = $db->fetchRow($sql);

if($row !== false)
{
	$runFileName = 'run_'.$di_id;
	$mapFileName = 'map_'.$di_id;
	$datFileName = 'dat_'.$di_id;
	$pdbFileName = 'pdb_'.$di_id;
	$minFileName = 'min_'.$di_id;
	$outFileName = 'out_'.$di_id;

	$script = GetAnalyzedScript($db, $row['di_ru_id']);
	$script = str_replace('#mapFileName', $mapFileName, $script);
	$script = str_replace('#datFileName', $datFileName, $script);
	$script = str_replace('#pdbFileName', $pdbFileName, $script);
	$script = str_replace('#minFileName', $minFileName, $script);

	$varRow = SpritVariable($row['di_variable']);
	for($i = 0; $i < count($varRow); $i++)
	{
		if(count($varRow[$i]) == 2)
		{
			$script = str_replace('#'.$varRow[$i][0], $varRow[$i][1].'.', $script);
		}
    }

    $sql = 'SELECT ou_di_id, ou_ru_id, ou_file_name FROM output_file WHERE ou_di_id = '.$di_id;
    $out_row = $db->fetchAll($sql);

	$smarty = new Smarty();
	$smarty->left_delimiter = '{{{';
    $smarty->right_delimiter = '}}}';
    $smarty->assign('ruRow',$ruRow);
	$smarty->assign('row', $row);
    $smarty->assign('script', $script);
    $smarty->assign('outRow', $out_row);
    $smarty->display(TEMPLATE.'/divided_run.html');
}

function GetAnalyzedScript($db, $id)
{
	$script = '';

	$sql = 'SELECT * FROM run WHERE ru_id = '.$id;
	$row = $db->fetchRow($sql);
	if($row !== false)
	{
		$script = $row['ru_analyzed_script'];
	}

	return $script;
}

