<?php /* Smarty version 2.6.26, created on 2013-03-08 21:56:03
         compiled from ../../../templates/default/header.tpl */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="ja" />
<title><?php echo $this->_tpl_vars['title']; ?>
</title>
<link href="<?php echo @TEMPLATE_URL; ?>
main.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="wrapper">
    <div id="header">
        <div class="inner">
            <h1><img src="<?php echo @TEMPLATE_URL; ?>
images/titlelogo.gif" alt="Input File Preprocesser and Distributed Processing" /></h1>
            <div id="headnavi">
                <ul>
                    <?php $this->assign("naviclass_".($this->_tpl_vars['menu']), ' class="active"'); ?>
                    <li><a href="<?php echo @SITE_URL; ?>
"<?php echo $this->_tpl_vars['naviclass_1']; ?>
><span><span>トップ</span></span></a></li>
                    <li><a href="<?php echo @SITE_URL; ?>
new.php"<?php echo $this->_tpl_vars['naviclass_2']; ?>
><span><span>新規プロジェクト</span></span></a></li>
                    <li><a href="<?php echo @SITE_URL; ?>
inputfile.php"<?php echo $this->_tpl_vars['naviclass_3']; ?>
><span><span>入力ファイル</span></span></a></li>
                    <li><a href="<?php echo @SITE_URL; ?>
configure.php"<?php echo $this->_tpl_vars['naviclass_4']; ?>
><span><span>環境設定</span></span></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div id="middle">