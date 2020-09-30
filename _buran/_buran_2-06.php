<?php
/**
 * Buran_0
 * @version 2.06
 * @date 30.07.2020
 * @author <sergey.it@delta-ltd.ru>
 * @copyright 2020 DELTA http://delta-ltd.ru/
 * @size 43333
 *
 */

error_reporting(0);
ini_set('display_errors','off');

define('BR','<br>');

$bu = new BURAN('2.06');

$auth = $bu->auth($_GET['w']);
if ( ! $auth) {
	exit('access denied');
}

if ('phpinfo' == $bu->act) {
	$bu->getphpinfo();
	exit();
}

if (
	'menu'                == $bu->act ||
	'files.manager'       == $bu->act ||
	'file.content'        == $bu->act ||
	'file.chmod'          == $bu->act ||
	'file.delete'         == $bu->act ||
	'file.rename'         == $bu->act ||
	'db.dump'             == $bu->act ||
	'files.backup'        == $bu->act ||
	'etalon.files.show'   == $bu->act ||
	'etalon.files.create' == $bu->act ||
	'etalon.list.compare' == $bu->act ||
	'etalon.list.create'  == $bu->act
) {
?>
<!doctype html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>
<?php
}

if ('menu' == $bu->act) {
?>
<style>
	.itm {
		font-size: 120%;
		margin-bottom: 10px;
	}
</style>

	<div class="itm"><a href="_buran.php?w=<?=$_GET['w']?>&a=info">INFO</a></div>

	<div class="itm"><a href="_buran.php?w=<?=$_GET['w']?>&a=modx_unblock_admin_user">Разблокировать админа MODx</a></div>

	<div class="itm"><a href="_buran.php?w=<?=$_GET['w']?>&a=phpinfo">phpinfo()</a></div>
<?php
	$act_ext = true;
}

if ('files.manager' == $bu->act) {
?>
<style>
	a {
		text-decoration: none;
		color: #00167d;
	}
</style>
	<?php
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
	$res = $bu->filesManager();
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
	$act_ext = true;
}

if ('file.content' == $bu->act) {
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
	$act_ext = true;
}

if ('file.chmod' == $bu->act) {
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
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
	$act_ext = true;
}

if ('file.delete' == $bu->act) {
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
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
	$act_ext = true;
}

if ('file.rename' == $bu->act) {
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
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
	$act_ext = true;
}

if ('db.dump' == $bu->act) {
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
	$res = $bu->dbDump();
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
	$act_ext = true;
}

if ('files.backup' == $bu->act) {
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
	$res = $bu->filesBackup();
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
	$act_ext = true;
}

if (
	'etalon.files.show' == $bu->act ||
	'etalon.files.create' == $bu->act
) {
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
	$create = $bu->act=='etalon.files.create' ? true : false;
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
	$act_ext = true;
}

if ('etalon.list.compare' == $bu->act) {
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
	if ( ! $bu->file) {
		$bu->file = 'etalon_list_0';
	}
	$res = $bu->etalonListCompare();
	print '[finish]' .BR;
	$act_ext = true;
}

if ('etalon.list.create' == $bu->act) {
	print '[start]' .BR;
	print '[time_'.date('Y-m-d-H-i-s').']' .BR;
	$res = $bu->etalonListCreate();
	if ($res) {
		print '[ok]' .BR;
	} else {
		print '[error]' .BR;
	}
	print '[finish]' .BR;
	$act_ext = true;
}

if ($act_ext) {
?>
</body>
</html>
<?php
	exit();
}

if ($bu->act) {
	$bu->htaccess();
	header('Content-Type: text/plain; charset=utf-8');
}

if ('info' == $bu->act) {
	ob_start();
	$p = serialize($bu->info());
	ob_clean();
	print $p;
	exit();
}

if ('update' == $bu->act) {
	$file = $_GET['file'];
	$res = $bu->update($file);
	if ( ! $res) exit('er');
	exit('ok');
}

if ('modx_unblock_admin_user' == $bu->act) {
	$res = $bu->modx_unblock_admin_user();
	if ( ! $res) exit('er');
	exit('ok');
}

