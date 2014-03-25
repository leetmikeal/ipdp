<?php
include_once 'Zend/Db.php';

function DiGetAllCount($db, $id)
{
	$sql = 'SELECT COUNT(*) FROM divided_run WHERE di_ru_id = '.$id;
	$row = $db->fetchRow($sql);
	return intval($row['count']);
}

function DiGetCountByStatus($db, $id, $status)
{
	$sql = 'SELECT COUNT(*) FROM divided_run WHERE di_ru_id = '.$id.' AND di_status = '.$status;
	$row = $db->fetchRow($sql);
	return intval($row['count']);
}

function DiGetAllTime($db, $id)
{
	$sql = 'SELECT SUM(di_calc_time) FROM divided_run WHERE di_ru_id = '.$id.' AND di_status = 2';
	$row = $db->fetchRow($sql);
	return intval($row['sum']);
}

function DiGetFirstStartDateTime($db, $id)
{
	$sql = 'SELECT * FROM divided_run WHERE di_ru_id = '.$id.' AND di_start_datetime != \'\' ORDER BY di_start_datetime ASC';
	$row = $db->fetchRow($sql);
	if($row !== false)
	{
		return $row['di_start_datetime'];
	}
	return '';
}

function DiGetLastEndDateTime($db, $id)
{
	$sql = 'SELECT * FROM divided_run WHERE di_ru_id = '.$id.' AND di_end_datetime != \'\' ORDER BY di_end_datetime DESC';
	$row = $db->fetchRow($sql);
	if($row !== false)
	{
		return $row['di_end_datetime'];
	}
	return '';
}

