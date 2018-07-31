<?php
/**
 * Buran_0
 * @version 1.43
 * 01.08.2018
 * Delta
 * sergey.it@delta-ltd.ru
 *
 */
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'off');
header('Content-type: text/html; charset=utf-8');

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
$url = 'http://bunker-yug.ru/__buran/secret_key.php';
$url .= '?h='.$bu->domain;
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

?>
<!doctype html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>

<?php

if ($action == 'update') {
	print '[start]' .BR;
	$res = $bu->update();
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
}


if ($action == 'files.manager') {
	?>
<style>
	a {
		text-decoration: none;
		color: #00167d;
	}
</style>
	<?php
	print '[start]' .BR;
	$res = $bu->filesManager();
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
}


if ($action == 'file.content') {
	$folder = dirname($_GET['file']);
	?>
<script>
(function($){
	$(document).ready(function(){
		$('.openpanel').on('click',function(){
			$(this).remove();
			$('.panel').css({ display:'flex' });
		});
	});
})(jQuery);
</script>
	<div class="openpanel" style="cursor:pointer;">PANEL</div>
	<div class="panel" style="display:none; justify-content:space-between;">
		<a href="_buran.php?w=<?=$_GET['w']?>&act=file.chmod&file=<?=$_GET['file']?>&prm=0755">0755</a>
		<a href="_buran.php?w=<?=$_GET['w']?>&act=file.cure&file=<?=$_GET['file']?>">CURE</a>
		<a target="_blank" href="_buran.php?w=<?=$_GET['w']?>&act=file.rename&file=<?=$_GET['file']?>&prm=">RENAME</a>
		<a href="_buran.php?w=<?=$_GET['w']?>&act=file.delete&file=<?=$_GET['file']?>">DELETE</a>
	</div>
	<br><a href="_buran.php?w=<?=$_GET['w']?>&act=files.manager&file=<?=$folder?>"><- back</a><br>
	<?php
	print BR;
	print date('d.m.Y, H:i:s', $bu->filetime($bu->droot.$bu->file)) .BR.BR;
	$bu->highlight($bu->droot.$bu->file) .BR;
}


if ($action == 'file.chmod') {
	print '[start]' .BR;
	if ($bu->go) {
		$res = $bu->chmodFile($bu->droot.$bu->file, $bu->param);
	} else {
		print '<a href="'.$bu->uri.'&go">GO</a>' .BR;
	}
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
}


if ($action == 'file.delete') {
	print '[start]' .BR;
	if ($bu->go) {
		$res = $bu->deleteFile($bu->droot.$bu->file);
	} else {
		print '<a href="'.$bu->uri.'&go">GO</a>' .BR;
	}
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
}


