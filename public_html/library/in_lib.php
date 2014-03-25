<?php
include_once 'Zend/Db.php';

function InFileExists($db, $fileName)
{
	$quoteFileName = $db->quote($fileName);
	$sql = 'SELECT COUNT(*) FROM input_file WHERE in_file_name = '.$quoteFileName;
	$row = $db->fetchRow($sql);

	if(intval($row['count']) === 0)
	{
		return false;
	}

	return true;
}
