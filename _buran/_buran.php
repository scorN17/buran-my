<?php
/**
 * Buran_0
 * v1.22
 * 28.04.2017
 * Delta
 * sergey.it@delta-ltd.ru
 *
 */
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'off');

if (file_exists('_buran_config.php')) {
	include_once('_buran_config.php');
}

ob_start();

$br = "<br>";
define('BR', '[br]');

$action = $_GET['act'];

$bu = new BURAN;
$bu->setConfig($config);

// ----------------------------------------------------------------------------
$url = 'http://fndelta.gavrishkin.ru/__password__002.php';
$url .= '?host='.$bu->domain;
$url .= '&w='.urlencode($_GET['w']);
$curloptions = array(
	CURLOPT_URL            => $url,
	CURLOPT_RETURNTRANSFER => true,
);
$curl = curl_init();
curl_setopt_array($curl, $curloptions);
$ww = curl_exec($curl);
if ( ! $ww || $_GET['w'] == '' || $_GET['w'] != $ww) {
	print '[fail]';
	exit();
}
// ----------------------------------------------------------------------------

if ( ! $action) goto mainpage;



if ($action == 'file.content') {
	print date('d.m.Y, H:i:s', $bu->filetime($bu->file)) .BR.BR;
	$bu->highlight($bu->file) .BR;
}


if ($action == 'db.dump') {
	print '[start]' .BR;
	$res = $bu->dbDump();
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
}


if ($action == 'files.backup' || $action == 'files.backup.auto') {
	print '[start]' .BR;
	$autonextpart = $action == 'files.backup.auto' ? true : false;
	$res = $bu->filesBackup($autonextpart);
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
}


