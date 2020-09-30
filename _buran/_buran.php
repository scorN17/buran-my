<?php
/**
 * Buran_0
 * @version 3.0
 * @date 30.09.2020
 * @author <sergey.it@delta-ltd.ru>
 * @copyright 2020 DELTA http://delta-ltd.ru/
 * @size 43333
 *
 */

error_reporting(0);
ini_set('display_errors','off');

$bu = new BURAN('3.0');

$bu->res_ctp = 'json';
$mres = $bu->auth($_GET['w']);
if ($mres['ok'] != 'y') {
	$bu->res['mres'][] = $mres;
	exit();
}

if ( ! $bu->act) exit();

if ('info' == $bu->act) {
	$mres = $bu->info();
	$bu->res['mres'][] = $mres;
}

if ('update' == $bu->act) {
	$file = $_GET['file'];
	$mres = $bu->update($file);
	$bu->res['mres'][] = $mres;
}
if ('setconfig' == $bu->act) {
	$data = $_POST['data'];
	$mres = $bu->setconfig($data);
	$bu->res['mres'][] = $mres;
}

if ('modx_unblock_admin_user' == $bu->act) {
	$mres = $bu->modx_unblock_admin_user();
	$bu->res['mres'][] = $mres;
}

if ('phpinfo' == $bu->act) {
	$bu->res_ctp = 'html';
	$bu->res['data'] = $bu->getphpinfo();
}

if ('db_dump' == $bu->act) {
	$mres = $bu->db_dump();
	$bu->res['mres'][] = $mres;
}

if ('fls_archive' == $bu->act) {
	$mres = $bu->fls_archive();
	$bu->res['mres'][] = $mres;
}

if ('etalon_update' == $bu->act) {
	$withdb = $_GET['withdb']=='y' ? true : false;
	$tozip = isset($_GET['tozip']) && $_GET['tozip']
		? ($_GET['tozip']=='chgs' ? 'chgs' : 'curr') : false;
	$mres = $bu->etalon_update($withdb,$tozip);
	$bu->res['mres'][] = $mres;
}

exit();

// ----------------------------------------------------------

class BURAN
{
	public $conf = array(
		'def' => array(
			'debug' => 0,

			'maxtime'     => 20,
			'maxmemory'   => 209715200, //1024*1024*200
			'maxitems'    => 10000,

			'flag_db_dump'             => true,
			'flag_files_backup'        => true,
			'files_backup_maxpartsize' => 209715200, //1024*1024*200

			'etalon_ext' => '/.php/.htaccess/.html/.htm/.js/.inc/.css/.sass/.scss/.less/.tpl/.twig/',

			'backup_files_ext_without' => '',

			'etalon_mode' => 'all', // [all, list, files]

			'etalon_dir'     => '/etalon',
			'backup_dir'     => '/backup',
			'etalon_db_dir'  => '/db',
			'etalon_db_file' => '/etalon_db_dump.sql',
			'etalon_fls_dir' => '/files',
			'etalon_lst_dir' => '/list',

			'max_etalon_txt_file' => 52428800, //1024*1024*50
		),

		'db' => array(
			'maxitems' => 10000,
		),
	);

	// ----------------------------------------------------

	function __construct($version)
	{
		$this->version = $version;
		$this->mfile   = '_buran.php';
		$this->mdir    = '/_buran/buran';
		$this->bunker  = 'http://bunker-yug.ru';
		$this->mua     = 'BuranModule/'.$version;
		$this->mhash   = md5(__FILE__);

		$this->mct_start = microtime(true);

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
		$this->www = '';
		if (strpos($domain,'www.') === 0) {
			$this->www = 'www.';
			$domain = substr($domain,4);
		}
		$this->domain = $domain;
		$this->scriptname = isset($_SERVER['SCRIPT_NAME'])
						? $_SERVER['SCRIPT_NAME']
						: $_SERVER['PHP_SELF'];
		$this->uri = $_SERVER['REQUEST_URI'];

		$this->iswritable = is_writable($this->droot.$this->mdir.'/')
			? true : false;
		$this->isreadable = is_readable($this->droot.$this->mdir.'/')
			? true : false;

		$this->curl_ext = extension_loaded('curl') &&
			function_exists('curl_init') ? true : false;

		$this->sock_ext = function_exists('stream_socket_client')
			? true : false;

		$this->fgc_ext = function_exists('file_get_contents')
			? true : false;

		if (isset($_GET['uniq'])) {
			$uniq = $_GET['uniq'];
			$uniq = str_replace(array('/','..',' '),'',$uniq);
			$this->uniq = $uniq;
		}

		$userconfig = $this->bufile('config_value','get');
		if (is_array($userconfig)) {
			foreach ($this->conf AS $key => $row) {
				if ( ! $userconfig[$key] || ! is_array($userconfig[$key])) {
					continue;
				}
				$this->conf[$key] = array_merge($this->conf[$key],$userconfig[$key]);
			}
		}

		$res = ob_start(array($this,'ob_end'));
	}

	// --------------------------------------------