if ($bu->act) exit();
?>
<!doctype html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
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

		$('.action').contextmenu(function(){
			let act = $(this).data('act');
			let get = $(this).data('get');
			if (get == undefined) get = '';
			window.open('<?=$bu->scriptname.'?w='.$_GET['w']?>&act='+act+get);
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
		<button class="action" data-act="etalon.files.create" data-get="&auto">Создать эталонные файлы</button>
	</div>

	<div>
		<button class="action" data-act="etalon.list.compare">Сравнить со слепком</button>
		<button class="action" data-act="etalon.list.create" data-get="&auto">Создать слепок</button>
	</div>

	<div>
		<button class="action" data-act="menu">Меню</button>
		<button class="action" data-act="update">Самообновление</button>
	</div>
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
	public $conf = array(
		'maxtime'   => 20,
		'maxmemory' => 209715200, //1024*1024*200
		'maxitems'  => 10000,

		'flag_db_dump'             => true,
		'flag_files_backup'        => true,
		'files_backup_maxpartsize' => 209715200, //1024*1024*200

		'etalon_files_ext' => '/.php/.htaccess/.html/.htm/.js/.inc/.css/.sass/.scss/.less/.tpl/.twig/',
		'etalon_list_ext'  => '/.php/.htaccess/.html/.htm/.js/.inc/.css/.sass/.scss/.less/.tpl/.twig/',

		'backup_files_ext_without' => '',
	);

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

	function __construct($version)
	{
		$this->version = $version;
		$this->module_file = '_buran.php';
		$this->module_folder = '/_buran/buran';
		$this->bunker = 'http://bunker-yug.ru';
		$this->time_start = microtime(true);

		$this->act = $_GET['a'] ? $_GET['a'] : $_GET['act'];

		$this->droot = dirname(dirname(__FILE__));
		$this->broot = dirname(__FILE__);

		$this->http  = (
			(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ||
			(isset($_SERVER['HTTP_PORT']) && $_SERVER['HTTP_PORT']     == '443') ||
			(isset($_SERVER['HTTP_HTTPS']) && $_SERVER['HTTP_HTTPS']   == 'on') ||
			(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ||
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
				$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
				? 'https://' : 'http://');
		$domain = isset($_SERVER['HTTP_HOST'])
			? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		$domain = explode(':',$domain);
		$domain = $domain[0];
		$this->www = strpos($domain,'www.')===0 ? 'www.' : '';
		if($this->www == 'www.') $domain = substr($domain,4);
		$this->domain = $domain;
		$this->scriptname = isset($_SERVER['SCRIPT_NAME'])
						? $_SERVER['SCRIPT_NAME']
						: $_SERVER['PHP_SELF'];
		$this->uri = $_SERVER['REQUEST_URI'];

		$this->iswritable = is_writable($this->droot.$this->module_folder.'/')
			? true : false;
		$this->isreadable = is_readable($this->droot.$this->module_folder.'/')
			? true : false;

		$this->curl_ext = extension_loaded('curl') &&
			function_exists('curl_init') ? true : false;

		$this->sock_ext = function_exists('stream_socket_client')
			? true : false;

		$this->fgc_ext = function_exists('file_get_contents')
			? true : false;

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

		define('DS',DIRECTORY_SEPARATOR);
	}

	// --------------------------------------------

	function dbDump()
	{
		if ( ! $this->conf('flag_db_dump')) return false;

		$this->cms();
		$this->db_access();

		$db = new mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_name);
		if ( ! $db || ! ($db instanceof mysqli)) return false;
		$db->query("{$this->db_method} {$this->db_charset}");

		$folder = $this->droot.$this->module_folder.'/backup/';
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);

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

		$folder = $this->droot.$this->module_folder.'/backup/';
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);

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
		$zip->open($folder.$filepath, ZIPARCHIVE::CREATE | ZipArchive::OVERWRITE);

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
					|| $nextfolder.$file == $this->module_folder
				) continue;
				if (is_dir($this->droot.$nextfolder.$file)) {
					$queue[] = $nextfolder.$file.'/';
					continue;
				}
				if ( ! is_file($this->droot.$nextfolder.$file)) {
					continue;
				}
				$ext = substr($file,strrpos($file,'.'));
				$ext = strtolower($ext);
				if (strpos($this->conf('backup_files_ext_without'),
					'/'.$ext.'/') !== false) {
					continue;
				}

				$fs = filesize($this->droot.$nextfolder.$file);

				if ($fs*1.1 >= $this->conf('files_backup_maxpartsize')) {
					print '[max_fs_'.$nextfolder.$file.']' .BR;
					$this->log('max_filesize | '.$nextfolder.$file.' | '.$fs, 'files_backup');
					continue;
				}
				
				$ii++;
				if ($ii <= $offset) continue;
				$this->itemscounter++;
				
				$zip->addFile($this->droot.$nextfolder.$file, 'www.'.$this->domain.$nextfolder.$file);
				/*$fh = fopen($this->droot.$nextfolder.$file,'rb');
				if ( ! $fh) continue;
				$data = '';
				while ( ! feof($fh)) $data .= fread($fh,1024*256);
				fclose($fh);
				$zip->addFromString('www.'.$this->domain.$nextfolder.$file, $data);*/
				
				$size += $fs;
				
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
		$folder = $this->droot.$this->module_folder.'/etalon/';
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);

		if ($create) {
			$zip = new ZipArchive();
			if ( ! $zip) return false;

			$this->processFile = $folder.'etalon_files_create_process';
			$offset  = 0;
			$info    = $this->processFile();
			if ($info) {
				$zipfile = $info[0];
				$offset  = intval($info[1]);
			} else {
				$zipfile = 'etalon_files_'.date('Y-m-d-H-i-s');
			}
			$zip->open($folder.$zipfile, ZIPARCHIVE::CREATE);
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
				if ( ! is_file($this->droot.$nextfolder.$file)) {
					continue;
				}
				$ext = substr($file,strrpos($file,'.'));
				$ext = strtolower($ext);
				if (strpos($this->conf('etalon_files_ext'),
					'/'.$ext.'/') === false) {
					continue;
				}

				if ($create) {
					$ii++;
					if ($ii <= $offset) continue;
					$this->itemscounter++;
				}

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

		$offset = $ii;

		if ($create) {
			$zip->close();

			print '[offset_'.$offset.']' .BR;
		}

		if ($flag_max) {
			print '[flag_max]' .BR;

			if ($create) {
				$res = $this->processFile('w', $zipfile."\n".$offset);
				if ( ! $res) return false;

				if ($this->autoReload) {
					$this->reloadAction();
				}
			}

		} elseif ($create) {
			$this->deleteFile($this->processFile);
		}

		return true;
	}


	function etalonFilesClear()
	{
		$folder = '/etalon/files';

		$flag_max = false;
		$queue[] = '/';
		do {
			$nextfolder = array_shift($queue);
			if( ! ($open = opendir($this->droot.$this->module_folder.$folder.$nextfolder)))
				continue;
			while ($file = readdir($open)) {
				if (filetype($this->droot.$this->module_folder.$folder.$nextfolder.$file) == 'link'
					|| $file == '.' || $file == '..')
					continue;
				if (is_dir($this->droot.$this->module_folder.$folder.$nextfolder.$file)) {
					$queue[] = $nextfolder.$file.'/';
					continue;
				}
				if( ! is_file($this->droot.$this->module_folder.$folder.$nextfolder.$file))
					continue;

				$file_1 = substr($file, 0, -2);

				if ( ! file_exists($this->droot.$nextfolder.$file_1)) {
					$this->deleteFile($this->droot.$this->module_folder.$folder.$nextfolder.$file);
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
		$folder = $this->droot.$this->module_folder.'/etalon/list/';
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);

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
				$list_3 .= '<div style="font-size:12px;font-family:arial;padding-bottom:2px;">';
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
				$row .= date('d.m.Y, H:i:s', $this->filetime($this->droot.$nextfolder.$file,'a'));
				$row .= ' | ';
				$row .= date('d.m.Y, H:i:s', $this->filetime($this->droot.$nextfolder.$file,'m'));
				$row .= ' | ';
				$row .= date('d.m.Y, H:i:s', $this->filetime($this->droot.$nextfolder.$file));
				$row .= ' | ';
				$row .= filesize($this->droot.$nextfolder.$file);
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
		$folder = $this->droot.$this->module_folder.'/etalon/list/';
		if ( ! file_exists($folder)) mkdir($folder, 0755, true);

		$this->processFile = $folder.'etalon_list_create_process';
		$process    = false;
		$offset     = 0;
		$etalonfile = false;
		$info       = $this->processFile();
		if ($info) {
			$etalonfile = $info[0];
			$offset     = intval($info[1]);
			$process    = true;
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

		print '[offset_'.$offset.']' .BR;

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

	// -------------------------------------------------

	function info()
	{
		$this->cms();
		$this->db_access();

		ob_start();
		$this->getphpinfo(INFO_CONFIGURATION);
		$pi = ob_get_contents();
		ob_end_clean();
		preg_match_all("/\<td.*\>open_basedir\<\/td\>(\<td.*\>(.*)\<\/td\>)(\<td.*\>(.*)\<\/td\>)/U",$pi,$mtchs);

		$info = array(
			'module_ver' => $this->version,
			'tm'         => time(),
			'droot'      => $this->droot,
			'php_ver'    => PHP_VERSION,
			'php_uname'  => php_uname(),
			'php_sapi'   => php_sapi_name(),
			'ws'         => $this->http.$this->www.$this->domain,
			'curl'       => $this->curl_ext,
			'iswritable' => $this->iswritable,
			'isreadable' => $this->isreadable,
			'openbasedir' => array(
				$mtchs[2][0],
				$mtchs[4][0],
			),
			'db_access'  => array(
				'host' => $this->db_host,
				'user' => $this->db_user,
				'pswd' => $this->db_pwd,
				'dbnm' => $this->db_name,
			),
		);
		return $info;
	}

	function getphpinfo($prms=-1)
	{
		phpinfo($prms);
		return true;
	}

	function reloadAction($tm=1000)
	{
		print '<script>setTimeout(function(){
			window.location.reload(true);
		}, '.$tm.');</script>';
		print '[auto_reloaded]' .BR;
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
		if ( ! file_exists($file)) return false;
		switch ($type) {
			case 'a': $time = fileatime($file); break;
			case 'm': $time = filemtime($file); break;
			default: $time = filectime($file);
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

		@include($this->droot.'/sites/default/settings.php');
		if ($drupal_hash_salt && is_array($databases['default']['default'])) {
			$this->cms      = 'drupal';
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
			$this->db_table_prefix = $table_prefix;
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

		if ($this->cms == 'drupal') {
			@include($this->droot.'/sites/default/settings.php');
			$this->db_host = $databases['default']['default']['host'];
			$this->db_user = $databases['default']['default']['username'];
			$this->db_pwd  = $databases['default']['default']['password'];
			$this->db_name = $databases['default']['default']['database'];
		}
	}
	
	// -------------------------------------------------------

	function bufile($type, $act='get', $prm='', $body=false, $base64=false)
	{
		$subfolder = '';
		$base64_e = $base64=='e' ? true : false;
		$base64_d = $base64=='d' ? true : false;
		$filepath = $act == 'file' ? true : false;
		$dirpath = $act == 'dir' ? true : false;
		$set = $act == 'set' ? true : false;
		$get = in_array($act,array('set','file','dir'))
			? false : true;

		if ($get) $body = '';

		$folder = $this->droot.$this->module_folder;

		switch ($type) {
			case 'module':
				$subfolder = '/../';
				$file = $this->module_file;
				break;

			default:
				return false;
		}

		if ($subfolder) $folder .= $subfolder; else $folder .= '/';

		if (($dirpath || $filepath) && ! file_exists($folder)) {
			mkdir($folder, 0755, true);
		}
		if ($dirpath) return $folder;
		if ($filepath) return $folder.$file;

		if ($set) {
			if ($body === false) {
				if (file_exists($folder.$file)) {
					unlink($folder.$file);
					return true;
				}
			}

			if ( ! file_exists($folder)) {
				mkdir($folder, 0755, true);
			}

			if ($serialize) $body = serialize($body);
			if ($base64_e) $body = base64_encode($body);
			if ($base64_d) $body = base64_decode($body);

			if ('module' == $type) {
				if (strpos($body, "<?php\n/**\n * Buran_") !== 0) {
					return false;
				}
				$res = copy($folder.$file,$folder.'_buran_back.php');
				if ( ! $res) return false;
			}

			$fh = fopen($folder.$file,'wb');
			if ( ! $fh) return false;
			$res = fwrite($fh, $body);
			if ($res === false) return false;
			fclose($fh);

			return true;
		}

		if ($get) {
			if ( ! file_exists($folder.$file)) return false;
			$fh = fopen($folder.$file,'rb');
			if ( ! $fh) return false;
			while ( ! feof($fh)) $body .= fread($fh,1024*8);
			fclose($fh);

			if ($base64_e) $body = base64_decode($body);
			if ($serialize) $body = unserialize($body);

			return $body;
		}
	}

	function update($file='')
	{
		$file = str_replace('/','',$file);
		$file = '/buran/update/buran'.($file?'_'.$file:'');
		$bunkerhost = substr($this->bunker,strpos($this->bunker,'://')+3);

		if ($this->curl_ext) {
			$options = array(
				CURLOPT_URL => $this->bunker.$file,
				CURLOPT_RETURNTRANSFER => true,
			);
			$curl = curl_init();
			curl_setopt_array($curl,$options);
			$code = curl_exec($curl);
			$curl_errno = curl_errno($curl);
			curl_close($curl);
		}
		if ($curl_errno && $this->sock_ext) {
			$code = '';
			$headers = "GET ".$this->bunker.$file." HTTP/1.0\n";
			$headers .= "Host: {$bunkerhost}\n\n";
			$res = stream_socket_client($bunkerhost.':80',$errno,$errstr,10);
			if ($res) {
				fwrite($res,$headers);
				while ( ! feof($res)) {
					$code .= fread($res,1024*1024); 
				}
				fclose($res);
				$code = $this->parse_response_headers($code);
				$code = $code[1];
			}
		}
		if ( ! $code && $this->fgc_ext) {
			$code = file_get_contents($this->bunker.$file);
		}
		if ( ! $code) return false;

		$res = $this->bufile('module','set','',$code);
		if ( ! $res) return false;
		return true;
	}

	function modx_unblock_admin_user()
	{
		$cms = $this->cms();
		if (
			$this->cms != 'modx.evo' &&
			$this->cms != 'evolution'
		) return false;

		$this->db_access();

		$db = new mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_name);
		if ( ! $db || ! ($db instanceof mysqli)) return false;
		$db->query("{$this->db_method} {$this->db_charset}");

		$res = $db->query("UPDATE `{$this->db_table_prefix}user_attributes`
			SET blocked='0', blockeduntil='0', blockedafter='0'
			WHERE id=1 LIMIT 1");

		return $res ? true : false;
	}

	function auth($get_w)
	{
		session_name('buran');
		session_start();
		if (time() - $_SESSION['buran']['auth'][$get_w] < 60*30) {
			return true;
		}

		$bunkerhost = substr($this->bunker,strpos($this->bunker,'://')+3);
		$url = '/buran/key.php';
		$url .= '?h='.$this->domain;
		$url .= '&w='.$get_w;

		if ($this->curl_ext) {
			$options = array(
				CURLOPT_URL => $this->bunker.$url,
				CURLOPT_RETURNTRANSFER => true,
			);
			$curl = curl_init();
			curl_setopt_array($curl,$options);
			$ww = curl_exec($curl);
			$curl_errno = curl_errno($curl);
			curl_close($curl);
		}
		if ($curl_errno && $this->sock_ext) {
			$headers = "GET ".$this->bunker.$url." HTTP/1.0\n";
			$headers .= "Host: {$bunkerhost}\n\n";
			$res = stream_socket_client($bunkerhost.':80',$errno,$errstr,10);
			if ($res) {
				fwrite($res,$headers);
				while ( ! feof($res)) {
					$ww .= fread($res,1024*1024); 
				}
				fclose($res);
				$ww = $this->parse_response_headers($ww);
				$ww = $ww[1];
			}
		}
		if ( ! $ww && $this->fgc_ext) {
			$ww = file_get_contents($this->bunker.$url);
		}
		if ($ww && $get_w && $ww === $get_w) {
			$_SESSION['buran']['auth'][$get_w] = time();
			return true;
		}
		unset($_SESSION['buran']);
		return false;
	}

	function parse_response_headers($data)
	{
		$data = str_replace("\r", '', $data);
		$data = explode("\n\n", $data, 2);
		return $data;
	}

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

	function htaccess()
	{
		$htaccess .= 'Order Deny,Allow'."\n";
		$htaccess .= 'Deny from all'."\n";
		$htaccess .= 'RewriteEngine On'."\n";
		$htaccess .= 'RewriteRule ^(.*)$ index.html [L,QSA]'."\n";
		$fh = fopen($this->droot.$this->module_folder.'/.htaccess','wb');
		if ( ! $fh) return false;
		$res = fwrite($fh,$htaccess);
		fclose($fh);
		if ( ! $res) return false;

		$htaccess = 'AddDefaultCharset utf-8'."\n";
		$htaccess .= 'php_value date.timezone Europe/Moscow'."\n";
		$fh = fopen($this->droot.dirname($this->module_folder).'/.htaccess','wb');
		if ( ! $fh) return false;
		$res = fwrite($fh,$htaccess);
		fclose($fh);
		if ( ! $res) return false;

		return true;
	}
}
// ----------------------------------------------
// ----------------------------------------------
// ----------------------------------------------
// -------