if ($action == 'etalon.files.show' || $action == 'etalon.files.create') {
	print '[start]' .BR;
	$create = $action == 'etalon.files.create' ? true : false;
	$res = $bu->etalonFiles($create);
	if ($res) {
		print '[ok]' .BR;

		$res = $bu->etalonFilesClear();
		if ($res) {
			print '[ok]' .BR;
		} else {
			print '[error]' .BR;
		}

	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
}



$toprint = ob_get_contents();
$toprint = str_replace(BR, $br, $toprint);
ob_end_clean();
print $toprint;
exit();










mainpage:
?>
<!doctype html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<script>
(function($){
	$(document).ready(function(){
		$('.action').on('click',function(){
			if ($('body').hasClass('loading')) return;
			$('body').addClass('loading');
			let act = $(this).data('act');
			$('.iframes iframe').removeClass('active');
			$('.iframes').prepend('<iframe class="active" src="<?=$bu->scriptname.'?w='.$_GET['w']?>&act='+act+'">');
			$('.iframes iframe.active').one('load',function(){
				$('body').removeClass('loading');
			});
		});

		$('.if_minimize').on('click',function(){
			$('.iframes iframe').removeClass('active');
		});

		$(window).on('keyup',function(e){
			var code = e.keyCode || e.which;
			if (
				(code == 27) &&
				$('.iframes iframe.active').length
			) {
				$('.if_minimize').trigger('click');
			}
		});
	});
})(jQuery);
</script>
</head>
<body>
<div class="panelbox">
	<button class="action" data-act="db.dump">Дамп базы</button>
	<button class="action" data-act="files.backup.auto">Бэкап файлов</button>
	<button class="action" data-act="etalon.files.show">Эталонные файлы</button>
	<button class="action" data-act="etalon.files.create">Создать эталонные файлы</button>
</div>

<div class="iframesbox">
	<div class="buttons">
		<button class="if_minimize">Свернуть</button>
	</div>
	<div class="iframes">
		
	</div>
</div>

<style>
* {
	position: relative;
	font-size: 1em;
	padding: 0;
	margin: 0;
	box-sizing: border-box;
}
body {
	-ms-text-size-adjust: 100%;
	-moz-text-size-adjust: 100%;
	-webkit-text-size-adjust: 100%;
	background: #fff;
	color: #000;
	width: 100%;
	height: 100%;
	font-stretch: normal;
	font-variant: normal;
	line-height: normal;
	font-weight: normal;
	font-style: normal;
	font-size: 14px;
	font-family: "Arial", sans-serif;
}
br {
	clear: both;
	font-size: 0;
	line-height: 0;
	height: 0;
}
.panelbox {
	height: 7vh;
	padding: 10px;
}
.iframesbox {
	height: 93vh;
	padding: 10px;
}
	.iframesbox .buttons {
		padding-bottom: 10px;
		text-align: right;
	}
.iframes {
	height: 100%;
}
	.iframes iframe {
		border: 1px solid #ddd;
		z-index: 1;
		background: #fff;
		width: 250px;
		height: 250px;
		margin: 0 10px 10px 0;
	}
	.iframes iframe.active {
		margin: 0;
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		width: 100%;
		height: 100%;
		z-index: 2;
	}
</style>
</body>
</html>
<?php
















// ========================================================================

class BURAN
{
	public $version = '1.0';

	public $conf = array(
		'maxtime'   => 25,
		'maxmemory' => 262144000, //1024*1024*250

		'flag_db_dump'             => true,
		'flag_files_backup'        => true,
		'files_backup_maxpartsize' => 262144000, //1024*1024*250

		'etalon_files_ext' => '/.php/.htaccess/.html/.htm/.js/',
	);

	public $time_start;

	public $http;
	public $domain;
	public $www;
	public $scriptname;
	public $requesturi;
	public $pageurl;
	public $querystring;
	public $droot;
	public $broot;

	public $file;
	public $files;

	public $logs = array();

	public $cms = false;
	public $cms_ver;
	public $cms_date;
	public $cms_name;

	function __construct()
	{
		$this->time_start = microtime(true);

		$this->http = (
			$_SERVER['SERVER_PORT'] == '443' ||
			$_SERVER['HTTP_PORT']   == '443' ||
			$_SERVER['HTTP_HTTPS']  == 'on' ||
			(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ||
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
				$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
				? 'https://' : 'http://');
		$domain = isset($_SERVER['HTTP_HOST'])
						? $_SERVER['HTTP_HOST']
						: $_SERVER['SERVER_NAME'];
		$domain = explode(':', $domain);
		$domain = $domain[0];
		$this->www = strpos($domain,'www.')===0 ? 'www.' : '';
		if($this->www == 'www.') $domain = substr($domain,4);
		$this->domain = $domain;
		$this->scriptname = isset($_SERVER['SCRIPT_NAME'])
						? $_SERVER['SCRIPT_NAME']
						: $_SERVER['PHP_SELF'];
		$this->requesturi  = $_SERVER['REQUEST_URI'];
		$this->pageurl     = parse_url($requesturi, PHP_URL_PATH);
		$this->querystring = $_SERVER['QUERY_STRING'];
		$this->droot       = dirname(dirname(__FILE__));
		$this->broot       = dirname(__FILE__);

		if (isset($_GET['file'])) {
			$file = urldecode($_GET['file']);
			if (substr($file,0,1) != '/')
				$file = '/'.$file;
			$this->file = $file;
		}

		define('DS', DIRECTORY_SEPARATOR);
	}

	// ========================================================================

	function dbDump()
	{
		if ( ! $this->conf('flag_db_dump')) return false;

		$this->cms();
		$this->db_access();

		$db = new mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_name);
		if ( ! $db || ! ($db instanceof mysqli)) return false;
		$db->query("{$this->db_method} {$this->db_charset}");

		$folder = $this->broot.DS.'backup'.DS;
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);
		$this->htaccess($folder);

		$name = $this->domain.'_db_'.date('Y-m-d-H-i-s');
		$filepath = $name.'.sql';
		$h = fopen($folder.$filepath, 'wb');
		if ( ! $h) return false;


		$dump .= "# -- start / ". date('d.m.Y, H:i:s') ."\n\n";

		$res = $db->query("SHOW TABLES");
		if ($res === false) return false;

		$tables = 0;
		while ($row = $res->fetch_row()) {
			$dump .= "# ---------------------------- `".$row[0]."`" ."\n\n";

			$tables++;

			$res2 = $db->query("SHOW CREATE TABLE `{$row[0]}`");
			if ($res2 === false) return false;
			while ($row2 = $res2->fetch_row()) {
				$dump .= "DROP TABLE IF EXISTS `{$row[0]}`;" ."\n";
				$dump .= $row2[1] .";" ."\n\n";
			}
		
			$res2 = $db->query("SELECT * FROM `{$row[0]}`");
			if ($res2 === false) return false;
			$ii = 0;
			while ($row2 = $res2->fetch_assoc()) {
				$dump .= "INSERT INTO `{$row[0]}` SET ";
				if ( ! is_array($row2)) return false;
				$first = true;
				foreach ($row2 AS $key => $val) {
					$val = $db->real_escape_string($val);
					$dump .= ($first ? "" : ",") ."`{$key}`='{$val}'";
					$first = false;
				}
				$dump .= ";" ."\n";

				$ii++;
				if ($ii >= 500 || strlen($dump) >= 1024*512) {
					fwrite($h, $dump);
					$dump = '';
					$ii = 0;
				}
			}
		}

		$dump .= "# -- the end / ". date('d.m.Y, H:i:s') ."\n";

		fwrite($h, $dump);
		fclose($h);
		$this->log($name.' | tables '.$tables.' | finish'."\n", 'db_dump');
		return true;
	}


	function filesBackup($autonextpart=false)
	{
		if ( ! $this->conf('flag_files_backup')) return false;

		$zip = new ZipArchive();
		if ( ! $zip) return false;

		$folder = $this->broot.DS.'backup'.DS;
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);
		$this->htaccess($folder);

		$part   = 0;
		$offset = 0;
		$h = fopen($folder.'files_backup_process', 'rb');
		if ($h) {
			while ( ! feof($h)) {
				$prms .= fread($h, 1024*128);
			}
			$prms   = explode("\n", $prms);
			$name   = $prms[0];
			$part   = $prms[1];
			$offset = $prms[2];

		} else {
			$name = $this->domain.'_files_'.date('Y-m-d-H-i-s');
		}
		$part++;
		$filepath = $name.'_part'.$part.'.zip';
		$zip->open($folder.$filepath, ZIPARCHIVE::CREATE);

		$flag_max = false;
		$queue[] = '/';
		$size = 0;
		$ii = 0;
		do {
			$nextfolder = array_shift($queue);
			if( ! ($open = opendir($this->droot.$nextfolder)))
				continue;

			while ($file = readdir($open)) {
				if (filetype($this->droot.$nextfolder.$file) == 'link'
					|| $file == '.' || $file == '..'
					|| $file == '.th'
					|| $nextfolder.$file == '/_buran/backup')
					continue;
				if (is_dir($this->droot.$nextfolder.$file)) {
					$queue[] = $nextfolder.$file.'/';
					continue;
				}
				if( ! is_file($this->droot.$nextfolder.$file))
					continue;
				
				$ii++;
				if ($ii < $offset) continue;
				
				$zip->addFile($this->droot.$nextfolder.$file, 'www.'.$this->domain.$nextfolder.$file);
				
				$size += filesize($this->droot.$nextfolder.$file);
				
				if (
					$this->max() ||
					$size >= $this->conf('files_backup_maxpartsize')
				) {
					$flag_max = true;
					break;
				}
				if ($ii % 2000 == 0) sleep(3);
			}

			if ($this->max()) {
				$flag_max = true;
			}
			if ($flag_max) break;
		} while ($queue[0]);

		$offset = $ii;

		$zip->close();

		print '[part_'.$part.']' .BR;
		print '[offset_'.$offset.']' .BR;

		if ($flag_max) {
			$h = fopen($folder.'files_backup_process', 'wb');
			if ( ! $h) return false;
			fwrite($h, $name."\n");
			fwrite($h, $part."\n");
			fwrite($h, $offset."\n");
			fclose($h);
			print '[nextpart]' .BR;
			$this->log($name.' | part '.$part.' | offset '.$offset.' | nextpart', 'files_backup');

			if ($autonextpart) {
				print '[autonextpart]' .BR;
				print '<script>
					setTimeout(function(){
						location.reload();
					},1000);
				</script>';
			}

		} else {
			unlink($folder.'files_backup_process');
			$this->log($name.' | part '.$part.' | offset '.$offset.' | finish'."\n", 'files_backup');
		}
		return true;
	}


	function etalonFiles($create=false)
	{
		if ($create) {
			$zip = new ZipArchive();
			if ( ! $zip) return false;
		}

		$folder = $this->broot.DS.'etalon'.DS;
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);
		$this->htaccess($folder);

		if ($create) {
			$zipfile = 'etalon_'.time().'_'.date('Y-m-d-H-i-s');
			$zip->open($folder.$zipfile, ZIPARCHIVE::CREATE);
		}

		$flag_max = false;
		$queue[] = '/';
		$files = array();
		do {
			$nextfolder = array_shift($queue);
			if( ! ($open = opendir($this->droot.$nextfolder)))
				continue;
			while ($file = readdir($open)) {
				if (filetype($this->droot.$nextfolder.$file) == 'link'
					|| $file == '.' || $file == '..')
					continue;
				if (is_dir($this->droot.$nextfolder.$file)) {
					$queue[] = $nextfolder.$file.'/';
					$files[$nextfolder.$file.'/'] = true;
					continue;
				}
				if( ! is_file($this->droot.$nextfolder.$file))
					continue;
				if (strpos($this->conf('etalon_files_ext'),
					'/'.substr($file,strrpos($file,'.')).'/') === false)
					continue;

				$etalonfolder = $folder.'etalon'.$nextfolder;
				$etalonfile = $file.'_0';

				$files[$nextfolder.$etalonfile] = true;

				if ($create) {
					$zip->addFile($this->droot.$nextfolder.$file,
						'www.'.$this->domain.$nextfolder.$file);

					if ( ! file_exists()) {
						mkdir($etalonfolder, 0755, true);
					}
				}

				$size = filesize($this->droot.$nextfolder.$file);
				$hash = md5_file($this->droot.$nextfolder.$file);

				$size_0 = false;
				$hash_0 = false;
				if (file_exists($etalonfolder.$etalonfile)) {
					$size_0 = filesize($etalonfolder.$etalonfile);
					$hash_0 = md5_file($etalonfolder.$etalonfile);
				}

				if ($size != $size_0 || $hash != $hash_0) {
					if ($create) {
						copy($this->droot.$nextfolder.$file,
							$etalonfolder.$etalonfile);

						$size_0 = filesize($etalonfolder.$etalonfile);
						$hash_0 = md5_file($etalonfolder.$etalonfile);
						if ($size != $size_0 || $hash != $hash_0) {
							print '--------- | '.$nextfolder.$file .BR;
						} else {
							print 'ok | '.$nextfolder.$file .BR;
						}

					} else {
						print date('d.m.Y, H:i:s', $this->filetime($nextfolder.$file)).' | ';
						print '<a style="text-decoration:none;" target="_blank" href="_buran.php?w='.$_GET['w'].'&act=file.content&file='.urlencode($nextfolder.$file).'">'.$nextfolder.$file.'</a>';
						print BR;
					}
				}

				if ($this->max()) {
					$flag_max = true;
					break;
				}
			}

			if ($this->max()) {
				$flag_max = true;
			}
			if ($flag_max) break;
		} while ($queue[0]);

		if ($create) {
			$zip->close();
		}

		if ($flag_max) {
			print '[flag_max]' .BR;
		}

		$this->files = $files;

		return true;
	}


	function etalonFilesClear()
	{
		$folder = DS.'etalon'.DS.'etalon';

		$flag_max = false;
		$queue[] = '/';
		$nextfolder = false;
		do {
			$nextfolder = array_shift($queue);
			if( ! ($open = opendir($this->broot.$folder.$nextfolder)))
				continue;
			while ($file = readdir($open)) {
				if (filetype($this->broot.$folder.$nextfolder.$file) == 'link'
					|| $file == '.' || $file == '..')
					continue;
				if (is_dir($this->broot.$folder.$nextfolder.$file)) {
					$queue[] = $nextfolder.$file.'/';
					continue;
				}
				if( ! is_file($this->broot.$folder.$nextfolder.$file))
					continue;

				$file_1 = substr($file, 0, -2);

				if ( ! file_exists($this->droot.$nextfolder.$file_1)) {
					unlink($this->broot.$folder.$nextfolder.$file);
					print 'remove | '.$folder.$nextfolder.$file .BR;
				}

				if ($this->max()) {
					$flag_max = true;
					break;
				}
			}

			if ($this->max()) {
				$flag_max = true;
			}
			if ($flag_max) break;
		} while ($queue[0]);

		if ($flag_max) {
			print '[flag_max]' .BR;
		}

		return true;
	}











	// ========================================================================
	
	function highlight($file)
	{
		highlight_file($this->droot.$file);
		return true;
	}
	
	function filetime($file, $type='c')
	{
		switch ($type) {
			case 'a':
				$time = fileatime($this->droot.$file);
				break;
			case 'm':
				$time = filemtime($this->droot.$file);
				break;
			default:
				$time = filectime($this->droot.$file);
		}
		return $time ? $time : false;
	}
	
	function cms()
	{
		@include_once($this->droot.'/manager/includes/version.inc.php');
		if (isset($modx_full_appname) && $modx_full_appname) {
			if (strpos($modx_full_appname, 'MODX') === 0) {
				$this->cms = 'modx.evo';
			} else {
				$this->cms = 'evolution';
			}
			$this->cms_ver  = $modx_version;
			$this->cms_date = $modx_release_date;
			$this->cms_name = $modx_full_appname;
			return true;
		}

		@include_once($this->droot.'/configuration.php');
		if (class_exists('JConfig')) {
			$conf = new JConfig();
			if ($conf->host) {
				$this->cms      = 'joomla';
				$this->cms_ver  = '';
				$this->cms_date = '';
				$this->cms_name = '';
				return true;
			}
		}

		@include_once($this->droot.'/config.php');
		if(defined('DB_DRIVER') && defined('DB_HOSTNAME') &&
			defined('DB_USERNAME') && defined('DB_PASSWORD') &&
			defined('DB_DATABASE')) {
			$this->cms      = 'opencart2';
			$this->cms_ver  = '';
			$this->cms_date = '';
			$this->cms_name = '';
			return true;
		}

		@include_once($this->droot.'/bootstrap.php');
		if(defined('HOSTCMS')) {
			$this->cms      = 'hostcms';
			$this->cms_ver  = '';
			$this->cms_date = '';
			$this->cms_name = '';
			return true;
		}

		return false;
	}

	function db_access()
	{
		$this->db_method  = 'SET CHARACTER SET';
		$this->db_charset = 'utf8';

		if ($this->cms == 'modx.evo' || $this->cms == 'evolution') {
			@include_once($this->droot.'/manager/includes/config.inc.php');
			$this->db_host    = $database_server;
			$this->db_user    = $database_user;
			$this->db_pwd     = $database_password;
			$this->db_name    = trim($dbase,"`");
			$this->db_method  = $database_connection_method;
			$this->db_charset = $database_connection_charset;
		}
		
		if ($this->cms == 'joomla') {
			@include_once($this->droot.'/configuration.php');
			$conf = new JConfig();
			$this->db_host = $conf->host;
			$this->db_user = $conf->user;
			$this->db_pwd  = $conf->password;
			$this->db_name = $conf->db;
		}

		if ($this->cms == 'opencart2') {
			@include_once($this->droot.'/config.php');
			$this->db_host = DB_HOSTNAME;
			$this->db_user = DB_USERNAME;
			$this->db_pwd  = DB_PASSWORD;
			$this->db_name = DB_DATABASE;
		}

		if ($this->cms == 'hostcms') {
			$ret = require($this->droot.'/modules/core/config/database.php');
			$this->db_host = $ret['default']['host'];
			$this->db_user = $ret['default']['username'];
			$this->db_pwd  = $ret['default']['password'];
			$this->db_name = $ret['default']['database'];
		}
	}
	
	// ========================================================================

	function setConfig($config)
	{
		if ( ! is_array($config)) return false;
		$this->conf = array_merge($this->conf, $config);

	}

	function conf($name)
	{
		return isset($this->conf[$name]) ? $this->conf[$name] : NULL;
	}

	function log($text, $name='0', $clear=false)
	{
		if ( ! $this->logs_files[$name]) {
			$h = fopen($this->broot.DS.'_buran_log_'.$name, ($clear ? 'wb' : 'ab'));
			if ($h) {
				$this->logs_files[$name] = $h;
			} else {
				return false;
			}
		}
		$text = date('d.m.Y, H:i:s').' | '. $text ."\n";
		$r = fwrite($this->logs_files[$name], $text);
		return $r;
	}

	function max()
	{
		$time = microtime(true) - $this->time_start;
		$memory = memory_get_peak_usage(true);
		$res = $time >= $this->conf('maxtime') || $memory >= $this->conf('maxmemory')
			? true : false;
		if ($res) {
			$this->log('max | '.$time.' s | '.$memory.' b');
		}
		return $res;
	}

	function htaccess($folder)
	{
		$htaccess = 'Order Deny,Allow'. "\n";
		$htaccess .= 'Deny from all'. "\n";
		$htaccess .= 'RewriteEngine On'. "\n";
		$htaccess .= 'RewriteRule ^(.*)$ index.html [L,QSA]'. "\n";
		$h = fopen($folder.'.htaccess', 'wb');
		fwrite($h, $htaccess);
		fclose($h);
	}
}