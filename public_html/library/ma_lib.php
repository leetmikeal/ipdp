<?php
set_time_limit(0);

// include path設定
set_include_path(get_include_path().PATH_SEPARATOR.'library');
set_include_path(get_include_path().PATH_SEPARATOR.'config');

// 共通関数読み込み
include_once('Common.php');

// DB接続
$db = DbManager::getConnection();

$showRun = false;
$ru_id = NULL; 
if(isset($_GET['run']) === true) {
    $showRun = true;
    $ru_id = intval($_GET['run']);
}
$type = 'csv';
if(isset($_GET['type'])) $type = $_GET['type'];
$form = 1;
if(isset($_GET['form'])) $form = $_GET['form'];


$ma_file_name = 'cta-energy';

if($showRun === false) {
	COMRedirect('/');
} else {
    $temp_name = tempnam("data_files", "df");
    $mf_handle = fopen($temp_name, "w");

    $sql = 'SELECT di_id, di_variable, di_min FROM divided_run WHERE di_ru_id = '.$ru_id.'ORDER BY di_id ASC';
    $diRow = $db->fetchAll($sql);

    switch($type) {
    case 'csv':
        switch($form) {
        case 1:
            $ma_file_name .= '_row';
            $varValue = "di_id,";
            $varCol = explode(" ", $diRow[0]['di_variable']);
            for($i = 0; $i < count($varCol); $i++) {
                $varArr = explode("=", $varCol[$j]);
                $varValue .= $varArr[0].",";
            }
            $varValue .= "di_min";
            fwrite($mf_handle, $ma_buf."\n");
            for($i = 0; $i < count($diRow); $i++) {
                $varCol = explode(" ", $diRow[$i]['di_variable']);
                $varValue = '';
                for($j = 0; $j < count($varCol); $j++) {
                    $varArr = explode("=",$varCol[$j]);
                    $varValue .= $varArr[1].',';
                }
                //$varValue = substr($varValue, 1, strlen($vaValue)-1);
                //$diRow[$i]['di_variable'].",".
                $ma_buf = $diRow[$i]['di_id'].",".
                    $varValue.
                    $diRow[$i]['di_min'];
                fwrite($mf_handle, $ma_buf."\n");
            }
            break;
        case 2:
            $ma_file_name .= '_table';
            $varValue = array();
            for($i = 0; $i < count($diRow); $i++) {
                $varCol = explode(" ", $diRow[$i]['di_variable']);
                for($j = 0; $j < 2; $j++) {
                    $varArr = explode("=", $varCol[$j]);
                    $parArr[$j][$i] = $varArr[1];
                }
            }
            for($j = 0; $j < 2; $j++) {
                $parArrCon[$j] = array_unique($parArr[$j]);
                sort($parArrCon[$j], SORT_NUMERIC);
                $parMax[$j] = count($parArrCon[$j]);
            }

            $col[0][0] = '';
            for($l = 0; $l <= $parMax[1]; $l++) {
                $col[$l+1][0] = $parArrCon[1][$l];
            }
            for($k = 0; $k <= $parMax[0]; $k++) {
                $col[0][$k+1] = $parArrCon[0][$k];
            }
            $i = 0;
            for($l = 1; $l <= $parMax[0]; $l++) {
                for($k = 1; $k <= $parMax[1]; $k++) {
                    $col[$l][$k] = $diRow[$i]['di_min'];
                    //$col[$l][$k] = "";
                    $i++;
                }
            }
            for($l = 0; $l <= $parMax[0]; $l++) {
                $ma_buf = implode(",",$col[$l]);
                fwrite($mf_handle, $ma_buf."\n");
            }
            break;
        default:
            break;
        }
        break;
    default:
        break;
    }
    fclose($mf_handle);

    $ma_file_name .= ".".$type;
    $file = $temp_name;
    if (file_exists($file)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='.$ma_file_name);
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      ob_clean();
      flush();
      readfile($file);
      exit();
    }
    unlink($mf_handle);
}

