<?php
// Buran_010
$ver= 'v.1.47';
// 24.08.2017
// Буран
//====================================================================================================
	//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('display_errors', 'off');
	                    
//====================================================================================================
	define('_DS', DIRECTORY_SEPARATOR);
	$host= str_replace('www.', '', $_SERVER['HTTP_HOST']);
	$root= __FILE__;
	$scriptname= $_SERVER['SCRIPT_NAME'];
	if(strpos($root, "\\") !== false) $scriptname= str_replace("/", "\\", $scriptname);
	$root= str_replace($scriptname, '', $root);
	if(substr($root, strlen($root)-1, 1) == _DS) $root= substr($root, 0, -1);
	if(isset($_GET['dir'])) $dir= trim(urldecode($_GET['dir'])); else $dir= '';
		if(substr($dir, 0, 1) != _DS) $dir= _DS.$dir;
		if(substr($dir, strlen($dir)-1, 1) == _DS) $dir= substr($dir, 0, -1);
	$root_dir= $root.$dir;
	
	$ww= @file_get_contents('http://fndelta.gavrishkin.ru/__password__002.php?host='.$host.'&w='.$_GET['w']);
	if( ! $ww || $_GET['w'] == '' || $_GET['w'] != $ww ){ print '[[FAIL]]'; exit(); }
//====================================================================================================
	define('_NR', "\n");
	define('_BR', "<br />");
	define('_EXT', '/.php/.htaccess/');
	$action= $_GET['act'];
	$flaggo= (isset($_GET['go'])?true:false);
//====================================================================================================
	$fp= $root._DS.'_buran'._DS.'b010'._DS;
	if( ! file_exists($fp)) mkdir($fp, 0777, true);
	$htaccess= '#buran-------------'._NR.'Order Deny,Allow'._NR.'Deny from all'._NR.'#buran-------------'._NR;
	$fh= fopen($root._DS.'_buran'._DS.'b010'._DS.'.htaccess', 'w');
	fwrite($fh, $htaccess);
	fclose($fh);
//====================================================================================================

logs($ver.' ------------------------------------------------------------------');

$ignore[]= "/^\/_buran\/__[0-9]{3}\.php$/";
$ignore[]= "/^\/cache\/(.*)\.html\.php$/";
$ignore[]= "/^\/core\/cache\/(.*)\.cache\.php$/";
$ignore[]= "/^\/bitrix\/cache\/(.*)\/[a-z0-9]{32}\.php$/";
$ignore[]= "/^\/bitrix\/managed_cache\/MYSQL\/(.*)\/[a-z0-9]{32}\.php$/";

print '[[START]]'._NR;

$cms= cms();
if($cms[0]=='modx_evo') print "[[MODX_".$cms[2]."]]"._NR;
if($cms[0]=='joomla') print "[[JOOMLA]]"._NR;
if($cms[0]=='opencart2') print "[[OPENCART2]]"._NR;

if($cms[0]=='modx_evo')
{
	$ignore[]= "/^\/assets\/cache\/docid_[0-9]{1,9}\.pageCache\.php$/";
}

if($action=='createetalon')
{
	print '[[CREATEETALON]]'._NR;
	logs('action:: createetalon');
	if($flaggo)
	{
		$zip= new ZipArchive();
		$zip->open($root._DS.'_buran'._DS.'b010'._DS.'_'.time().'_'.date('Y-m-d-H-i-s'), ZIPARCHIVE::CREATE);
	}else{
		print _BR.'<a href="'.$_SERVER['REQUEST_URI'].'&go">Подтвердить создание слепка этих файлов</a>'._BR._BR;
	}
	$files= array();
	createetalon('', $zip, $files, $ignore);
	if($flaggo) $zip->close();
	deletefiles('', $files);
}

if($action=='checkfiles')
{
	print '[[CHECKFILES]]'._NR;
	logs('action:: checkfiles');
	$folder= $root._DS.'_buran'._DS.'b010'._DS;
	$flag= false;
	if($open= opendir($folder))
	{
		while(($file= readdir($open))!==false)
		{
			if( ! is_dir($folder.$file) && preg_match("/^_[0-9]{10,14}_[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}$/", $file))
			{
				$flag= true;
				break;
			}
		}
	}
	if($flag)
	{
		checkfiles('', $ignore);
		restoredeletedfiles('');
	}else logs('checkfiles:: error_01', true);
}
print '[[FINISH]]'._NR;