if ($action == 'file.rename') {
	print '[start]' .BR;
	if ($bu->go) {
		$res = $bu->renameFile($bu->droot.$bu->file, $bu->param);
	} else {
		print '<a href="'.$bu->uri.'&go">GO</a>' .BR;
	}
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
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


if ($action == 'files.backup') {
	print '[start]' .BR;
	$res = $bu->filesBackup();
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


if ($action == 'etalon.list.compare') {
	print '[start]' .BR;
	if ( ! $bu->file) {
		$bu->file = 'etalon_list_0';
	}
	$res = $bu->etalonListCompare();
	print '[finish]' .BR;
}


if ($action == 'etalon.list.create') {
	print '[start]' .BR;
	$res = $bu->etalonListCreate();
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
}



$toprint = ob_get_contents();
$toprint = str_replace(BR, $br, $toprint);
ob_end_clean();
print $toprint;
?>
</body>
</html>
<?php
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
			let get = $(this).data('get');
			if (get == undefined) get = '';
			$('.iframes iframe').removeClass('active');
			$('.iframes').prepend('<iframe class="active" src="<?=$bu->scriptname.'?w='.$_GET['w']?>&act='+act+get+'">');
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
<div class="loading_status">Загрузка ...</div>

<div class="panelbox">
	<div><button class="action" data-act="files.manager">Менеджер файлов</button></div>

	<div>
		<button class="action" data-act="db.dump">Дамп базы</button>
		<button class="action" data-act="files.backup" data-get="&auto">Бэкап файлов</button>
	</div>

	<div>
		<button class="action" data-act="etalon.files.show">Эталонные файлы</button>
		<button class="action" data-act="etalon.files.create">Создать эталонные файлы</button>
	</div>

	<div>
		<button class="action" data-act="etalon.list.compare">Сравнить со слепком</button>
		<button class="action" data-act="etalon.list.create" data-get="&auto">Создать слепок</button>
	</div>

	<div><button class="action" data-act="update">Самообновление</button></div>
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
.loading_status {
	color: #003bb6;
	display: none;
	position: absolute;
	top: 0;
	right: 0;
}
	.loading .loading_status {
		display: block;
	}
.panelbox {
	height: 7vh;
	padding: 10px;
	display: flex;
	justify-content: space-between;
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
	public $version = '1.43';

	public $conf = array(
		'maxtime'   => 27,
		'maxmemory' => 262144000, //1024*1024*250
		'maxitems'  => 10000,

		'flag_db_dump'             => true,
		'flag_files_backup'        => true,
		'files_backup_maxpartsize' => 262144000, //1024*1024*250

		'etalon_files_ext' => '/.php/.htaccess/.html/.htm/.js/.inc/.css/.sass/.scss/.less/',
		'etalon_list_ext'  => '/.php/.htaccess/.html/.htm/.js/.inc/.css/.sass/.scss/.less/',
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

	public $go         = false;
	public $autoReload = false;
	public $param      = false;

	public $file;
	public $tofile;
	public $files;

	public $processFile = false;

	public $itemscounter = 0;

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
		$this->uri         = $this->requesturi;
		$this->droot       = dirname(dirname(__FILE__));
		$this->broot       = dirname(__FILE__);

		if (isset($_GET['auto'])) {
			$this->autoReload = true;
		}
		if (isset($_GET['prm'])) {
			$this->param = $_GET['prm'];
		}
		if (isset($_GET['go'])) {
			$this->go = true;
		}

		if (isset($_GET['file'])) {
			$file = urldecode($_GET['file']);
			if (substr($file,0,1) != '/')
				$file = '/'.$file;
			$this->file = $file;
		}

		if (isset($_GET['tofile'])) {
			$tofile = urldecode($_GET['tofile']);
			if (substr($tofile,0,1) != '/')
				$tofile = '/'.$tofile;
			$this->tofile = $tofile;
		}

		define('DS', DIRECTORY_SEPARATOR);
	}

	// ========================================================================

	function update()
	{
		$requestsheaders = array(
			'Content-Type: text/plain; charset=utf-8'
		);
		$url = 'http://bunker-yug.ru/__buran/update/_buran';
		$curloptions = array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
		);
		$curl = curl_init();
		curl_setopt_array($curl, $curloptions);
		$code = curl_exec($curl);

		if ( ! $code ||
			strpos($code, "<?php\n/**\n * Buran_0") !== 0)
			return false;

		$h = fopen(__FILE__, 'wb');
		if ( ! $h) return false;

		$res = fwrite($h, $code);
		if ( ! $res) return false;

		fclose($h);

		return true;
	}


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


	function filesBackup()
	{
		if ( ! $this->conf('flag_files_backup')) return false;

		$zip = new ZipArchive();
		if ( ! $zip) return false;

		$folder = $this->broot.DS.'backup'.DS;
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);
		$this->htaccess($folder);

		$name = $this->domain.'_files_'.date('Y-m-d-H-i-s');

		$this->processFile = $folder.'files_backup_process';
		$part   = 0;
		$offset = 0;
		$info = $this->processFile();
		if ($info) {
			$name   = $info[0];
			$part   = $info[1];
			$offset = $info[2];
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
					|| $nextfolder.$file == '/_buran/backup'
					|| $nextfolder.$file == '/_buran/etalon/files')
					continue;
				if (is_dir($this->droot.$nextfolder.$file)) {
					$queue[] = $nextfolder.$file.'/';
					continue;
				}
				if( ! is_file($this->droot.$nextfolder.$file))
					continue;
				
				$ii++;
				if ($ii <= $offset) continue;
				$this->itemscounter++;
				
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
			print '[nextpart]' .BR;

			$res = $this->processFile('w', $name."\n".$part."\n".$offset);
			if ( ! $res) return false;

			$this->log($name.' | part '.$part.' | offset '.$offset.' | nextpart', 'files_backup');

			if ($this->autoReload) {
				$this->reloadAction();
			}

		} else {
			$this->deleteFile($this->processFile);
			$this->log($name.' | part '.$part.' | offset '.$offset.' | finish'."\n", 'files_backup');
		}
		return true;
	}


	function filesManager()
	{
		$flag_max = false;
		$folder = $this->file;
		if (substr($folder,-1) != '/')
			$folder .= '/';

		if( ! ($open = opendir($this->droot.$folder)))
			return false;
		while ($file = readdir($open)) {
			if (filetype($this->droot.$folder.$file) == 'link'
				|| $file == '.' || $file == '..')
				continue;
			if (is_dir($this->droot.$folder.$file)) {
				$a1[] = array(
					$folder.$file.DS,
				);
				continue;
			}
			if( ! is_file($this->droot.$folder.$file))
				continue;

			$fs = filesize($this->droot.$folder.$file);
			$st = 0;
			while ($fs >= 1024) {
				$fs /= 1024;
				$st++;
			}
			$fs = round($fs, 2).' '.$this->sizeType($st);

			$a2[] = array(
				$folder.$file,
				$fs
			);

			if ($this->max()) {
				$flag_max = true;
				break;
			}
		}

		$folder = dirname($folder);

		sort($a1);
		sort($a2);

		print BR;
		print '<div><a href="_buran.php?w='.$_GET['w'].'&act=files.manager">#droot</a></div>';
		print '<div><a href="_buran.php?w='.$_GET['w'].'&act=files.manager&file='.urlencode($folder).'"><- back</a></div>';
		print BR;
		
		if (is_array($a1)) {
			foreach ($a1 AS $row) {
				print '<div><a href="_buran.php?w='.$_GET['w'].'&act=files.manager&file='.urlencode($row[0]).'">'.$row[0].'</a></div>';
			}
		}

		print BR;
		
		if (is_array($a2)) {
			foreach ($a2 AS $row) {
				print '<div style="display:flex;">
					<div style="width:100px;text-align:right;padding-right:20px;">'.$row[1].'</div>
					<div><a href="_buran.php?w='.$_GET['w'].'&act=file.content&file='.urlencode($row[0]).'">'.$row[0].'</a></div>
				</div>';
			}
		}

		print BR;

		if ($flag_max) {
			print '[flag_max]' .BR;
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
			$zipfile = 'etalon_files_'.date('Y-m-d-H-i-s');
			$zip->open($folder.$zipfile, ZIPARCHIVE::CREATE);
		}

		$flag_max = false;
		$queue[] = '/';
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
					continue;
				}
				if( ! is_file($this->droot.$nextfolder.$file))
					continue;
				if (strpos($this->conf('etalon_files_ext'),
					'/'.substr($file,strrpos($file,'.')).'/') === false)
					continue;

				$etalonfolder = $folder.'files'.$nextfolder;
				$etalonfile = $file.'_0';

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
						print date('d.m.Y, H:i:s', $this->filetime($this->droot.$nextfolder.$file)).' | ';
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

		return true;
	}


	function etalonFilesClear()
	{
		$folder = DS.'etalon'.DS.'etalon';

		$flag_max = false;
		$queue[] = '/';
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
					$this->deleteFile($this->broot.$folder.$nextfolder.$file);
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


	function etalonListCompare()
	{
		$folder = $this->broot.DS.'etalon'.DS.'list'.DS;
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);
		$this->htaccess($folder);

		if (($files = glob($folder.'etalon_*'))) {
			print BR;
			foreach ($files AS $etalonfile) {
				$etalonfile = substr($etalonfile, strrpos($etalonfile, '/'));
				print '<div><a href="_buran.php?w='.$_GET['w'].'&act=etalon.list.compare&file='.$etalonfile.'">'.$etalonfile.'</a></div>';
			}
		}

		$h = fopen($folder.$this->file, 'rb');
		if ( ! $h) return false;
		$list = '';
		while ( ! feof($h))
			$list .= fread($h, 1024*256);
		fclose($h);

		$list = explode("\n\n", $list);
		$time = array_shift($list);

		$parts = array();
		while ($part = array_shift($list)) {
			$part = unserialize($part);
			$parts = array_merge($parts, $part);
		}
		$list = $parts;

		$this->processFile = $folder.'etalon_list_process';
		$offset = 0;
		$info = $this->processFile();
		if ($info) {
			$offset = intval($info[0]);
		}

		if (is_array($list)) {
			foreach ($list AS $file => $row) {
				if (file_exists($this->droot.$file))
					continue;
				$list_3 = '<div style="font-size:12px;font-family:arial;padding-bottom:2px;">';
				$list_3 .= '<span style="text-decoration:none;color:#000;">'.$file.'</span>';
				$list_3 .= '</div>';
			}
		}

		$flag_max = false;
		$queue[] = '/';
		$ii = 0;
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
					continue;
				}
				if( ! is_file($this->droot.$nextfolder.$file))
					continue;
				if (strpos($this->conf('etalon_list_ext'),
					'/'.substr($file,strrpos($file,'.')).'/') === false)
					continue;

				$ii++;
				if ($ii <= $offset) continue;
				$this->itemscounter++;

				$stat = stat($this->droot.$nextfolder.$file);
				$hash = md5_file($this->droot.$nextfolder.$file);

				$row = '<div style="font-size:12px;font-family:arial;padding-bottom:2px;">';
				$row .= date('d.m.Y, H:i:s', $this->filetime($this->droot.$nextfolder.$file));
				$row .= ' | ';
				$row .= '<a style="text-decoration:none;color:#000;" target="_blank" href="_buran.php?w='.$_GET['w'].'&act=file.content&file='.urlencode($nextfolder.$file).'">'.$nextfolder.$file.'</a>';
				$row .= '</div>';
				
				if ( ! $list[$nextfolder.$file]['h']) {
					$list_1 .= $row;

				} elseif ($list[$nextfolder.$file]['h'] != $hash
					|| $list[$nextfolder.$file]['s'] != $stat['size']) {
					$list_2 .= $row;

				} else {
					$list_0 .= $row;
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

		$offset = $ii;

		print BR;
		print 'Дата эталона: '.date('d.m.Y, H:i:s', $time) .BR;

		if ($flag_max) {
			print '[flag_max]' .BR;

			$res = $this->processFile('w', $offset);
			if ( ! $res) return false;

		} else {
			$this->deleteFile($this->processFile);
		}

		print '<h3 style="color:#d70000;">Файл изменен</h3>';
		print $list_2;
		print '<h3 style="color:#004cd6;">Нет файла</h3>';
		print $list_3;
		print '<h3 style="color:#d3b200;">Новый файл</h3>';
		print $list_1;
		print '<h3 style="color:#00c73c;">Файл не изменен</h3>';
		print $list_0;

		return true;
	}


	function etalonListCreate()
	{
		$folder = $this->broot.DS.'etalon'.DS.'list'.DS;
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);
		$this->htaccess($folder);

		$this->processFile = $folder.'etalon_list_create_process';
		$process = false;
		$offset = 0;
		$etalonfile = false;
		$info = $this->processFile();
		if ($info) {
			$etalonfile = $info[0];
			$offset = intval($info[1]);
			$process = true;
		}

		$files = array();

		$flag_max = false;
		$queue[] = '/';
		$ii = 0;
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
					continue;
				}
				if( ! is_file($this->droot.$nextfolder.$file))
					continue;
				if (strpos($this->conf('etalon_list_ext'),
					'/'.substr($file,strrpos($file,'.')).'/') === false)
					continue;

				$ii++;
				if ($ii <= $offset) continue;
				$this->itemscounter++;

				$stat = stat($this->droot.$nextfolder.$file);
				$hash = md5_file($this->droot.$nextfolder.$file);

				$files[$nextfolder.$file] = array(
					'h' => $hash,
					's' => $stat['size'],
					't' => $stat['ctime'],
				);

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

		$offset = $ii;

		if (is_array($files) && count($files)) {
			$files = serialize($files);
		} else {
			$files = false;
		}

		if ( ! $etalonfile) {
			$etalonfile = 'etalon_list_'.date('Y-m-d-H-i-s').'_process';
		}
		if ($files) {
			$h = fopen($folder.$etalonfile, ($process ? 'ab' : 'wb'));
			if ( ! $h) return false;
			if ( ! $process) {
				fwrite($h, time()."\n\n");
			}
			fwrite($h, $files."\n\n");
			fclose($h);
		}

		if ($flag_max) {
			print '[flag_max]' .BR;

			$res = $this->processFile('w', $etalonfile."\n".$offset);
			if ( ! $res) return false;

			if ($this->autoReload) {
				$this->reloadAction();
			}

		} else {
			$rename = substr($etalonfile, 0, -8);
			rename($folder.$etalonfile, $folder.$rename);
			$this->deleteFile($this->processFile);
		}

		return true;
	}









	// ========================================================================


	function reloadAction($tm=1000)
	{
		print '<script>setTimeout(function(){
			window.location.reload(true);
		}, '.$tm.');</script>';
	}

	
	function processFile($act='r', $text='')
	{
		if ($act == 'w') {
			$h = fopen($this->processFile, 'wb');
			if ( ! $h) return false;
			$res = fwrite($h, $text);
			if ( ! $res) return false;
			fclose($h);

		} else {
			$info = false;
			$foo = $this->filetime($this->processFile);
			if (time() - $foo > 60*60*2) {
				$this->deleteFile($this->processFile);
				return false;
			}
			$h = fopen($this->processFile, 'rb');
			if ( ! $h) return false;
			$info = '';
			while ( ! feof($h))
				$info .= fread($h, 1024*256);
			$info = explode("\n", $info);
			fclose($h);
			return $info;
		}
		return true;
	}
	
	function highlight($file)
	{
		highlight_file($file);
		return true;
	}
	
	function deleteFile($file)
	{
		$res = unlink($file);
		return $res;
	}
	
	function renameFile($file, $newfile)
	{
		if ( ! $newfile) return false;
		$path = substr($file, 0, strrpos($file, '/'));
		$newfile = $path .DS. $newfile;
		$res = rename($file, $newfile);
		return $res;
	}
	
	function chmodFile($file, $chmod='0755')
	{
		$res = chmod($file, intval($chmod,8));
		return $res;
	}
	
	function filetime($file, $type='c')
	{
		switch ($type) {
			case 'a':
				$time = fileatime($file);
				break;
			case 'm':
				$time = filemtime($file);
				break;
			default:
				$time = filectime($file);
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

		@include_once($this->droot.'/core/docs/version.inc.php');
		if (isset($v) && $v['code_name']) {
			$this->cms      = 'revolution';
			$this->cms_ver  = $v['full_version'];
			$this->cms_date = '';
			$this->cms_name = $v['full_appname'];
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

		@include_once($this->droot.'/wp-config.php');
		if(defined('DB_NAME') && defined('DB_USER') &&
			defined('DB_PASSWORD') && defined('DB_HOST')) {
			$this->cms      = 'wordpress';
			$this->cms_ver  = $wp_version;
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

		if ($this->cms == 'revolution') {
			@include_once($this->droot.'/core/config/config.inc.php');
			$this->db_host    = $database_server;
			$this->db_user    = $database_user;
			$this->db_pwd     = $database_password;
			$this->db_name    = $dbase;
			$this->db_method  = 'SET NAMES';
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

		if ($this->cms == 'wordpress') {
			@include_once($this->droot.'/wp-config.php');
			$this->db_host = DB_HOST;
			$this->db_user = DB_USER;
			$this->db_pwd  = DB_PASSWORD;
			$this->db_name = DB_NAME;
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
		$res = $time >= $this->conf('maxtime') ||
			$memory >= $this->conf('maxmemory') ||
			$this->itemscounter >= $this->conf('maxitems')
			? true : false;
		if ($res) {
			$this->log('max | '.$time.' s | '.$memory.' b | '.$this->itemscounter.' i');
		}
		return $res;
	}

	function sizeType($st)
	{
		$sta = array(
			'b', 'Kb', 'Mb', 'Gb', 'Tb'
		);
		return $sta[$st];
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
