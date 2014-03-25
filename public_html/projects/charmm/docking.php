<?php

function prjGetHtml($db, $ruRow, $diRow) {
  // project
  $sql = 'SELECT ru_software, ru_project FROM run WHERE ru_id = '.$ruRow['ru_id'];
  $prRow = $db->fetchRow($sql);
  // e.g.) projects/charmm/docking.php
  $project_path = DIRECTORY_SEPARATOR.DIR_PROJECT.DIRECTORY_SEPARATOR.$prRow['ru_software'].DIRECTORY_SEPARATOR.$prRow['ru_project']; // ~~~.php

  $html = '';
  $html .= '<ul>';
  $html .= '<li><a href="'.$project_path.'_show_data.php?run='.$ruRow['ru_id'].'">1. 分割保存された出力ファイルを一括で取得</a></li>';
  $html .= '<li><a href="'.$project_path.'_data.php?run='.$ruRow['ru_id'].'">2. download individual output file</a></li>';
  $html .= '<li><a href="'.$project_path.'_pdb.php?run='.$ruRow['ru_id'].'">3. download result pdb file with limitation of energy</a></li>';
  $html .= '</ul>';
  return $html;
}