// _____();
// function _____($folder)
// {
// 	global $root;
// 	$___= _DS.'_buran'._DS.'b010';
// 	if($open= opendir($root.$___._DS.'_'._DS.$folder))
// 	{
// 		while(($file= readdir($open))!==false)
// 		{
// 			if( ! is_dir($root.$___._DS.'_'._DS.$folder.$file))
// 			{
// 				if(file_exists($root._DS.$folder.$file))
// 				{
// 					if( ! file_exists($root.$___._DS.'backup'._DS.$folder._DS))
// 						mkdir($root.$___._DS.'backup'._DS.$folder._DS, 0777, true);
// 					$ii= 0;
// 					do{
// 						$ii++;
// 					}while(file_exists($root.$___._DS.'backup'._DS.$folder.$file.'_'.$ii));
// 					$copy= copy($root._DS.$folder.$file, $root.$___._DS.'backup'._DS.$folder.$file.'_'.$ii);
// 					logs('backup:: '._DS.$folder.$file.':: '._DS.$file.'_'.$ii, true);
// 				}
// 				if( ! file_exists($root.$___._DS.'etalon'._DS.$folder._DS))
// 					mkdir($root.$___._DS.'etalon'._DS.$folder._DS, 0777, true);
// 				copy($root.$___._DS.'_'._DS.$folder.$file, $root._DS.$folder.$file);
// 				$copy= copy($root.$___._DS.'_'._DS.$folder.$file, $root.$___._DS.'etalon'._DS.$folder.$file.'_0');
// 				if($copy)
// 				{
// 					unlink($root.$___._DS.'_'._DS.$folder.$file);
// 					logs('newfile:: ok:: '._DS.$folder.$file, true);
// 				}else{
// 					logs('newfile:: error:: '._DS.$folder.$file, true);
// 				}
// 			}elseif(is_link($root.$___._DS.'_'._DS.$folder.$file)){
// 			}elseif($file != '.' && $file != '..'){
// 				if( ! file_exists($root._DS.$folder.$file._DS))
// 					mkdir($root._DS.$folder.$file._DS, 0777, true);
// 				_____($folder.$file._DS);
// 				rmdir($root.$___._DS.'_'._DS.$folder.$file._DS);
// 			}
// 		}
// 	}
// 	rmdir($root.$___._DS.'_'._DS);
// }

function to_quarantine($folder, $file)
{
	global $root;
	$fp= $root._DS.'_buran'._DS.'b010'._DS.'quarantine'._DS.$folder;
	if( ! file_exists($fp)) mkdir($fp, 0777, true);
	$ii= 0;
	do{
		$ii++;
	}while(file_exists($fp.$file.'_'.$ii));
	$copy= copy($root._DS.$folder.$file, $fp.$file.'_'.$ii);
	logs('to_quarantine:: '._DS.$folder.$file.':: '._DS.$file.'_'.$ii, true);
	return $copy;
}

function deletefiles($folder, $files)
{
	global $root;
	$etalon= _DS.'_buran'._DS.'b010'._DS.'etalon'._DS;
	if($open= opendir($root.$etalon.$folder))
	{
		while(($file= readdir($open))!==false)
		{
			if( ! is_dir($root.$etalon.$folder.$file))
			{
				if( ! $files[$folder.$file])
				{
					unlink($root.$etalon.$folder.$file);
					logs('delete:: '.$etalon.$folder.$file, true);
				}
			}elseif(is_link($root.$etalon.$folder.$file)){
			}elseif($file != '.' && $file != '..'){
				deletefiles($folder.$file._DS, $files);
				if( ! $files[$folder.$file._DS])
				{
					rmdir($root.$etalon.$folder.$file._DS);
					logs('delete:: '.$etalon.$folder.$file._DS, true);
				}
			}
		}
	}
}

function restoredeletedfiles($folder)
{
	global $root;
	$etalon= _DS.'_buran'._DS.'b010'._DS.'etalon';
	if($open= opendir($root.$etalon._DS.$folder))
	{
		while(($file_0= readdir($open))!==false)
		{
			if( ! is_dir($root.$etalon._DS.$folder.$file_0))
			{
				$file= rtrim($file_0, '_0');
				if( ! file_exists($root._DS.$folder.$file))
				{
					copy($root.$etalon._DS.$folder.$file_0, $root._DS.$folder.$file);
					logs('restoredeleted:: '._DS.$folder.$file, true);
				}
			}elseif(is_link($root.$etalon._DS.$folder.$file_0)){
			}elseif($file_0 != '.' && $file_0 != '..'){
				restoredeletedfiles($folder.$file_0._DS);
			}
		}
	}
}

