<?php
/**
 * Buran_012
 * v1.0
 * 11.12.2017
 * Delta
 * sergey.it@delta-ltd.ru
 *
 * .htaccess
 * php_value auto_prepend_file /.../_buran/__012.php
 */

if(is_array($_POST) && count($_POST))
{
	$droot= dirname(__FILE__);
	if( ! @file_exists($droot.'/b012/'))
		@mkdir($droot.'/b012/', 0777, true);
	$fp= @fopen($droot.'/b012/'.$_SERVER['REMOTE_ADDR'], 'ab');
	if($fp)
	{
		@fwrite($fp, "\n".'= '.date('d.m.Y, H:i:s').' ='."\n");
		@fwrite($fp, "\t".'+ '.$_SERVER['REQUEST_URI']."\n");
		foreach($_POST AS $k => $v)
			@fwrite($fp, "\t| ".$k.' => '.$v."\n");
		@fclose($fp);
	}
}