	function fls_archive()
	{
		$res = array(
			'method' => 'fls_archive',
			'ok'     => 'n',
		);

		$uniq = $this->uniq ? $this->uniq : date('Y-m-d-H-i-s');

		$dir = $this->conf('backup_dir');
		$folder = $this->droot.$this->mdir.$dir;
		if ( ! file_exists($folder)) mkdir($folder,0755,true);

		$statefile = $dir.'/state_fls_'.$uniq;
		$state = $this->proccess_state($statefile,false,true);
		if ($state === false) {
			$this->res['errors'][] = array('num'=>'0802');
			return $res;
		} elseif ( ! $state) {
			$state_new = true;
			$state = array(
				'part'   => 0,
				'offset' => 0,
			);
		}

		$part = $state['part'];
		$part++;

		$res['state'] = $state;
		$res['uniq']  = $uniq;
		$res['dir']   = $dir;

		$archfile = '/'.$this->domain.'_fls_'.$uniq;
		$archfilepart = $archfile.'_part'.$part.'.zip';
		$zip = new ZipArchive();
		if ($zip && ($zip instanceof ZipArchive)) {
			$this->zip = $zip;
			$res['archfile']     = $archfile;
			$res['archfilepart'] = $archfilepart;
			$this->zip->open($folder.$archfilepart,ZipArchive::CREATE | ZipArchive::OVERWRITE);
			$this->zipfile = $folder.$archfilepart;
		} else {
			$this->zip = false;
			$this->res['errors'][] = array('num'=>'0801');
			return $res;
		}

		$this->max['cntr'][0] = array(
			'nm'  => 'maxitems',
			'max' => $this->conf('maxitems'),
			'cnt' => 0,
		);
		$this->max['cntr'][1] = array(
			'nm'  => 'partsize',
			'max' => $this->conf('files_backup_maxpartsize'),
			'cnt' => 0,
		);

		$queue[] = '/';
		$ii = 0;
		do {
			$nextfolder = array_shift($queue);
			if ( ! ($open = opendir($this->droot.$nextfolder))) {
				continue;
			}
			while ($file = readdir($open)) {
				if (filetype($this->droot.$nextfolder.$file) == 'link'
					|| $file == '.' || $file == '..'
					|| $file == '.th'
					|| $nextfolder.$file == $this->mdir
				) {
					continue;
				}
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
					continue;
				}
				
				$ii++;
				if ($ii <= $state['offset']) continue;
				
				$this->zip->addFile(
					$this->droot.$nextfolder.$file,
					'www.'.$this->domain.$nextfolder.$file
				);
				
				$this->max['cntr'][0]['cnt']++;
				$this->max['cntr'][1]['cnt'] += $fs;
				
				if ($this->max()) {
					$flag_max = true;
					break;
				}
				if ($ii % 2000 == 0) sleep(2);
			}

			if ($this->max()) {
				$flag_max = true;
			}
			if ($flag_max) break;
		} while ($queue[0]);

		$this->zip->close();

		$state['part']   = $part;
		$state['offset'] = $ii;

		if ($this->max['flag']) {
			$res['max'] = true;
			$foores = $this->proccess_state($statefile,$state,true);
			if ( ! $foores) {
				$this->res['errors'][] = array('num'=>'0803');
				return $res;
			}

		} else {
			$res['completed'] = 'y';
			$this->proccess_state($statefile,'rem');
		}

