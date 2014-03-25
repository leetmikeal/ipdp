#!/usr/bin/php
<?php

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$ru_id = 0;
$loopAry = array();
$comboAry = array();
$di_variable = '';

for(;;)
{
	$sql = 'SELECT * FROM run WHERE ru_divided = 0';
	$row = $db->fetchRow($sql);
	if($row !== false)
	{
		$index1 = 0;
		$index2 = 0;
		$ru_id = $row['ru_id'];
    $ru_out = intval($row['ru_out']);
		$loopAry = AnalyzedAndVarCreate($row['ru_original_script']);

		$comboAry = array();
		$db->beginTransaction();
		try
		{

			GetComboAry($loopAry, $index1, array(), $comboAry);

			for($i = 0; $i < count($comboAry); $i++)
			{
				$di_variable = '';
				for($k = 0; $k < count($comboAry[$i]); $k++)
				{
					$di_variable .= $comboAry[$i][$k]['name'].'='.$comboAry[$i][$k]['value'].' ';
				}
				$di_variable = trim($di_variable);

				InsertDividedRun($db, $ru_id, $di_variable, $ru_out);
			}

			UpdateDivided($db, $ru_id, $row['ru_original_script']);

			$db->commit();
		}
		catch(Exception $e)
		{
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	else
	{
		sleep(60);
	}
}

function InsertDividedRun($db, $ru_id, $di_variable, $ru_out)
{
	$di_id = COMGetNextId($db, 'di_id', 'divided_run');
	$row = array(	'di_id' => $di_id,
					'di_ru_id' => $ru_id,
					'di_status' => 0,
					'di_start_datetime' => '',
					'di_end_datetime' => '',
					'di_variable' => $di_variable,
          'di_out' => $ru_out);

	$db->insert('divided_run', $row);
}

function UpdateDivided($db, $id, $ru_analyzed_script)
{
	$upRow = array(	'ru_divided' => 1,
					'ru_analyzed_script' => $ru_analyzed_script);
	$db->update('run', $upRow, 'ru_id = '.$id);
}

function AnalyzedAndVarCreate(&$script)
{
	$loopAry = array();

	$fileAry = explode("\n", $script);
	$script = '';

	for($i = 0; $i < count($fileAry); $i++)
    {
        $row = $fileAry[$i];
        $pos = strpos($row, '!');
        //var_dump($pos);
        if($pos !== false)
        {
            $row = substr($tempStr, 0, $pos);
        }
		$pos = strpos($row, '##');

		if($pos !== false)
		{
			$valueAry = array();
            
			$row = substr($row, $pos);

			$ary = explode(' ', $row);

			$variableName = str_replace('##', '', $ary[0]);

			if(count($ary) == 1)
			{

			}
			else if(count($ary) == 2)
			{
				$valueAry[] = intval($ary[1]);

				$loopAry[] = array(	'name' => $variableName, 'value' => $valueAry);
				$fileAry[$i] = ReplaceMarkToVariable($fileAry[$i], $variableName);
			}
			else if(count($ary) >= 3)
			{
				if($ary[2] == 'to')
				{
					$valueAry = CreateLoopValue($ary);
					if($valueAry !== false)
					{
						$loopAry[] = array(	'name' => $variableName, 'value' => $valueAry);
						$fileAry[$i] = ReplaceMarkToVariable($fileAry[$i], $variableName);
					}
				}
				else
				{
					for($k = 1; $k < count($ary); $k++)
					{
						$valueAry[] =  intval($ary[$k]);
					}
					$loopAry[] = array(	'name' => $variableName, 'value' => $valueAry);
					$fileAry[$i] = ReplaceMarkToVariable($fileAry[$i], $variableName);
				}
			}
			else
			{

			}
		}

		$script .= $fileAry[$i]."\n";
	}

	return $loopAry;
}

function CreateLoopValue($ary)
{
	if(count($ary) < 6)
	{
		return false;
	}

	if($ary[4] != 'step')
	{
		return false;
	}

	if(intval($ary[5]) === 0)
	{
		return false;
	}

	$valueAry = array();
	$from = intval($ary[1]);
	$to = intval($ary[3]);
	$step = intval($ary[5]);

	while($from <= $to)
	{
		$valueAry[] = $from;
		$from += $step;
	}

	return $valueAry;
}

function ReplaceMarkToVariable($row, $variableName)
{
	$pos = strpos($row, '##');
	$mark = substr($row, $pos);
	return str_replace($mark, $variableName.' #'.$variableName, $row);
}

function GetComboAry(&$ary, $index1, $inComboAry, &$outComboAry)
{
	if($index1 >= count($ary))
	{
		$outComboAry[] = $inComboAry;
		return;
	}

	for($i = 0; $i < count($ary[$index1]['value']); $i++)
	{
		$inComboAry[$index1] = array('name' => $ary[$index1]['name'], 'value' => $ary[$index1]['value'][$i]);
		GetComboAry($ary, $index1 + 1, $inComboAry, $outComboAry);

	}
}

