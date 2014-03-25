<?php
set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'../../library');
set_include_path(get_include_path().PATH_SEPARATOR.'../../config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$ru_id = 0;
if(isset($_GET['run']) === true) { $ru_id = intval($_GET['run']); }

$ou_file_name = '';
if(isset($_GET['file']) === true) { $ou_file_name = $_GET['file']; }
//$ou_file_name = 'cta-energy.dat';

$ncolumn = null;
if(isset($_GET['column']) === true && isNumber($_GET['column'])) { 
  $ncolumn = intval($_GET['column']);
}

$varAry = array();
$argStr = ' '.$ru_id;


$sql = 'SELECT * FROM divided_run WHERE di_ru_id ='.$ru_id.'ORDER BY di_id ASC';
$diRow = $db->fetchAll($sql);
//print_r($diRow);
//$sql = 'SELECT COUNT(*) FROM divided_run WHERE di_min = \'\' and di_ru_id = '.$ru_id;
//$chkRow = $db->fetchRow($sql);

//if($diRow != false && $chkRow['count'] != 0)
if($diRow == false || empty($ncolumn)) {
  exit();
} else {
    $ener_array = array();
    for($i = 0; $i < count($diRow); $i++) {
      if($diRow[$i]['di_id'] != "") {
        $sql = 'SELECT ou_contents FROM output_file WHERE ou_ru_id = '.$ru_id.' AND ou_file_name = \''.$ou_file_name.'\' AND ou_di_id = '.$diRow[$i]['di_id'];
        $ouRow = $db->fetchRow($sql);
        $ou_buf = $ouRow['ou_contents'];
        $ou_buf = trim($ou_buf);
        $ou_buf = trim($ou_buf, '\n');

        $ener_buf = 100000; 
        if($ou_buf != '') { // no empty mean outfile exist
            $ou_row = explode("\n",$ou_buf);
            for($j = 0; $j < count($ou_row); $j++) {
                $ou_col = split(" +", trim($ou_row[$j]));
                $ou_col_float = floatval($ou_col[$ncolumn]); // ncolumn come from url
                //echo $ou_col_float;
                if($ener_buf > $ou_col_float) $ener_buf = $ou_col_float;
            }
        }
        $where = $db->quoteInto('di_id = ?', $diRow[$i]['di_id']);
        $update = array('di_min' => $ener_buf);
        //echo $i."===".$where."===".$ener_buf."<br />";
        $db->update('divided_run', $update, $where);
      }
    }
    
    $varAry = SpritVariable($diRow['di_variable']);

	for($i = 0; $i < count($varAry); $i++)
	{
		if(isset($_GET[$varAry[$i][0]]) === true)
		{
			$argStr .= ' '.$varAry[$i][0];
		}
	}

	//chdir('exe');
    //$exe = './maper.php'.$argStr.' > maper_out &';
	//exec($exe);
	//chdir('..');

	COMRedirect('map_show.php?run='.$ru_id);
//	$smarty = new Smarty();
//	$smarty->left_delimiter = '{{{';
//	$smarty->right_delimiter = '}}}';
//	$smarty->display(TEMPLATE.'/maper_starter.html');
}