		$res['state'] = $state;
		$res['ok']    = 'y';
		return $res;
	}

	function fls_etalon($tozip='curr')
	{
		$res = array(
			'method' => 'fls_etalon',
			'ok'     => 'n',
		);

		$mode = $this->conf('etalon_mode');
		$mode = $mode=='files'
			? 'files' : ($mode=='list' ? 'list' : 'all');

		$uniq = $this->uniq ? $this->uniq : date('Y-m-d-H-i-s');

		if ($tozip) $tozip = $tozip=='chgs' ? 'chgs' : 'curr';
		if ( ! $this->zip) $tozip = false;

		$dir = $this->conf('etalon_dir').$this->conf('etalon_fls_dir');
		$folder = $this->droot.$this->mdir.$dir;
		if ( ! file_exists($folder)) mkdir($folder,0755,true);

		$statefile = $this->conf('etalon_dir').'/state_fls_'.$uniq;
		$state = $this->proccess_state($statefile,false,true);
		if ($state === false) {
			$this->res['errors'][] = array('num'=>'0901');
			return $res;
		} elseif ( ! $state || ! is_array($state)) {
			$state_new = true;
			$state = array(
				'step'   => array(
					'num' => 1,
					'cnt' => 0,
				),
				'offset' => 0,
				'todo'   => array(),
			);
		}

		$res['state'] = $state;
		$res['uniq']  = $uniq;
		$res['dir']   = $dir;

		$this->max['cntr'][0] = array(
			'nm'  => 'maxitems',
			'max' => $this->conf('maxitems'),
			'cnt' => 0,
		);

		if ($state['step']['num'] === 1) {
			$step_flag = true;
			$state['step']['cnt']++;

			if (in_array($mode,array('all','list'))) {
				$lst_dir = $this->conf('etalon_dir').$this->conf('etalon_lst_dir');
				$lst_folder = $this->droot.$this->mdir.$lst_dir;
				if ( ! file_exists($lst_folder)) mkdir($lst_folder,0755,true);
				$lst_file = '/etalon_list';
				$allfile  = '__all';
				$chgsfile = '__chgs';
				$procfile = '_process';

				$fszhs = array(
					'hash',
					'fctm',
					'size',
					'file',
				);

				if ('chgs' == $tozip) {
					$lst_file_all_prev_fh = file_exists($lst_folder.$lst_file.$allfile)
						? fopen($lst_folder.$lst_file.$allfile,'rb') : false;

					$lst_file_chgs_fh = fopen($lst_folder.$lst_file.$chgsfile.$procfile,
						($state['step']['cnt'] === 1 ? 'wb' : 'ab')
					);
					if ($lst_file_chgs_fh) {
						if ($state['step']['cnt'] === 1) {
							$fres = fputcsv($lst_file_chgs_fh,$fszhs,';');
						}
					} else {
						$this->res['errors'][] = array('num'=>'0905');
						return $res;
					}

					if (
						$state['step']['cnt'] === 1
						&& $lst_file_all_prev_fh
					) {
						while ($row = fgetcsv($lst_file_all_prev_fh)) {
							$row = str_getcsv($row[0],';');

							if (
								substr($row[3],0,1) == '/'
								&& ! file_exists($this->droot.$row[3])
							) {
								$fszhs2 = array(
									isset($row[0]) ? $row[0] : '',
									isset($row[1]) ? $row[1] : '',
									isset($row[2]) ? $row[2] : '',
									'-->',
									'',
									'',
									'',
									$row[3],
								);
								$fres = fputcsv($lst_file_chgs_fh,$fszhs2,';');
							}
						}
					}
				}

				$lst_file_all_fh = fopen($lst_folder.$lst_file.$allfile.$procfile,
					($state['step']['cnt'] === 1 ? 'wb' : 'ab')
				);
				if ($lst_file_all_fh) {
					if ($state['step']['cnt'] === 1) {
						$fres = fputcsv($lst_file_all_fh,$fszhs,';');
					}
				} else {
					$this->res['errors'][] = array('num'=>'0905');
					return $res;
				}
			}
		}

		$queue[] = '/';
		$ii = 0;
		do {
			if ($state['step']['num'] !== 1) break;

			$nextfolder = array_shift($queue);
			if ( ! ($open = opendir($this->droot.$nextfolder))) {
				continue;
			}
			while ($file = readdir($open)) {
				if (filetype($this->droot.$nextfolder.$file) == 'link'
					|| $file == '.' || $file == '..'
					|| $file == '.th'
					|| (
						strpos(
							$nextfolder.$file,
							$this->mdir.$this->conf('etalon_dir').'/'
						) === 0
						&& strpos(
							$nextfolder.$file.'/',
							$this->mdir.$dir.'/'
						) !== 0
					)
				) {
					continue;
				}
				if (is_dir($this->droot.$nextfolder.$file)) {
					$queue[] = $nextfolder.$file.'/';
					continue;
				}
				if ( ! is_file($this->droot.$nextfolder.$file)) {
					continue;
				}

				$ii++;
				if ($ii <= $state['offset']) continue;
				$this->max['cntr'][0]['cnt']++;

				$isetalon = strpos($nextfolder.$file,$this->mdir.$dir.'/') === 0
					? true : false;

				if ($isetalon) {
					if ( ! in_array($mode,array('all','files'))) {
						continue;
					}

					$origfile = substr($nextfolder,strlen($this->mdir.$dir));
					$origfile .= substr($file,0,-2);

					$ext = substr($origfile,strrpos($origfile,'.'));
					$ext = strtolower($ext);
					$is_etalon_ext = strpos($this->conf('etalon_ext'),'/'.$ext.'/') !== false
						? true : false;

					if (
						! $is_etalon_ext
						|| ! file_exists($this->droot.$origfile)
					) {
						if ('chgs' == $tozip) {
							$zipres = $this->zip->addFile(
								$this->droot.$nextfolder.$file,
								'www.'.$this->domain.$dir.'/a'.$origfile
							);
							$state['todo'][] = array(
								'unlink',
								$nextfolder.$file,
							);
						} else {
							unlink($this->droot.$nextfolder.$file);
						}
					}
					continue;
				}

				$ext = substr($file,strrpos($file,'.'));
				$ext = strtolower($ext);
				$is_etalon_ext = strpos($this->conf('etalon_ext'),'/'.$ext.'/') !== false
					? true : false;

				$etalonfolder = $folder.$nextfolder;
				$etalonfile = $etalonfolder.$file.'_0';

				$size_0 = false;
				$hash_0 = false;
				$size = filesize($this->droot.$nextfolder.$file);
				$hash = md5_file($this->droot.$nextfolder.$file);
				$fctm = $this->filetime($this->droot.$nextfolder.$file);

				if ($size > $this->conf('max_etalon_txt_file')) {
					$is_etalon_ext = false;
				}

				if (in_array($mode,array('all','list'))) {
					$fszhs = array(
						$hash,
						$fctm,
						$size,
						$nextfolder.$file,
					);
					$fres = fputcsv($lst_file_all_fh,$fszhs,';');

					$changed = true;
					if ('chgs' == $tozip) {
						$changed = false;
						$prev = false;
						if ($lst_file_all_prev_fh) {
							rewind($lst_file_all_prev_fh);
							while ($row = fgetcsv($lst_file_all_prev_fh)) {
								if ( ! strpos($row[0],$nextfolder.$file)) continue;
								$row = str_getcsv($row[0],';');
								if ($row[3] != $nextfolder.$file) continue;
								$prev = $row;
								break;
							}
						}
						if ($prev) {
							if ($prev[2] == $size) {
								if ($prev[0] != $hash) {
									$changed = true;
								}
							} else $changed = true;
						} else $changed = true;
						if ($changed) {
							$fszhs = array(
								isset($prev[0]) ? $prev[0] : '',
								isset($prev[1]) ? $prev[1] : '',
								isset($prev[2]) ? $prev[2] : '',
								'-->',
								$hash,
								$fctm,
								$size,
								$nextfolder.$file,
							);
							$fres = fputcsv($lst_file_chgs_fh,$fszhs,';');
						}
					}
				}

				$changed = false;
				if (
					$is_etalon_ext
					&& in_array($mode,array('all','files'))
				) {

					$etalonfile_ex = file_exists($etalonfile);
					if ($etalonfile_ex) {
						$size_0 = filesize($etalonfile);
						if ($size === $size_0) {
							$hash_0 = md5_file($etalonfile);
							if ($hash != $hash_0) {
								$changed = true;
							}
						} else $changed = true;
					} else $changed = true;

					if ($changed) {
						if ( ! file_exists($etalonfolder)) {
							mkdir($etalonfolder,0755,true);
						}

						$state['todo'][] = array(
							'update',
							$nextfolder.$file,
						);
					}

					if ($changed && 'chgs' == $tozip) {
						$this->zip->addFile(
							$etalonfile,
							'www.'.$this->domain.$dir.'/a'.$nextfolder.$file
						);
					}

					if ($tozip) {
						if ($changed || 'curr' == $tozip) {
							$this->zip->addFile(
								$this->droot.$nextfolder.$file,
								'www.'.$this->domain.$dir.'/b'.$nextfolder.$file
							);
						}
					}
				}
				
				if ($this->max()) {
					$flag_max = true;
					break;
				}
				if ($ii % 2000 == 0) sleep(2);
			}

			if ($this->max()) {
				$flag_max = true;
			}
			if ($flag_max) break;
		} while ($queue[0]);

		if ($step_flag) {
			if ($lst_file_all_prev_fh) fclose($lst_file_all_prev_fh);
			if ($lst_file_prev_fh) fclose($lst_file_prev_fh);
			if ($lst_file_chgs_fh) fclose($lst_file_chgs_fh);
			if ($lst_file_all_fh) fclose($lst_file_all_fh);

			if ($this->max['flag']) {
				$state['offset'] = $ii;
			} else {
				$state['step'] = array(
					'num' => $state['step']['num']+1,
					'cnt' => 0,
				);
				$state['offset'] = 0;

				if (in_array($mode,array('all','list'))) {
					copy(
						$lst_folder.$lst_file.$allfile.$procfile,
						$lst_folder.$lst_file.'_'.$uniq
					);
					rename(
						$lst_folder.$lst_file.$allfile.$procfile,
						$lst_folder.$lst_file.$allfile
					);

					if ('chgs' == $tozip) {
						rename(
							$lst_folder.$lst_file.$chgsfile.$procfile,
							$lst_folder.$lst_file.$chgsfile
						);

						$this->zip->addFile(
							$lst_folder.$lst_file.$chgsfile,
							'www.'.$this->domain.$lst_dir.$lst_file.$chgsfile
						);

					} elseif ($tozip) {
						$this->zip->addFile(
							$lst_folder.$lst_file.$allfile,
							'www.'.$this->domain.$lst_dir.'/b'.$lst_file.$allfile
						);
					}
				}
			}

			if ($tozip) {
				$this->zip->close();
			}
		}

		$ii = 0;
		if (
			$state['step']['num'] === 2
			&& isset($state['todo'])
			&& is_array($state['todo'])
		) {
			$step_flag = true;
			$state['step']['cnt']++;

			foreach ($state['todo'] AS $row) {
				$ii++;
				if ($ii <= $state['offset']) continue;
				$this->max['cntr'][0]['cnt']++;

				$etalonfile = $folder.$row[1].'_0';

				if ('update' == $row[0]) {
					$cpres = copy(
						$this->droot.$row[1],
						$etalonfile
					);
					if ( ! $cpres) {
						$this->res['errors'][] = array('num'=>'0902');
					}

				} elseif ('unlink' == $row[0]) {
					if (strpos($row[1],$this->mdir.$dir.'/') === 0) {
						unlink($this->droot.$row[1]);
					}
				}

				if ($this->max()) {
					$flag_max = true;
				}
				if ($flag_max) break;
			}

			$state['offset'] = $ii;
		}

		if ($step_flag) {
			if ($this->max['flag']) {
				$res['max'] = true;

			} else {
				$state['step'] = array(
					'num' => $state['step']['num']+1,
					'cnt' => 0,
				);
				$state['offset'] = 0;
			}

			$foores = $this->proccess_state($statefile,$state,true);
			if ( ! $foores) {
				$this->res['errors'][] = array('num'=>'0904');
				return $res;
			}

		} else {
			$res['completed'] = 'y';
			$this->proccess_state($statefile,'rem');
		}

		unset($state['todo']);

		$res['state'] = $state;
		$res['ok']    = 'y';
		return $res;
	}

	function db_dump($mode='archive')
	{
		$res = array(
			'method' => 'db_dump',
			'ok'     => 'n',
		);

		$procfile = '_process';

		$uniq = $this->uniq ? $this->uniq : date('Y-m-d-H-i-s');

		if ('etalon' == $mode) {
			$dir = $this->conf('etalon_dir').$this->conf('etalon_db_dir');
			$dumpfile = $this->conf('etalon_db_file');
			$statefile = $this->conf('etalon_dir').'/state_db_'.$uniq;
		} else {
			$dir = $this->conf('backup_dir');
			$dumpfile = '/'.$this->domain.'_db_'.$uniq.'.sql';
			$statefile = $dir.'/state_db_'.$uniq;
		}
		$folder = $this->droot.$this->mdir.$dir;
		if ( ! file_exists($folder)) mkdir($folder,0755,true);

		$state = $this->proccess_state($statefile,false,true);
		if ($state === false) {
			$this->res['errors'][] = array('num'=>'0101');
			return $res;
		} elseif ( ! $state || ! is_array($state)) {
			$state_new = true;
			$state = array(
				'tbl'    => false,
				'offset' => 0,
			);
		}

		$res['state']    = $state;
		$res['uniq']     = $uniq;
		$res['dir']      = $dir;
		$res['dumpfile'] = $dumpfile;

		$fres = $this->cms();
		if ( ! $fres) {
			$this->res['errors'][] = array('num'=>'0102');
			return $res;
		}
		$fres = $this->db_access();
		if ( ! $fres) {
			$this->res['errors'][] = array('num'=>'0103');
			return $res;
		}
		$dbcres = $this->db_connect();
		if ($dbcres['ok'] != 'y') return $res;

		$dbres = $this->db->query("SHOW TABLES");
		if ( ! $dbres) {
			$this->res['errors'][] = array('num'=>'0104');
			return $res;
		}

		$this->max['cntr'][0] = array(
			'nm'  => 'maxitems',
			'max' => $this->conf('maxitems','db'),
			'cnt' => 0,
		);

		$dump = "# -- start / ".date('d.m.Y, H:i:s')."\n\n";
		while ($row = $dbres->fetch_row()) {

			if (
				$state['tbl']
				&& $row[0] != $state['tbl']
			) continue;
			$ii_tbl = $row[0];
			$state['tbl'] = false;

			if ( ! $state['offset']) {
				$dump .= "# ---------------------------- `".$row[0]."`"."\n\n";

				$dbres2 = $this->db->query("SHOW CREATE TABLE `{$row[0]}`");
				if ( ! $dbres2) {
					$this->res['errors'][] = array('num'=>'0105');
					continue;
				}

				$row2 = $dbres2->fetch_row();
				$dump .= "DROP TABLE IF EXISTS `{$row[0]}`;"."\n";
				$dump .= $row2[1].";"."\n\n";
			}

			$dbres2 = $this->db->query("SELECT * FROM `{$row[0]}`");
			if ( ! $dbres2) {
				$this->res['errors'][] = array('num'=>'0106');
				continue;
			}

			$ii = 0;
			while ($row2 = $dbres2->fetch_assoc()) {
				$ii++;
				if ($ii <= $state['offset']) continue;
				$this->max['cntr'][0]['cnt']++;

				$dump .= "INSERT INTO `{$row[0]}` SET ";

				$first = true;
				foreach ($row2 AS $key => $val) {
					$val = $this->db->real_escape_string($val);
					$dump .= ($first ? "" : ",")."`{$key}`='{$val}'";
					$first = false;
				}
				$dump .= ";"."\n";

				if ($this->max()) {
					$flag_max = true;
					break;
				}
			}

			$dump .= "\n";

			if ( ! $this->max['flag']) {
				$state['offset'] = 0;
			}

			if ($this->max()) {
				$flag_max = true;
			}
			if ($flag_max) break;
		}
		$dump .= "# -- the end / ".date('d.m.Y, H:i:s')."\n\n";

		$fh = fopen($folder.$dumpfile.$procfile,($state_new?'wb':'ab'));
		if ( ! $fh) {
			$this->res['errors'][] = array('num'=>'0107');
			return $res;
		}
		$fwres = fwrite($fh,$dump);
		fclose($fh);
		if ( ! $fwres) {
			$this->res['errors'][] = array('num'=>'0108');
			return $res;
		}

		$state['tbl']    = $ii_tbl;
		$state['offset'] = $ii;

		if ($this->max['flag']) {
			$res['max'] = true;

			$foores = $this->proccess_state($statefile,$state,true);
			if ( ! $foores) {
				$this->res['errors'][] = array('num'=>'0109');
				return $res;
			}

		} else {
			$foores = rename(
				$folder.$dumpfile.$procfile,
				$folder.$dumpfile
			);
			if ( ! $foores) {
				$this->res['errors'][] = array('num'=>'0110');
				return $res;
			}

			$res['completed'] = 'y';
			$this->proccess_state($statefile,'rem');
		}

		$res['state'] = $state;
		$res['ok']    = 'y';
		return $res;
	}

	function etalon_update($withdb=true, $tozip='curr')
	{
		$res = array(
			'method' => 'etalon_update',
			'ok'     => 'n',
		);

		if ($tozip) $tozip = $tozip=='chgs' ? 'chgs' : 'curr';

		$dir = $this->conf('etalon_dir');
		$folder = $this->droot.$this->mdir.$dir;
		if ( ! file_exists($folder)) mkdir($folder,0755,true);

		$uniq = $this->uniq ? $this->uniq : date('Y-m-d-H-i-s');

		$statefile = $dir.'/state_chgs_'.$uniq;
		$state = $this->proccess_state($statefile,false,true);
		if ($state === false) {
			$this->res['errors'][] = array('num'=>'1001');
			return $res;
		} elseif ( ! $state) {
			$state_new = true;
			$state = array(
				'step' => array(
					'num' => 1,
					'cnt' => 0,
				),
			);
		}

		$res['state'] = $state;
		$res['uniq']  = $uniq;
		$res['dir']   = $dir;
		
		if ($tozip) {
			$procfile = '_process';
			$archfile = '/'.$this->domain.'_etalon_'.$tozip.'_'.$uniq.'.zip';
			$zip = new ZipArchive();
			if ($zip && ($zip instanceof ZipArchive)) {
				$this->zip = $zip;
				$res['archfile'] = $archfile;
				$ziptp = $state['step']['num'] === 1
					? ZipArchive::CREATE | ZipArchive::OVERWRITE
					: ZipArchive::CREATE;
				$this->zip->open($folder.$archfile.$procfile,$ziptp);
				$this->zipfile = $folder.$archfile.$procfile;
			} else {
				$this->zip = false;
				$this->res['errors'][] = array('num'=>'1002');
				$tozip = false;
			}
		}

		if ($withdb && $state['step']['num'] === 1) {
			$step_flag = true;
			$state['step']['cnt']++;

			$dir_dir = $dir.$this->conf('etalon_db_dir');
			$dir_folder = $this->droot.$this->mdir.$dir_dir;
			$dumpfile = $this->conf('etalon_db_file');
			if (
				'chgs' == $tozip
				&& $state['step']['cnt'] == 1
				&& file_exists($dir_folder.$dumpfile)
			) {
				$this->zip->addFile(
					$dir_folder.$dumpfile,
					'www.'.$this->domain.$dir_dir.'/a'.$dumpfile
				);
				$this->zip->close();
				$this->zip->open($this->zipfile);
			}

			$dumpres = $this->db_dump('etalon');

			$dumpdata = $dumpres['dump'];
			unset($dumpres['dump']);
			$res['stepres'] = $dumpres;

			if ($this->max['flag']) {
			} else {
				if ($tozip) {
					$this->zip->addFile(
						$dir_folder.$dumpfile,
						'www.'.$this->domain.$dir_dir.'/b'.$dumpfile
					);
					$this->zip->close();
				}
				$step_next = true;
			}

			if ($dumpres['ok'] != 'y') {
				$step_next = true;
			}

		} elseif ($state['step']['num'] === 1) {
			$state = array(
				'step' => array(
					'num' => $state['step']['num']+1,
					'cnt' => 0,
				),
			);
		}

		if ($state['step']['num'] === 2) {
			$step_flag = true;
			$state['step']['cnt']++;

			$fetres = $this->fls_etalon($tozip);

			$res['stepres'] = $fetres;

			if ($fetres['completed'] == 'y') {
				$step_next = true;
			}
			if ($fetres['ok'] != 'y') {
				$step_next = true;
			}
		}

		if ($step_flag) {
			if ($step_next) {
				$res['nextstep'] = true;
				$state = array(
					'step' => array(
						'num' => $state['step']['num']+1,
						'cnt' => 0,
					),
				);
			}
			$foores = $this->proccess_state($statefile,$state,true);
			if ( ! $foores) {
				$this->res['errors'][] = array('num'=>'1003');
				return $res;
			}
		} else {
			$foores = rename(
				$folder.$archfile.$procfile,
				$folder.$archfile
			);
			if ( ! $foores) {
				$this->res['errors'][] = array('num'=>'1004');
				return $res;
			}

			$res['completed'] = 'y';
			$this->proccess_state($statefile,'rem');
		}

		$res['state'] = $state;
		$res['ok'] = 'y';
		return $res;
	}

	// -------------------------------------------------

	function ob_end($data)
	{
		if ($this->conf('debug')) return false;

		if ( ! is_array($this->res)) $this->res = array();
		$this->res['tm'] = time();
		$this->res['ok'] = 'y';
		if (is_array($this->res['mres'])) {
			foreach ($this->res['mres'] AS $mres) {
				if ($mres['ok'] != 'y') {
					$this->res['ok'] = 'n';
					break;
				}
			}
		}
		if ('html' == $this->res_ctp) {
			$res_data = $this->res['data'];
			header('Content-Type: text/html; charset=utf-8');
		} else {
			$res_data = json_encode($this->res);
			header('Content-Type: text/plain; charset=utf-8');
		}
		return $res_data;
	}

	function info()
	{
		$res = array(
			'method' => 'info',
			'ok' => 'n',
		);

		$this->cms();
		$this->db_access();

		$pi = $this->getphpinfo(INFO_CONFIGURATION);
		preg_match_all("/\<td.*\>open_basedir\<\/td\>(\<td.*\>(.*)\<\/td\>)(\<td.*\>(.*)\<\/td\>)/U",$pi,$mtchs);

		$zipisavailable = false;
		if (class_exists('ZipArchive')) {
			$zip = new ZipArchive();
			if ($zip && ($zip instanceof ZipArchive)) {
				$zipisavailable = true;
			}
		}

		$info = array(
			'module_ver' => $this->version,
			'modulefile' => __FILE__,
			'droot'      => $this->droot,
			'php_ver'    => PHP_VERSION,
			'php_uname'  => php_uname(),
			'php_sapi'   => php_sapi_name(),
			'ws'         => $this->http.$this->www.$this->domain,
			'curl'       => $this->curl_ext,
			'sock'       => $this->sock_ext,
			'fgc'        => $this->fgc_ext,
			'iswritable' => $this->iswritable,
			'isreadable' => $this->isreadable,
			'zipisavailable' => $zipisavailable,
			'cms'        => array(
				'cms'      => $this->cms,
				'cms_ver'  => $this->cms_ver,
				'cms_date' => $this->cms_date,
				'cms_name' => $this->cms_name,
			),
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

		$res['info'] = $info;
		$res['ok'] = 'y';
		return $res;
	}

	function getphpinfo($prms=-1)
	{
		ob_start();
		phpinfo($prms);
		$p = ob_get_contents();
		ob_end_clean();
		return $p;
	}

	function proccess_state($proc, $data=false, $serz=false)
	{
		$file = $this->droot.$this->mdir.$proc;
		if ('rem' == $data) {
			$res = unlink($file);
			return $res;
		}
		if ($data === false) {
			if ( ! file_exists($file)) return;
			$fh = fopen($file,'rb');
			if ( ! $fh) return false;
			$res = '';
			while ( ! feof($fh)) {
				$res .= fread($fh,1024*256);
			}
			fclose($fh);
			if ($serz) $res = unserialize($res);
			return $res;
		}
		$fh = fopen($file,'wb');
		if ( ! $fh) return false;
		if ($serz) $data = serialize($data);
		$res = fwrite($fh,$data);
		if ( ! $res) return false;
		fclose($h);
		return true;
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
		ob_start();
		@include_once($this->droot.'/manager/includes/version.inc.php');
		ob_end_clean();
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

		ob_start();
		@include_once($this->droot.'/core/docs/version.inc.php');
		ob_end_clean();
		if (isset($v) && $v['code_name']) {
			$this->cms      = 'revolution';
			$this->cms_ver  = $v['full_version'];
			$this->cms_date = '';
			$this->cms_name = $v['full_appname'];
			return true;
		}

		ob_start();
		@include_once($this->droot.'/configuration.php');
		ob_end_clean();
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

		ob_start();
		@include_once($this->droot.'/config.php');
		ob_end_clean();
		if (defined('DB_DRIVER') && defined('DB_HOSTNAME') &&
			defined('DB_USERNAME') && defined('DB_PASSWORD') &&
			defined('DB_DATABASE')) {
			$this->cms      = 'opencart2';
			$this->cms_ver  = '';
			$this->cms_date = '';
			$this->cms_name = '';
			return true;
		}

		ob_start();
		@include_once($this->droot.'/bootstrap.php');
		ob_end_clean();
		if (defined('HOSTCMS')) {
			$this->cms      = 'hostcms';
			$this->cms_ver  = '';
			$this->cms_date = '';
			$this->cms_name = '';
			return true;
		}

		ob_start();
		@include_once($this->droot.'/wp-config.php');
		ob_end_clean();
		if (defined('DB_NAME') && defined('DB_USER') &&
			defined('DB_PASSWORD') && defined('DB_HOST')) {
			$this->cms      = 'wordpress';
			$this->cms_ver  = $wp_version;
			$this->cms_date = '';
			$this->cms_name = '';
			return true;
		}

		ob_start();
		@include($this->droot.'/sites/default/settings.php');
		ob_end_clean();
		if ($drupal_hash_salt && is_array($databases['default']['default'])) {
			$this->cms      = 'drupal';
			$this->cms_ver  = '';
			$this->cms_date = '';
			$this->cms_name = '';
			return true;
		} elseif (isset($db_url) && $db_url) {
			$this->cms      = 'drupal_old';
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
			ob_start();
			@include_once($this->droot.'/manager/includes/config.inc.php');
			ob_end_clean();
			$this->db_host    = $database_server;
			$this->db_user    = $database_user;
			$this->db_pwd     = $database_password;
			$this->db_name    = trim($dbase,"`");
			$this->db_method  = $database_connection_method;
			$this->db_charset = $database_connection_charset;
			$this->db_table_prefix = $table_prefix;
			return true;
		}

		if ($this->cms == 'revolution') {
			ob_start();
			@include_once($this->droot.'/core/config/config.inc.php');
			ob_end_clean();
			$this->db_host    = $database_server;
			$this->db_user    = $database_user;
			$this->db_pwd     = $database_password;
			$this->db_name    = $dbase;
			$this->db_method  = 'SET NAMES';
			$this->db_charset = $database_connection_charset;
			return true;
		}
		
		if ($this->cms == 'joomla') {
			ob_start();
			@include_once($this->droot.'/configuration.php');
			ob_end_clean();
			$conf = new JConfig();
			$this->db_host = $conf->host;
			$this->db_user = $conf->user;
			$this->db_pwd  = $conf->password;
			$this->db_name = $conf->db;
			return true;
		}

		if ($this->cms == 'opencart2') {
			ob_start();
			@include_once($this->droot.'/config.php');
			ob_end_clean();
			$this->db_host = DB_HOSTNAME;
			$this->db_user = DB_USERNAME;
			$this->db_pwd  = DB_PASSWORD;
			$this->db_name = DB_DATABASE;
			return true;
		}

		if ($this->cms == 'hostcms') {
			ob_start();
			$ret = require($this->droot.'/modules/core/config/database.php');
			ob_end_clean();
			$this->db_host = $ret['default']['host'];
			$this->db_user = $ret['default']['username'];
			$this->db_pwd  = $ret['default']['password'];
			$this->db_name = $ret['default']['database'];
			return true;
		}

		if ($this->cms == 'wordpress') {
			ob_start();
			@include_once($this->droot.'/wp-config.php');
			ob_end_clean();
			$this->db_host = DB_HOST;
			$this->db_user = DB_USER;
			$this->db_pwd  = DB_PASSWORD;
			$this->db_name = DB_NAME;
			return true;
		}

		if ($this->cms == 'drupal') {
			ob_start();
			@include($this->droot.'/sites/default/settings.php');
			ob_end_clean();
			$this->db_host = $databases['default']['default']['host'];
			$this->db_user = $databases['default']['default']['username'];
			$this->db_pwd  = $databases['default']['default']['password'];
			$this->db_name = $databases['default']['default']['database'];
			return true;
		}

		if ($this->cms == 'drupal_old') {
			ob_start();
			@include($this->droot.'/sites/default/settings.php');
			ob_end_clean();
			$databases = parse_url($db_url);
			$this->db_host = $databases['host'];
			$this->db_user = $databases['user'];
			$this->db_pwd  = $databases['pass'];
			$this->db_name = substr($databases['path'],1);
			return true;
		}
		return false;
	}

	function db_connect()
	{
		$res = array(
			'method' => 'db_connect',
			'ok' => 'n',
		);
		if ($this->db && ($this->db instanceof mysqli)) {
			$res['ok'] = 'y';
			return $res;
		}
		$db = new mysqli($this->db_host,$this->db_user,$this->db_pwd,$this->db_name);
		if ( ! $db || ! ($db instanceof mysqli)) {
			$this->res['errors'][] = array('num'=>'0701');
			return $res;
		}
		$dbres = $db->query("{$this->db_method} {$this->db_charset}");
		if ( ! $dbres) {
			$this->res['errors'][] = array('num'=>'0702');
			return $res;
		}
		$this->db = $db;
		$res['ok'] = 'y';
		return $res;
	}

	function bufile($type, $act='get', $prm='', $body=false, $base64=false)
	{
		$res = array(
			'method' => 'bufile',
		);
		$subfolder = '';
		$base64_e = $base64=='e' ? true : false;
		$base64_d = $base64=='d' ? true : false;
		$filepath = $act == 'file' ? true : false;
		$dirpath = $act == 'dir' ? true : false;
		$set = $act == 'set' ? true : false;
		$get = in_array($act,array('set','file','dir'))
			? false : true;

		if ($get) $body = '';

		$folder = $this->droot.$this->mdir;

		switch ($type) {
			case 'module':
				$subfolder = '/../';
				$file = $this->mfile;
				break;

			case 'config':
				$file = $type.'.txt';
				break;
			case 'config_value':
				$serialize = true;
				$base64_e = true;
				$file = 'config.txt';
				break;

			default:
				return false;
		}

		if ($subfolder) $folder .= $subfolder; else $folder .= '/';

		if (($dirpath || $filepath) && ! file_exists($folder)) {
			mkdir($folder,0755,true);
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
				mkdir($folder,0755,true);
			}
			if ($serialize) $body = serialize($body);
			if ($base64_e) $body = base64_encode($body);
			if ($base64_d) $body = base64_decode($body);

			if ('module' == $type) {
				if (strpos($body, "<?php\n/**\n * Buran_") !== 0) {
					$this->res['errors'][] = array('num'=>'0601');
					return false;
				}
				$cres = copy($folder.$file,$folder.'_buran_back.php');
				if ( ! $cres) {
					$this->res['errors'][] = array('num'=>'0602');
					return false;
				}
			}

			if ('config' == $type) {
				$tmp = base64_decode($body);
				if ($body && $tmp) {
					$tmp = unserialize($tmp);
					if ( ! is_array($tmp)) {
						$this->res['errors'][] = array('num'=>'0603');
						return false;
					}
				} else {
					$this->res['errors'][] = array('num'=>'0604');
					return false;
				}
			}

			$fh = fopen($folder.$file,'wb');
			if ( ! $fh) {
				$this->res['errors'][] = array('num'=>'0605');
				return false;
			}
			$res = fwrite($fh,$body);
			if ($res === false) {
				$this->res['errors'][] = array('num'=>'0606');
				return false;
			}
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
		$res = array(
			'method' => 'update',
			'ok' => 'n',
		);
		$file = preg_replace("/[^a-z0-9\-_]/",'',$file);
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
			if ($curl_errno) {
				$code = false;
				$this->res['errors'][] = array(
					'num' => '0501',
					'info' => $curl_errno,
				);
			}
		}
		if ( ! $code && $this->sock_ext) {
			$code = '';
			$headers = "GET ".$this->bunker.$file." HTTP/1.0\n";
			$headers .= "Host: {$bunkerhost}\n\n";
			$sockres = stream_socket_client($bunkerhost.':80',$errno,$errstr,10);
			if ($sockres) {
				fwrite($sockres,$headers);
				while ( ! feof($sockres)) {
					$code .= fread($sockres,1024*1024); 
				}
				fclose($sockres);
				$code = $this->parse_response_headers($code);
				$code = $code[1];
			}
		}
		if ( ! $code && $this->fgc_ext) {
			$code = file_get_contents($this->bunker.$file);
		}
		if ($code) {
			$fres = $this->bufile('module','set','',$code);
			if ($fres) $res['ok'] = 'y';
			else $this->res['errors'][] = array('num'=>'0502');
		} else {
			$this->res['errors'][] = array('num'=>'0503');
		}
		return $res;
	}

	function setconfig($data)
	{
		$res = array(
			'method' => 'setconfig',
			'ok' => 'n',
		);
		$fres = $this->bufile('config','set','',$data);
		if ($fres) $res['ok'] = 'y';
		else $this->res['errors'][] = array('num'=>'0401');
		return $res;
	}

	function modx_unblock_admin_user()
	{
		$res = array(
			'method' => 'modx_unblock_admin_user',
			'ok' => 'n',
		);
		$cms = $this->cms();
		if (
			! $cms
			|| (
				$this->cms != 'modx.evo'
				&& $this->cms != 'evolution'
			)
		) {
			$this->res['causes'][] = array('num'=>'0303');
			return $res;
		}
		$dbres = $this->db_access();
		if ( ! $dbres) {
			$this->res['errors'][] = array('num'=>'0301');
			return $res;
		}
		$dbres = $this->db_connect();
		if ($dbres['ok'] != 'y') return $res;
		$dbres = $this->db->query("UPDATE `{$this->db_table_prefix}user_attributes`
			SET blocked='0', blockeduntil='0', blockedafter='0'
			WHERE id=1 LIMIT 1");
		if ( ! $dbres) {
			$this->res['errors'][] = array('num'=>'0302');
			return $res;
		}
		$res['ok'] = 'y';
		return $res;
	}

	function auth($get_w)
	{
		$res = array(
			'method' => 'auth',
			'ok' => 'n',
		);
		session_name('buran');
		session_start();

		if (time() - $_SESSION['buran']['auth'][$get_w] < 60*30) {
			$res['ok'] = 'y';
			return $res;
		}

		unset($_SESSION['buran']);
		$this->htaccess();

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
			if ($curl_errno) {
				$ww = false;
				$this->res['errors'][] = array(
					'num' => '0201',
					'info' => $curl_errno,
				);
			}
		}
		if ( ! $ww && $this->sock_ext) {
			$headers = "GET ".$this->bunker.$url." HTTP/1.0\n";
			$headers .= "Host: {$bunkerhost}\n\n";
			$sockres = stream_socket_client($bunkerhost.':80',$errno,$errstr,10);
			if ($sockres) {
				fwrite($sockres,$headers);
				while ( ! feof($sockres)) {
					$ww .= fread($sockres,1024*1024); 
				}
				fclose($sockres);
				$ww = $this->parse_response_headers($ww);
				$ww = $ww[1];
			} else {
				$this->res['errors'][] = array('num'=>'0202');
			}
		}
		if ( ! $ww && $this->fgc_ext) {
			$ww = file_get_contents($this->bunker.$url);
		}
		if ($ww && $get_w && $ww === $get_w) {
			$_SESSION['buran']['auth'][$get_w] = time();
			$res['ok'] = 'y';
		}
		return $res;
	}

	function parse_response_headers($data)
	{
		$data = str_replace("\r",'',$data);
		$data = explode("\n\n",$data,2);
		return $data;
	}

	function conf($name,$tp='def')
	{
		return isset($this->conf[$tp][$name]) ? $this->conf[$tp][$name] : NULL;
	}

	function max()
	{
		$mct = $this->mct_passed();
		$memory = memory_get_peak_usage(true);
		$res = false;
		if (is_array($this->max['cntr'])) {
			foreach ($this->max['cntr'] AS $row) {
				if ($row['cnt'] >= $row['max']) {
					$res = true;
					break;
				}
			}
		}
		if (
			$mct >= $this->conf('maxtime')
			|| $memory >= $this->conf('maxmemory')
		) $res = true;
		if ($res) {
			$this->max['flag'] = true;
			$this->res['max'] = array(
				'flg' => true,
				'mct' => $mct,
				'mem' => $memory,
				'cnt' => $this->max['cntr'],
			);
		}
		return $res;
	}

	function mct_passed($m='start', $set_last=false)
	{
		$mct = microtime(true);
		if ('last' == $m) {
			if ( ! $this->mct_passed_last) {
				$this->mct_passed_last = $this->mct_start;
			}
			$res = $mct - $this->mct_passed_last;
		} else {
			$res = $mct - $this->mct_start;
		}
		if ($set_last) $this->mct_passed_last = $mct;
		$res = round($res,4);
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
		$fh = fopen($this->droot.$this->mdir.'/.htaccess','wb');
		if ( ! $fh) return false;
		$res = fwrite($fh,$htaccess);
		fclose($fh);
		if ( ! $res) return false;

		$htaccess = 'AddDefaultCharset utf-8'."\n";
		$htaccess .= 'php_value date.timezone Europe/Moscow'."\n";
		$fh = fopen($this->droot.dirname($this->mdir).'/.htaccess','wb');
		if ( ! $fh) return false;
		$res = fwrite($fh,$htaccess);
		fclose($fh);
		if ( ! $res) return false;

		return true;
	}
}
// -----------------------------