function checkfiles($folder, $ignore)
{
	global $root;
	if($open= opendir($root._DS.$folder))
	{
		while(($file= readdir($open))!==false)
		{
			foreach($ignore AS $row) if(preg_match($row, _DS.$folder.$file)) continue 2;

			if( ! is_dir($root._DS.$folder.$file))
			{
				if(strpos(_EXT,'/'.substr($file,strrpos($file,'.')).'/')===false) continue;
				$fn= md5(_DS.$folder.$file);
				$fp= $root._DS.'_buran'._DS.'b010'._DS.'etalon'._DS.$folder;
				$fs= filesize($root._DS.$folder.$file);
				$fmd5= md5_file($root._DS.$folder.$file);
				$do= 'ok';
				if( ! file_exists($fp.$file.'_0')) $do= 'to_quarantine';
				if($do=='ok')
				{
					$fs_2= filesize($fp.$file.'_0');
					$fmd5_2= md5_file($fp.$file.'_0');
					if($fs_2!=$fs || $fmd5_2!=$fmd5) $do= 'restore_from_etalon';
				}
				if($do=='to_quarantine')
				{
					$rr= to_quarantine($folder, $file);
					if($rr)
					{
						logs('unlink:: '._DS.$folder.$file, true);
						unlink($root._DS.$folder.$file);
					}
				}
				if($do=='restore_from_etalon')
				{
					$rr= to_quarantine($folder, $file);
					if($rr)
					{
						logs('restore:: '._DS.$folder.$file, true);
						copy($fp.$file.'_0', $root._DS.$folder.$file);
					}
				}
			}elseif(is_link($root._DS.$folder.$file)){
			}elseif($file != '.' && $file != '..'){
				checkfiles($folder.$file._DS, $ignore);
			}
		}
	}
}

function createetalon($folder, &$zip, &$files, $ignore)
{
	global $root,$flaggo;
	if($open= opendir($root._DS.$folder))
	{
		while(($file= readdir($open))!==false)
		{
			foreach($ignore AS $row) if(preg_match($row, _DS.$folder.$file)) continue 2;

			if( ! is_dir($root._DS.$folder.$file))
			{
				if(strpos(_EXT,'/'.substr($file,strrpos($file,'.')).'/')===false) continue;
				
				$files[$folder.$file.'_0']= true;
				if($flaggo) $zip->addFile($root._DS.$folder.$file, 'www'._DS.$folder.$file);
				
				$fp= $root._DS.'_buran'._DS.'b010'._DS.'etalon'._DS.$folder;
				if($flaggo && ! file_exists($fp)) mkdir($fp, 0777, true);
				$fs= filesize($root._DS.$folder.$file);
				$fmd5= md5_file($root._DS.$folder.$file);
				if(file_exists($fp.$file.'_0'))
				{
					$fs_2= filesize($fp.$file.'_0');
					$fmd5_2= md5_file($fp.$file.'_0');
				}else{
					$fs_2= false;
					$fmd5_2= false;
				}
				if($fs_2==$fs && $fmd5_2==$fmd5)
				{

				}else{
					if( ! $flaggo)
					{
						print date('d-m-Y, H:i:s', filectime($root._DS.$folder.$file)).' | <a target="_blank" href="__001.php?act=printfile&w='.$_GET['w'].'&dir=/&file='._DS.$folder.$file.'">'._DS.$folder.$file.'</a>'._BR;
					}else{
						copy($root._DS.$folder.$file, $fp.$file.'_0');
						$fs_2= filesize($fp.$file.'_0');
						$fmd5_2= md5_file($fp.$file.'_0');
						if($fs_2!=$fs || $fmd5_2!=$fmd5) logs('error:: '._DS.$folder.$file, true);
						else logs('create:: '._DS.$folder.$file, true);
					}
				}
			}elseif(is_link($root._DS.$folder.$file)){
			}elseif($file != '.' && $file != '..'){
				$files[$folder.$file._DS]= true;
				createetalon($folder.$file._DS, $zip, $files, $ignore);
			}
		}
	}
}

function logs($logs,$print=false)
{
	global $root;
	$fp= $root._DS.'_buran'._DS.'b010'._DS;
	if( ! file_exists($fp)) mkdir($fp, 0777, true);
	$ff= fopen($fp.'log', 'a');
	fwrite($ff, date('d.m.Y, H:i:s').' | '.$logs._NR);
	if($print) print $logs._NR._BR;
	fclose($ff);
}

function cms()
{
	//v005
	global $root_dir;
	@include($root_dir._DS.'manager/includes/version.inc.php');
	if( ! empty($modx_full_appname))
	{
		$cms= 'modx_evo';
		$cmsname= $modx_full_appname;
		$cmsver= $modx_version;
	}
	@include($root_dir._DS.'configuration.php');
	if(class_exists('JConfig')) $conf= new JConfig();
	if($conf->host)
	{
		$cms= 'joomla';
		$cmsname= '';
		$cmsver= '';
	}
	@include($root_dir._DS.'config.php');
	if(defined('DB_DRIVER') && defined('DB_HOSTNAME') && defined('DB_USERNAME') && defined('DB_PASSWORD') && defined('DB_DATABASE'))
	{
		$cms= 'opencart2';
		$cmsname= '';
		$cmsver= '';
	}
	return array($cms, $cmsname, $cmsver);
}
