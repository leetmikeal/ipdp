<?php /* Smarty version 2.6.26, created on 2013-03-08 21:56:03
         compiled from map_select.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'map_select.html', 7, false),array('modifier', 'escape', 'map_select.html', 24, false),array('modifier', 'nl2br', 'map_select.html', 24, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../templates/".(@TEMPLATE)."/header.tpl", 'smarty_include_vars' => array('title' => "docking data show | ".($this->_tpl_vars['ruRow']['ru_file_name']),'menu' => '0')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <h2>出力ファイルを選択して集計を開始</h2>
    <?php if ($this->_tpl_vars['showMode'] == false): ?>
    <?php if ($this->_tpl_vars['fileTypeFlag'] == true): ?>
    <div class="container">
    <?php if (count($this->_tpl_vars['ou_file_names']) > 0): ?>
        <table>
            <tr><th>出力ファイルの一覧</th><th>分割数</th><th>集計</th></tr>
        <?php $_from = $this->_tpl_vars['ou_file_names']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file']):
?>
        <tr>
            <td><?php echo $this->_tpl_vars['file']['name']; ?>
<br /><a href="map_select.php?run=<?php echo $this->_tpl_vars['ru_id']; ?>
&type=<?php echo $this->_tpl_vars['fileType']; ?>
&filename=<?php echo $this->_tpl_vars['file']['name']; ?>
">プレビュー</a></td>
            <td><?php echo $this->_tpl_vars['file']['count']; ?>
</a></td>
            <td>
              <form method="get" action="map_starter.php">
              Energy Column (0 start): <input type="text" name="column" id="column" value="10">
              <input type="hidden" name="file" id="file" value="<?php echo $this->_tpl_vars['file']['name']; ?>
" />
              <input type="hidden" name="run" id="run" value="<?php echo $this->_tpl_vars['ru_id']; ?>
" />
              <input type="submit" value="集計開始" />
              </form>
            </td>
        </tr>
        <tr>
          <td colspan="4" class="script"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['file']['contents'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
        </tr>
    <?php endforeach; endif; unset($_from); ?>
    </table>
    <?php else: ?>
    この拡張子を持つ重複したファイル名の出力ファイルが見つかりませんでした。<br />
    別の拡張子を選択してください。<br />
    <a href="map_select.php?run=<?php echo $this->_tpl_vars['ru_id']; ?>
">＜戻る</a>
    <?php endif; ?>
        </div>
    <?php else: ?>
    <div class="container">
        <table>
            <tr><th>拡張子一覧</th></tr>
            <?php $_from = $this->_tpl_vars['fileExtArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ext'] => $this->_tpl_vars['count']):
?>
            <tr><td><a href="map_select.php?run=<?php echo $this->_tpl_vars['ru_id']; ?>
&type=<?php echo $this->_tpl_vars['ext']; ?>
"><?php echo $this->_tpl_vars['ext']; ?>
(<?php echo $this->_tpl_vars['count']; ?>
)</a></td></tr>
            <?php endforeach; endif; unset($_from); ?>
        </table>
    </div>
            <?php endif; ?>
    <?php else: ?>
    <div class="script">
    <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['ou_file_name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

    </div>
    <div class="script">
        <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['ou_contents'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

    </div>
    <?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../templates/".(@TEMPLATE)."/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
