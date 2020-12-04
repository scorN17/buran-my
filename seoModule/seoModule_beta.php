<?php
/**
 * seoModule
 * @version 5.984-b
 * @date 04.12.2020
 * @author <sergey.it@delta-ltd.ru>
 * @copyright 2021 DELTA http://delta-ltd.ru/
 * @size 69000
 */

$bsm = new buran_seoModule('5.984-b');

if ( ! $bsm->module_mode) {
	$bsm->init();

} else {
	error_reporting(0);
	ini_set('display_errors', 'off');

	if ('list' == $_GET['a']) {
		header('Content-type: text/html; charset=utf-8');
		print $bsm->alist();
		exit();
	}

	if ('phpinfo' == $_GET['a']) {
		if ( ! ($bsm->auth($_GET['w']))) exit('er');
		phpinfo();
		exit();
	}
	
	header('Content-Type: text/plain; charset=utf-8');

	if ('info' == $_GET['a']) {
		print $bsm->info_parse();
		exit();
	}

	if ('update' == $_GET['a']) {
		if ( ! ($bsm->auth($_GET['w']))) exit('er');
		$bsm->cache_clear();
		$bsm->htaccess();
		$type  = $_GET['t'];
		$code  = $_POST['c'];
		$alias = preg_replace("/[^a-z0-9_]/", '', $_GET['n']);
		if ('imgs' == $type) {
			$code = array(
				'filescount' => $_POST['fs'],
				'files'      => $_FILES,
			);
		}
		$res = $bsm->bsmfile($type, 'set', $alias, $code);
		if ( ! $res) exit('er');
		exit('ok');
	}

	if ('upgrade' == $_GET['a']) {
		if ( ! ($bsm->auth($_GET['w']))) exit('er');
		$bsm->cache_clear();
		$bsm->htaccess();
		$res = $bsm->upgrade();
		if ( ! $res) exit('er');
		exit('ok');
	}

	if ('deactivate' == $_GET['a']) {
		if ( ! ($bsm->auth($_GET['w']))) exit('er');
		$module_hash = $bsm->droot.$bsm->module_folder.'/'.$bsm->module_hash;
		if (file_exists($module_hash)) {
			unlink($module_hash);
			$bsm->log('[07]');
		}
		exit('ok');
	}

	if ('clearlog' == $_GET['a']) {
		if ( ! ($bsm->auth($_GET['w']))) exit('er');
		$bsm->log('', '', $_GET['t'], true);
		exit('ok');
	}

	if ('clearcache' == $_GET['a']) {
		$bsm->cache_clear();
		exit('ok');
	}

	if ('reverse' == $_GET['a']) {
		$res = $bsm->send_reverse_request(true);
		print $res ? 'ok' : 'er';
		exit();
	}

	if ('transit' == $_GET['a']) {
		if ( ! ($bsm->auth($_GET['w']))) exit();
		if ( ! $_GET['u']) exit();
		$post = $bsm->requestmethod == 'POST' ? $_POST : false;
		$headers = $_GET['h'] ? $_GET['h'] : false;
		if ($headers) {
			$headers = base64_decode($headers);
			$headers = unserialize($headers);
			if ( ! is_array($headers)) $headers = false;
		}
		$reqres = $bsm->request(
			$_GET['u'], false, $headers, $post
		);
		$res = '';
		if ($reqres['res']) {
			$headers = serialize($reqres['headers']);
			$headers = base64_encode($headers);
			$info    = serialize($reqres['info']);
			$info    = base64_encode($info);
			$res     = $info."\n\n".$headers."\n\n".$reqres['response'];
		}
		print $res;
		exit();
	}

	if ('transit_list' == $_GET['a']) {
		if ( ! ($bsm->auth($_GET['w']))) exit();
		print $bsm->bsmfile('transit', 'get');
		exit();
	}

	if ('watch' == $_GET['a']) {
		session_name('sssm');
		session_start();
		$tm = isset($_SESSION['buranseomodule']['watch'][$_GET['u']])
			? intval($_SESSION['buranseomodule']['watch'][$_GET['u']]) : 0;
		if (time() - $tm > 60) exit();
		unset($_SESSION['buranseomodule']['watch'][$_GET['u']]);
		$alias = preg_replace("/[^a-z0-9_]/", '', $_GET['b']);
		$info = $bsm->bsmfile('txt_info', 'get', $alias);
		$info['seotext_js'] .= $_GET['s'] == 'y' ? 'y' : 'n';
		$bsm->bsmfile('txt_info', 'set', $alias, $info);

		if ($bsm->c[2]['reverse_requests']) {
			$bsm->send_reverse_request();
		}
		if ($bsm->c[2]['transit_requests']) {
			$bsm->transit_list_check();
		}

		exit();
	}

	if ('auth' == $_GET['a']) {
		if ( ! ($bsm->auth($_GET['w']))) exit('er');
		$_SESSION['buranseomodule']['auth'] = time();
		exit('ok');
	}

	exit('er');
}

// ------------------------------------------------------------------

class buran_seoModule
{
	public $version;

	public $c = false;
	public $module_hash;
	public $module_hash_flag = false;
	public $module_file;
	public $module_folder;

	public $droot;
	public $website;
	public $http;
	public $www;
	public $domain;
	public $domain_h;
	public $requesturi;
	public $requestmethod;
	public $pageurl;
	public $querystring;
	public $protocol;
	public $protocol_dop = false;
	public $sapi_name;

	public $accesscode = false;

	public $test = false;
	public $test_stitle;
	public $test_stext;
	public $test_beforecode;

	public $declension;

	public $seotext = false;
	public $seotext_cache = false;
	public $seotext_alias;
	public $seotext_tp;
	public $seotext_tpl;
	public $seotext_hide;
	public $seotext_date;
	public $seotext_info;
	public $charset;
	public $module_ua;

	public $template;
	public $headers;
	public $tag_s = false;
	public $tag_f = false;
	public $code = array();
	
	public $ob_start_flags;

	public $curl_ext;
	public $sock_ext;
	public $fgc_ext;

	public $logs_files;

	public $curl_request_headers = array();

	function __construct($version)
	{
		$this->mct_start = microtime(true);

		$this->version = $version;

		$this->module_file   = 'seoModule.php';
		$this->module_folder = '/_buran/seoModule';
		$this->module_ua     = 'BuranSeoModule';

		$this->droot = dirname(dirname(__FILE__));

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
		$domain = explode(':', $domain);
		$domain = $domain[0];
		$this->www = '';
		if (strpos($domain,'www.') === 0) {
			$this->www = 'www.';
			$domain = substr($domain, 4);
		}
		$this->domain        = $domain;
		$this->domain_h      = md5($domain);
		$this->website       = $this->http . $this->www . $this->domain;
		$this->requesturi    = $_SERVER['REQUEST_URI'];
		$this->requestmethod = $_SERVER['REQUEST_METHOD'];
		$this->pageurl       = parse_url($this->requesturi, PHP_URL_PATH);
		$this->querystring   = substr($this->requesturi, strpos($this->requesturi, '?')+1);
		$this->sapi_name     = php_sapi_name();
		if (substr($this->sapi_name,0,3) == 'cgi') {
			$this->protocol = 'Status:';
		} else {
			$this->protocol =
				isset($_SERVER['HTTP_X_PROTOCOL']) && $_SERVER['HTTP_X_PROTOCOL']
				? $_SERVER['HTTP_X_PROTOCOL']
				: (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL']
					? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		}
		if (isset($_POST['seomodule_test']) &&
			isset($_POST['s_title']) && isset($_POST['s_text'])) {
			$this->test            = true;
			$this->test_stitle     = $_POST['s_title'];
			$this->test_stext      = $_POST['s_text'];
			$this->test_beforecode = $_POST['before_code'];
		}

		$this->module_mode = strpos($this->requesturi, dirname($this->module_folder).'/'.$this->module_file) === 0
			? true : false;
		$this->module_hash = md5(__FILE__);
		if (file_exists($this->droot.$this->module_folder.'/'.$this->module_hash)) {
			$this->module_hash_flag = true;
		} else {
			$this->log('[02]');
		}

		$this->curl_ext = extension_loaded('curl') &&
			function_exists('curl_init') ? true : false;

		$this->sock_ext = function_exists('stream_socket_client') ? true : false;

		$this->fgc_ext = function_exists('file_get_contents') ? true : false;


		$this->useragent = false;
		if (stripos($_SERVER['HTTP_USER_AGENT'],'google.com/bot') !== false) {
			$this->useragent = 'google';
		} elseif (stripos($_SERVER['HTTP_USER_AGENT'],'yandex.com/bots') !== false) {
			$this->useragent = 'yandex';
		} elseif (stripos($_SERVER['HTTP_USER_AGENT'],'xenu') !== false) {
			$this->useragent = 'xenu';
		} elseif (stripos($_SERVER['HTTP_USER_AGENT'],'screaming frog seo spider') !== false) {
			$this->useragent = 'sfss';
		} elseif (stripos($_SERVER['HTTP_USER_AGENT'],'siteanalyzerbot') !== false) {
			$this->useragent = 'siteanalyzer';
		}

		$this->c = $this->config();

		if ($this->c[2]['accesscode']) {
			$this->accesscode = $this->c[2]['accesscode'];
		}

		if ($this->c[2]['dop_protocol']) {
			$this->protocol_dop = $this->c[2]['dop_protocol'];
		}

		$this->ob_start_flags = $this->c[2]['ob_start_flags']
			? $this->c[2]['ob_start_flags'] : 112;

		if ($this->c[2]['urldecode']) {
			$this->requesturi = urldecode($this->requesturi);
		}

		if (isset($this->c[7][$this->c[1]['city']])) {
			$this->declension = $this->c[7][$this->c[1]['city']];
		}

		if ($this->c[2]['ignore_errors']) {
			$this->c[2]['ignore_errors'] = str_replace(' ','',$this->c[2]['ignore_errors']);
		}

		if (isset($_GET['proxy'])) {
			$this->c[2]['proxy'] = $_GET['proxy'];
		}
		if ($this->c[2]['proxy']) {
			$this->c[2]['proxy'] = str_replace('-',':',$this->c[2]['proxy']);
		}

		$charsetlist = array(
			'utf-8' => array(
				/*0*/ 'Статьи',
				/*1*/ 'Все статьи',
				/*2*/ 'Дата',
				/*3*/ 'публикации',
				/*4*/ 'изменения',
				/*5*/ 'рисунок',
				/*6*/ 'фото',
				/*7*/ 'Автор',
			),
			'cp1251' => array(
				/*0*/ '0fLg8vzo',
				/*1*/ 'wvHlIPHy4PL86A==',
				/*2*/ 'xODy4A==',
				/*3*/ '7/Ph6+jq4Pbo6A==',
				/*4*/ '6Ofs5e3l7ej/',
				/*5*/ '8Ojx8+3u6g==',
				/*6*/ '9O7y7g==',
				/*7*/ 'wOLy7vA=',
			),
		);
		$this->charset = $charsetlist[$this->c[2]['out_charset']][0]
			? $charsetlist[$this->c[2]['out_charset']]
			: $charsetlist['utf-8'];
		if ($this->c[2]['out_charset'] != 'utf-8' && is_array($this->charset)) {
			foreach ($this->charset AS $key => $txt) {
				$this->charset[$key] = base64_decode($txt);
			}
		}

		$this->regmask = array(
			'h1'          => "/<h1(.*)>(.*)<\/h1>/isU",
			'base'        => "/<base (.*)>/iU",
			'title'       => "/(<title>(.*)<\/title>)(.*<\/head>)/isU",

			'canonical'   => "/<link (.*)rel=('|\")canonical('|\")(.*)>/iU",
			'canonical_v' => "/href=('|\")(.*)('|\")/isU",

			'description' => "/<meta [.]*name=('|\")description('|\")(.*)>/isU",
			'description_v' => "/content=('|\")(.*)('|\")/isU",

			'keywords'    => "/<meta [.]*name=('|\")keywords('|\")(.*)>/isU",
			'keywords_v'    => "/content=('|\")(.*)('|\")/isU",
		);

		if ( ! $this->module_mode) $this->bsm_sqlite();
		if ($this->db_op) {
			if ($this->useragent) {
				$ip = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
				$res = $this->db->querySingle("SELECT id FROM bots
					WHERE
						day = '".date('Y-m-d')."'
						AND bot = '{$this->useragent}'
						AND ip = '{$ip}'
					LIMIT 1");
				if ($res) {
					$this->db->query("UPDATE bots
						SET cnt = cnt+1
						WHERE id = '{$res}'");
				} elseif ($res !== false) {
					$this->db->query("INSERT INTO bots
						(day, bot, ip, cnt)
						VALUES (
							'".date('Y-m-d')."',
							'{$this->useragent}',
							'{$ip}',
							'1'
						)");
				}
			}

			$requesturi = $this->db->escapeString($this->requesturi);
			$referer = $this->db->escapeString($_SERVER['HTTP_REFERER']);
			$res = $this->db->querySingle("SELECT id FROM url_refr
				WHERE url='{$requesturi}' AND referer='{$referer}'
				LIMIT 1");
			if ($res) {
				$this->db->query("UPDATE url_refr SET
						freq = ((freq+(".time()."-lastload))/2),
						lastload = '".time()."'
					WHERE id = '{$res}'");
			} elseif ($res !== false) {
				$this->db->query("INSERT INTO url_refr
					(url, referer, freq, lastload)
					VALUES (
						'{$requesturi}',
						'{$referer}',
						'".(60*60*24)."',
						'".time()."'
					)");
			}

			$this->bsm_sqlite(true);
		}
	}

	function config()
	{
		$config_default = array(
			1 => array(
				'website'      => $this->http.$this->www.$this->domain,
				'articles'     => '',
				'bunker_id'    => '',
				'city'         => '',
				'date_start'   => '',
				'company_name' => '',
				'logo'         => '',
				'phone'        => '',
				'address'      => '',
			),
			2 => array(
				'module_enabled'                  => '0',
				'accesscode'                      => '',
				'include_files'                   => '/index.php',
				'launch_exceptions'               => '',
				'transit_requests'                => 1,
				'out_charset'                     => 'utf-8',
				'reverse_requests'                => '',
				'proxy'                           => '',
				'classname'                       => '',
				'starttag_title'                  => '',
				'starttag_breadcrumbs'            => '',
				'bcrumbs_after_h1'                => '',
				'template_coding'                 => '1',
				'dop_protocol'                    => '',
				'ob_start_flags'                  => 112,
				'base'                            => '0',
				'canonical'                       => 'replace_or_add',
				'meta'                            => 'replace_or_add',
				'meta_neseo'                      => 'add_if_not_exists',
				'canonical_neseo'                 => 'add_if_not_exists',
				'disable_stext'                   => '',
				're_linking'                      => 2,
				're_linking_without_stext'        => '',
				'page_without_stit_to_re_linking' => '',
				'hide_opt'                        => '',
				'urldecode'                       => 1,
				'redirect'                        => 1,
				'domain_redirect'                 => 1,
				'ignore_errors'                   => '',
				'city_replace'                    => '',
				'use_cache'                       => 604800,
				'requets_methods'                 => '/GET/HEAD/',
				'error_handler'                   => '',
				'share_code'                      => '<script>(function(b,d,s,m,k,a){setTimeout(function(){k=d.createElement(s);a=d.getElementsByTagName(s)[0];k.src=m;a.parentNode.insertBefore(k,a);},2000);})(window,document,"script","https://yastatic.net/share2/share.js");</script><div class="ya-share2" data-curtain data-services="vkontakte,facebook,odnoklassniki,messenger,telegram,twitter,viber,whatsapp,moimir,skype,evernote,reddit"></div>',
			),
			4 => array(
				'/index.php'  => '/',
				'/index.html' => '/',
			),
			12 => array(
				'obrabotka'      => '',
				'o_canonical'    => '',
				'o_micromarking' => '',
			),
			16 => array(
				'bc_1' => '',
			),
		);

		$config = $this->bsmfile('config_value', 'get');

		if ($config && is_array($config)) {
			foreach ($config AS $key => $row) {
				if ( ! isset($config_default[$key])) {
					$config_default[$key] = $row;
				} else {
					$config_default[$key]
						= array_merge($config_default[$key], $row);
				}
			}
		} else {
			$this->log('[03]');
		}
		return $config_default;
	}

	function init()
	{
		if (
			! $this->test
			&& (
				strpos($this->c[2]['requets_methods'],
					'/'.$this->requestmethod.'/') === false
				|| (
					$this->c[2]['module_enabled'] !== '1'
					&& $_SERVER['REMOTE_ADDR'] !== $this->c[2]['module_enabled']
				)
			)
		) {
			if (
				! $this->test
				&& $this->c[2]['module_enabled'] !== '1'
				&& $_SERVER['REMOTE_ADDR'] !== $this->c[2]['module_enabled']
			) $this->log('[01]');
			return false;
		}

		if ($this->c[2]['launch_exceptions']) {
			$paths = $this->c[2]['launch_exceptions'];
			$paths = explode('  ', $paths);
			if (is_array($paths)) {
				foreach ($paths AS $path) {
					$path = trim($path);
					if ( ! $path) continue;
					if (stripos($this->requesturi, $path) === 0) {
						$this->log('[08]');
						return false;
					}
				}
			}
		}

		if ($this->c[2]['redirect']) {
			$this->redirects();
		}

		if (strpos($_SERVER['HTTP_USER_AGENT'], $this->module_ua) !== false) {
			return false;
		}

		if (
			$this->c[2]['error_handler']
			&& function_exists('set_error_handler')
		) {
			set_error_handler(array($this,'error_handler'),E_ALL & ~E_NOTICE);
		}

		$this->clear_request();
		$this->seotext();

		$res = ob_start(array($this,'ob_end'),0,$this->ob_start_flags);
		if ($res !== true) {
			$this->log('[04]');
		}
	}

	function redirects()
	{
		$redirect_ws = $this->c[2]['domain_redirect']
			? $this->c[1]['website'] : $this->website;

		$redirect_to = $this->requesturi;
		if (
			isset($this->c[4][$redirect_to])
			&& $this->c[4][$redirect_to]
		) {
			$redirect_to = $this->c[4][$redirect_to];
		}
		foreach ($this->c[4] AS $from => $to) {
			if (substr($from,0,1) !== '+') continue;
			$from = substr($from,1);
			if (preg_match($from, $redirect_to) === 1) {
				$redirect_to = preg_replace($from, $to, $redirect_to);
			}
		}
		if (
			isset($this->c[4][$redirect_to])
			&& $this->c[4][$redirect_to]
		) {
			$redirect_to = $this->c[4][$redirect_to];
		}
		if ($redirect_to == $this->requesturi) $redirect_to = false;

		if ( ! $redirect_to && $redirect_ws !== $this->website) {
			$redirect_to = $this->requesturi;
		}
		
		if ($redirect_to) {
			header('Location: '.$redirect_ws.$redirect_to, true, 301);
			exit();
		}
	}

	function clear_request()
	{
		$patt = "/((&|^)(_openstat|utm_.*|yclid|ymclid|gclid|fbclid)=.*)(&|$)/U";
		while (preg_match($patt, $this->querystring, $matches) === 1) {
			$this->querystring = preg_replace($patt, '${4}', $this->querystring);
			if (strpos($this->querystring,'&') === 0) {
				$this->querystring = substr($this->querystring, 1);
			}
			$this->requesturi = $this->pageurl.
				($this->querystring ? '?'.$this->querystring : '');
		}
	}

	function seotext()
	{
		if ( ! is_array($this->c[3])) {
			return false;
		}
		$seotext_alias = false;
		$seotext_tp    = false;
		$seotext_tit   = 'd';
		$seotext_site  = 'd';
		$seotext_hide  = 'D';
		$flag = false;
		foreach ($this->c[3] AS $alias => $prms) {
			if ($this->requesturi != $prms[0]) continue;

			$seotext_alias = $alias;
			$seotext_tpl   = $prms['tpl']=='n' ? false : true;
			$seotext_hide  = $prms[2]=='h' ? 'Y'
				: ($prms[2]=='s' ? 'N' : 'D');

			if (isset($prms['tit']) && in_array($prms['tit'],array('n','h1','h2h1'))) {
				$seotext_tit = $prms['tit'];
			}

			if (isset($prms['st']) && in_array($prms['st'],array('s','sf','f'))) {
				$seotext_site = $prms['st'];
			}

			if ($flag) {
				$this->log('[13]');
				break;
			}
			$flag = true;
		}
		if ( ! $seotext_alias) return false;
		$this->seotext_alias = $seotext_alias;

		$text = $this->seofile($seotext_alias);
		if ( ! $text && $this->seotext_cache) {
			$text = $this->seofile($seotext_alias, false);
		}
		if ( ! $text) return false;

		if ( ! isset($text['type']) || ! $text['type']) {
			$text['type'] = $seotext_tp;
		}
		$text['type'] = $text['type']=='S' ? 'S' : 'A';
		$this->seotext_tp = $text['type'];

		$hide_flag = $this->c[2]['hide_opt'] === '1' ? 'Y'
			: ( ! $this->c[2]['hide_opt'] || $this->c[2]['hide_opt'] === '0'
				? 'N'
				: (strpos($this->c[2]['hide_opt'], $this->seotext_tp) !== false
					? 'Y' : 'N'));
		$hide_flag = $seotext_hide == 'Y' ? 'Y'
			: ($seotext_hide == 'N' ? 'N' : $hide_flag);
		$this->seotext_hide = $hide_flag;

		$this->seotext = $text;
		$this->seotext_tpl = $seotext_tpl;
		$this->seotext_date = date('Y-m-d', $this->filetime($text['file']));

		$this->seotext_tit  = $seotext_tit;
		$this->seotext_site = $seotext_site;

		if ($this->requestmethod != 'HEAD') {
			$this->seotext_info = $this->bsmfile('txt_info', 'get', $seotext_alias);
			if (
				! $this->seotext_info ||
				! is_array($this->seotext_info) ||
				! isset($this->seotext_info['last'])
			) {
				$this->seotext_info = array(
					'last'           => time()-(60*60*24),
					'type'           => '',
					'interval'       => 0,
					'seotext_exists' => '',
					'seotext_js'     => '',
				);
			}
			$sr = time() - $this->seotext_info['last'];
			$this->seotext_info['interval'] = intval(($this->seotext_info['interval']+$sr)/2);
			$this->seotext_info['last'] = time();
			$this->seotext_info['seotext_exists'] .= '-';
			if (strlen($this->seotext_info['seotext_exists']) > 100) {
				$this->seotext_info['seotext_exists']
					= substr($this->seotext_info['seotext_exists'], 4);
			}
			if (strlen($this->seotext_info['seotext_js']) > 100) {
				$this->seotext_info['seotext_js']
					= substr($this->seotext_info['seotext_js'], 4);
			}
			$this->bsmfile('txt_info', 'set', $seotext_alias, $this->seotext_info);
		}

		return true;
	}

	function seofile($alias, $use_cache=true)
	{
		if ( ! $this->c[2]['use_cache']) {
			$use_cache = false;
		}
		$file = $this->bsmfile('text_value', 'file', $alias);
		$ft_t = $this->filetime($file);
		$ft_c = $this->filetime($this->bsmfile('txt_cache', 'file', $alias));

		if (
			$use_cache &&
			$ft_c &&
			time()-$ft_c <= $this->c[2]['use_cache'] &&
			$ft_c > $ft_t
		) {
			$from_cache = true;
			$this->seotext_cache = true;
			$text = $this->bsmfile('txt_cache', 'get', $alias);

		} else {
			$from_cache = false;
			$text = $this->bsmfile('text_value', 'get', $alias);
		}

		if ( ! isset($text['file']) || ! $text['file']) {
			$text['file'] = $file;
		}
		if (
			! is_array($text)
			|| ! isset($text['title'])
			|| ! isset($text['description'])
			|| ! isset($text['keywords'])
			|| ! isset($text['s_title'])
			|| ! isset($text['s_text'])
		) {
			if ( ! $from_cache) {
				$this->log('[10]');
			}
			return false;
		}
		return $text;
	}

	function template_coding($template, $act='en')
	{
		if ($this->c[2]['template_coding'] == '2') {
			if ('de' == $act) {
				$template = gzinflate(substr($template,10));
			} else {
				$template = gzencode($template);
			}

		} else {
			if ('de' == $act) {
				$template = zlib_decode($template);
			} else {
				$template = zlib_encode($template, ZLIB_ENCODING_GZIP);
			}
		}
		return $template;
	}

	function ob_end($template)
	{
		if ( ! $this->module_hash_flag) {
			return false;
		}

		error_reporting(0);
		ini_set('display_errors', 'off');

		session_write_close();
		session_name('sssm');
		session_start();

		if (headers_sent()) {
			$this->log('[22]');
		}
		
		$template_orig = $template;
		$this->tpl_modified = false;

		$this->http_code = 0;
		$this->http_code_200_exists = false;
		if (function_exists('http_response_code')) {
			$this->http_code = http_response_code();
		}
		if ($this->http_code != 404) {
			$headers_list = headers_list();
			if (is_array($headers_list)) {
				foreach ($headers_list AS $row) {
					if (stripos($row, '404 not found') !== false) {
						$this->http_code = 404;
					} elseif (stripos($row, '200 ok') !== false) {
						$this->http_code = 200;
						$this->http_code_200_exists = true;
					} elseif (
						stripos($row, 'http/1') !== false ||
						stripos($row, 'status:') !== false
					) {
						$this->http_code = 1;
					}
				}
			}
		}
		if ($this->seotext) {
			if ( ! $this->http_code) {
				$this->http_code = 200;
			}
			if ($this->http_code == 404) {
				$this->seotext_tp = 'S';
				$this->seotext['type'] = 'S';
			}
			if (
				$this->http_code != 200
				&& $this->http_code != 404
			) {
				$this->log('[20]');
				$this->save_url_info();
				return false;
			}
		}

		$gzip = strcmp(substr($template,0,2),"\x1f\x8b") ? false : true;
		if ($gzip) $template = $this->template_coding($template,'de');
		
		if ( ! $template) {
			$this->log('[21]');
			$this->save_url_info();
			return false;
		}

		$res = $this->meta_parse($template);
		if ($res) {
			$template = $res;
			$this->tpl_modified = true;
		}

		if ( ! $this->seotext) {
			$this->save_url_info($template);

			if ($this->tpl_modified) {
				if (function_exists('header_remove')) {
					header_remove('Content-Length');
				}
				if ($gzip) $template = $this->template_coding($template,'en');
				return $template;
			} else return false;
		}

		if ( ! $this->seotext_cache || $this->test) {
			$this->text_parse();
			if ($this->requesturi == $this->c[1]['articles']) {
				$this->articles_parse();
			} elseif ($this->c[2]['re_linking']) {
				$with_stext_only = $this->c[2]['re_linking_without_stext'] ? false : true;
				$this->articles_parse($this->seotext_alias, $this->c[2]['re_linking'], $with_stext_only);
			}
		}

		if ($this->seotext['s_text']) {
			$tags1 = $this->get_tag($template, 'finish');
			if ($tags1) {
				$tags2 = $this->get_tag($template, 'start');
			}
			if (
				! $tags1
				|| ($this->seotext_tp == 'S' && ! $tags2)
			) {
				$this->log('[40]');
				$this->save_url_info($template);
				return false;
			}
		} else {
			if ($this->seotext_tp == 'S') {
				$this->log('[41]');
				$this->save_url_info($template);
				return false;
			}
		}
		
		$template = $this->template_parse($template);
		$this->tpl_modified = true;

		if ( ! $this->http_code_200_exists || $this->http_code == 404) {
			header($this->protocol.' 200 OK');
			if ($this->protocol_dop) {
				header($this->protocol_dop.' 200 OK');
			}
		}

		if ( ! $this->seotext['cache'] && $this->c[2]['use_cache']) {
			$this->cache_save($this->seotext_alias);
		}
		
		if (
			time() - $this->seotext_info['check']['meta_robots'] > 60*60*6
			&& preg_match_all("/\<meta .*\>/isU", $template, $matches)
			&& is_array($matches)
		) {
			$this->seotext_info['check']['meta_robots'] = time();
			foreach ($matches[0] AS $m) {
				if (
					stripos($m,'noindex') !== false
					|| stripos($m,'nofollow') !== false
					|| stripos($m,'none') !== false
				) {
					$this->log('[51]');
					break;
				}
			}
		}

		if ($this->requestmethod != 'HEAD') {
			if (
				$this->seotext_info['type'] &&
				$this->seotext_info['type'] != $this->seotext_tp
			) {
				if ('S' == $this->seotext_tp) {
					$this->log('[14]');
				} else {
					$this->log('[15]');
				}
			}
			$this->seotext_info['type'] = $this->seotext_tp;
			$this->seotext_info['seotext_exists'] .= '+';
			$this->seotext_info['seotext_js'] .= '+';
			$this->bsmfile('txt_info', 'set', $this->seotext_alias, $this->seotext_info);
		}

		if (function_exists('header_remove')) {
			header_remove('Content-Length');
		}
		$this->save_url_info($template);
		if ($gzip) $template = $this->template_coding($template,'en');
		return $template;
	}

	function get_tag($template, $type='finish')
	{
		$list = $this->c[6][$type];
		if ( ! is_array($list)) {
			return false;
		}
		foreach ($list AS $row) {
			$tag = preg_quote($row[1],"/");
			$tag = str_replace("\n", '\n', $tag);
			$tag = str_replace("\r", '', $tag);
			$tag = str_replace("\t", '\t', $tag);
			$res = preg_match("/".$tag."/s", $template);
			if ($res === 1) {
				$foo = array(
					'p' => $row[0],
					't' => $row[1],
					'm' => $tag,
				);
				if ($type == 'start')  $this->tag_s = $foo;
				if ($type == 'finish') $this->tag_f = $foo;
				return true;
			}
		}
		return false;
	}

	function text_parse()
	{
		$st = &$this->seotext;

		if ($this->test) {
			$st['s_title']     = $this->test_stitle;
			$st['s_text']      = $this->test_stext;
			$st['before_code'] = $this->test_beforecode;
		}

		$st['flag_multitext'] = strpos($st['s_text'], '[part]') !== false
			? true : false;

		$st['s_text'] = str_replace('<p>[img]</p>', '[img]', $st['s_text']);
		$st['s_text'] = str_replace('<p>[col]</p>', '[col]', $st['s_text']);
		$st['s_text'] = str_replace('<p>[part]</p>', '[part]', $st['s_text']);

		$s_img_f = array();
		if (is_array($st['s_img'])) {
			foreach ($st['s_img'] AS $key => $row) {
				$img = $this->module_folder.'/img/'.$this->seotext_alias.'_'.($key+1);
				if (file_exists($this->droot.$img.'.jpg')) {
					$img .= '.jpg';
				} elseif (file_exists($this->droot.$img.'.png')) {
					$img .= '.png';
				} else {
					continue;
				}
				$s_img_f[] = array(
					'src' => $img,
					'alt' => $row,
				);
			}
		}

		$flag_dopimgs = false;
		$i = 0;
		while ($img = array_shift($s_img_f)) {
			$img_p = '';
			if ($img['src']) {
				$i++;
				$img['attr'] = $img['alt'].' ('.($i==1
					? $this->charset[5] : $this->charset[6]).')';
				$img_p = '<div class="sssmb_img '.($i%2===0 ? 'sssmb_img_l' : 'sssmb_img_r').'"><img itemprop="image" src="'.$img['src'].'" alt="'.$img['attr'].'" /><div class="sssmb_bck"><div class="sssmb_ln"></div></div></div>';
			}
			$st['s_text'] = preg_replace("/\[img\]/U", $img_p, $st['s_text'], 1, $cc);
			if ( ! $cc && $img_p) {
				if ( ! $flag_dopimgs) {
					$flag_dopimgs = true;
					$st['s_text'] .= '<div class="sssmb_dopimgs">';
				}
				$st['s_text'] .= $img_p;
			}
		}
		if ($flag_dopimgs) {
			$st['s_text'] .= '</div>';
		}
		$st['s_text'] = str_replace('[img]', '', $st['s_text']);

		$i = 0;
		do {
			$i = $i == 3 ? 1 : $i+1;
			if($i == 1) {
				$colp = '<div class="sssmb_clr"></div>
				<div class="sssmb_cols">
				<div class="sssmb_col sssmb_col_l">';
			} elseif ($i == 2) {
				$colp = '</div>
				<div class="sssmb_col sssmb_col_r">';
			} else {
				$colp = '</div></div>';
			}
			$st['s_text'] = preg_replace("/\[col\]/U", $colp, $st['s_text'], 1, $cc);
		} while ($cc);

		preg_match_all("/\[tab(.*)\]/U", $st['s_text'], $tabtags);
		if (is_array($tabtags[0])) {
			$st['flag_tabs'] = true;
			$tabs = '';
			$first = true;
			foreach ($tabtags[0] AS $key => $row) {
				$st['s_text'] = str_replace('<p>'.$row.'</p>', $row, $st['s_text']);
				if ($key+1 != count($tabtags[0])) {
					$butt = trim($tabtags[1][$key]);
					$tabs .= '<div class="sssmbt_butt '.(!$key?'sssmbt_butt_a':'').'" data-tabid="'.$key.'">'.$butt.'</div>';
				}
				if ($first) {
					$first = false;
					$replace = '<div id="sssmb_tabs" class="sssmb_tabs">
						<div class="sssmbt_butts">[tabs_buttons]</div>
						<div class="sssmbt_itms">
							<div class="sssmbt_itm sssmbt_itm_'.$key.' sssmbt_itm_a">';
				} elseif ($key+1 == count($tabtags[0])) {
					$replace = '<div class="sssmb_clr"></div></div></div></div>';
				} else {
					$replace = '<div class="sssmb_clr"></div></div><div class="sssmbt_itm sssmbt_itm_'.$key.'">';
				}
				$st['s_text'] = str_replace($row, $replace, $st['s_text']);
			}
			$st['s_text'] = str_replace('[tabs_buttons]', $tabs, $st['s_text']);
		}
	}

	function articles_parse($alias_start=false, $limit=0, $with_stext_only=false)
	{
		if (
			! $this->seotext['s_text']
			&& $with_stext_only
		) return;

		$imgs = $this->module_folder.'/img/';
		$flag = $alias_start ? true : false;
		$list = $this->c[3];
		end($list);
		$last = key($list);
		reset($list);
		while ($row = each($list)) {
			$alias = $row['key'];
			$url   = $row['value'][0];
			if ($alias_start && $limit && $alias==$last) {
				reset($list);
			}
			if ($flag) {
				if ($alias == $alias_start) {
					$flag = false;
				}
				continue;
			}
			
			if ($url == $this->c[1]['articles']) continue;

			$counter++;
			$text = $this->seofile($alias);
			if ( ! $text) continue;
			if (
				! $this->c[2]['page_without_stit_to_re_linking']
				&& ! $text['s_title']
			) {
				$counter--;
				continue;
			}
			if ( ! $text['a_title']) {
				$text['a_title'] = $text['s_title'] ? $text['s_title'] : $text['title'];
			}
			if ( ! $text['a_description']) {
				$text['a_description'] = $text['description'];
			}
			for ($k=1; $k<=10; $k++) {
				$img = $imgs.$alias.'_'.$k;
				if (file_exists($this->droot.$img.'.jpg')) {
					$img .= '.jpg'; break;
				} elseif (file_exists($this->droot.$img.'.png')) {
					$img .= '.png'; break;
				}
				$img = false;
			}
			$txt .= '<div class="sssmba_itm">
				<div class="sssmba_img">';
			if ($img) $txt .= '<img itemprop="image" src="'.$img.'" alt="'.$text['a_title'].'" />';
			$txt .= '</div>
				<div class="sssmba_inf">
					<div class="sssmba_tit"><a href="'.$url.'">'.$text['a_title'].'</a></div>
					<div class="sssmba_txt">'.$text['a_description'].'</div>
				</div>
			</div>';
			$counter--;
			$limit--;

			if ($alias_start && $limit<=0) break;
		}
		if ($txt) {
			if ($alias_start) {
				$tit  = $this->charset[0];
				$link = $this->charset[1];
				$txt = '<div class="sssmb_h2 sssmb_h2_cols">
					<div class="col"><h2>'.$tit.'</h2></div>
					<div class="col rght"><a href="'.$this->c[1]['articles'].'">'.$link.'</a></div>
				</div>'.$txt;
			}
			$txt = '<div class="sssmb_clr"></div><div class="sssmb_articles">'.$txt.'</div>';
			$this->seotext['s_text'] .= $txt;
		}
		if ($counter) $this->log('[12]');
	}

	function template_parse($template)
	{
		$st = $this->seotext;

		$stext_f = $st['s_text'] ? true : false;
		$stitle_f = $st['s_title'] ? true : false;
		if ($this->c[2]['disable_stext']) {
			$stext_f = false;
			$stitle_f = false;
		}

		if ($st['flag_multitext']) {
			$st['s_text'] = explode('[part]', $st['s_text']);
			$s_text_single = '';
			foreach ($st['s_text'] AS $key => $row) {
				$row = trim($row);
				$foo = "<!--bsm_start_".($key+1)."-->(.*)";
				$foo .= "<!--bsm_finish_".($key+1)."-->";
				$template = preg_replace("/".$foo."/s", $row,
					$template, 1, $matches);
				if ( ! $matches) $s_text_single .= $row;
			}
			$st['s_text'] = $s_text_single;
		}

		if ($stext_f) {
			$style = $this->module_folder.'/'.$this->domain_h.'/style.css';
			$body = '<link rel="stylesheet" href="'.$style.'" />';

			if ($st['before_code']) {
				$body .= $st['before_code'];
			}
		}

		if ($stext_f && $this->seotext_hide == 'Y') {
			$body .= '
<script>
	function sssmb_chpoktext(){
		let obj = document.getElementById("sssmodulebox");
		obj.style.display = obj.style.display=="none" ? "" : "none";
	}
</script>
<article onclick="sssmb_chpoktext()">&rarr;</article>';
		}

		if ($st['flag_tabs']) {
			$body .= '
<script>
document.addEventListener("readystatechange",(event)=>{
	if (document.readyState != "interactive") return;
	var tabs = document.getElementById("sssmb_tabs");
	if ( ! tabs) return;
	var butts = tabs.getElementsByClassName("sssmbt_butt");
	Array.prototype.filter.call(butts, function(butt){
		butt.onclick = function(e){
			if (butt.classList.contains("sssmbt_butt_a")) return;
			let tabid = butt.dataset.tabid;
			tabs.getElementsByClassName("sssmbt_butt_a")[0].classList.remove("sssmbt_butt_a");
			this.classList.add("sssmbt_butt_a");
			tabs.getElementsByClassName("sssmbt_itm_a")[0].classList.remove("sssmbt_itm_a");
			tabs.getElementsByClassName("sssmbt_itm_"+tabid)[0].classList.add("sssmbt_itm_a");
		};
	});
});
</script>';
		}

		if ($this->test) {
			$body .= '
<script>
window.addEventListener("load",(event)=>{
	var e = document.getElementById("sssmodulebox");
	var h = e.offsetTop + e.offsetHeight + 1000;
	if (document.body.offsetHeight > h) {
		h = document.body.offsetHeight;
	}
	parent.window.postMessage({
		a : "body_height",
		from : "bsm",
		height : h
	},"*");
});
</script>';
		}

		if ('d' == $this->seotext_site) {
			$this->seotext_site = $this->seotext_tp=='A'
				? 'f' : 'sf';
		}
		if ( ! $stext_f) {
			$this->seotext_tit = 'n';
		}
		if ('d' == $this->seotext_tit) {
			if ('f' == $this->seotext_site) {
				$this->seotext_tit = 'h2h1';
			} else {
				$this->seotext_tit = 'h1';
			}
		}

		if ($stext_f) {
			$body .= '
<section id="sssmodulebox" class="sssmodulebox turbocontainer '.$this->c[2]['classname'].'" '.($this->seotext_hide=='Y'?'style="display:none;"':'').' itemscope itemtype="http://schema.org/Article">
	<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="'.$this->c[1]['website'].$this->requesturi.'" content="'.$st['title'].'" />
	<div class="sssmb_clr">&nbsp;</div>';
		}

		if ($stitle_f) {
			if ($this->c[16]['bc_1'] && $this->seotext_tp == 'S') {
				$bc_1 = base64_decode($this->c[16]['bc_1']);
				$bc_1 = str_replace('[+bsm_pagetitle+]',$st['s_title'],$bc_1);
				$bc_1 = str_replace('[+bsm_linktoarticles+]',$this->c[1]['articles'],$bc_1);
				$bc_1 = str_replace('[+bsm_pagelink+]',$this->requesturi,$bc_1);
				$breadcrumbs = true;
			}

			$h1cc = $h2cc = 1;
			if ('h1' == $this->seotext_tit) {
				$rpl = '<h1 ${1} itemprop="name">'.$st['s_title'].'</h1>';
				if ($breadcrumbs) {
					if ($this->c[2]['bcrumbs_after_h1']) {
						$rpl .= '[+bsm_breadcrumbs+]';
					} else {
						$rpl = '[+bsm_breadcrumbs+]'.$rpl;
					}
				}
				$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU",
					$rpl, $template, -1, $h1cc);

			} elseif ('h2h1' == $this->seotext_tit) {
				$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU",
					'<h2 ${1}>${2}</h2>', $template, -1, $h2cc);
			}
			if (
				! $h1cc
				|| 'h2h1' == $this->seotext_tit
				|| $this->c[2]['starttag_title']
			) {
				$tit = '<div class="sssmb_h1"><h1 itemprop="name">'.$st['s_title'].'</h1></div>';
				if (
					$breadcrumbs
					&& $this->c[2]['starttag_title']
					&& $this->c[2]['starttag_breadcrumbs']
				) {
					if ($this->c[2]['bcrumbs_after_h1']) {
						$tit .= '[+bsm_breadcrumbs+]';
					} else {
						$tit = '[+bsm_breadcrumbs+]'.$tit;
					}
				}
				$body .= $tit;
			}

			if ($h1cc >= 2 || $h2cc >= 2) {
				$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU", '', $template);
				$this->log('[50]');
			}
		}

		if ($stext_f) {
			list($logo_w, $logo_h) = getimagesize($this->droot.$this->c[1]['logo']);

			if ( ! $this->c[12]['obrabotka'] || ! $this->c[12]['o_micromarking']) {
				$body .= '
<div class="sssmb_cinf">
	<p itemprop="author">'.$this->charset[7].': '.$this->c[1]['company_name'].'</p>
	<div itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
		<meta itemprop="name" content="'.$this->domain.'" />
		<meta itemprop="telephone" content="'.$this->c[1]['phone'].'" />
		<meta itemprop="address" content="'.addslashes($this->c[1]['address']).'" />
		<div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
			<img itemprop="url image" src="'.$this->website.$this->c[1]['logo'].'" alt="" />
			<meta itemprop="width" content="'.$logo_w.'" />
			<meta itemprop="height" content="'.$logo_h.'" />
		</div>
	</div>
	<p>'.$this->charset[2].' '.$this->charset[3].': <time itemprop="datePublished">'.date('Y-m-d',strtotime($this->c[1]['date_start'])).'</time></p>
	<p>'.$this->charset[2].' '.$this->charset[4].': <time itemprop="dateModified">'.$this->seotext_date.'</time></p>
	<!--noindex--><p itemprop="headline">'.$st['s_title'].'</p><!--/noindex-->
	<!--noindex--><p itemprop="description">'.$st['description'].'</p><!--/noindex-->
</div>';
			}

			$body .= '<div class="sssmb_stext" itemprop="articleBody">';

			$body .= $st['s_text'];

			$body .= '<div class="sssmb_clr">&nbsp;</div></div>';
			if($this->c[2]['share_code'])
				$body .= '<div class="yasharebox">'.$this->c[2]['share_code'].'</div>';
			$body .= '</section>';

			if ('f' == $this->seotext_site) {
				$foo = $this->tag_f['p'] == 'a' ? $this->tag_f['t'] : '';
				$foo .= $body;
				$foo .= $this->tag_f['p'] == 'b' ? $this->tag_f['t'] : '';
				$mask = "/".$this->tag_f['m']."/s";

			} elseif ('sf' == $this->seotext_site) {
				$foo = $this->tag_s['p'] == 'a' ? $this->tag_s['t'] : '';
				$foo .= $body;
				$foo .= $this->tag_f['p'] == 'b' ? $this->tag_f['t'] : '';
				$mask = "/".$this->tag_s['m']."(.*)".$this->tag_f['m']."/sU";

			} elseif ('s' == $this->seotext_site) {
				$foo = $this->tag_s['p'] == 'a' ? $this->tag_s['t'] : '';
				$foo .= $body;
				$foo .= $this->tag_s['p'] == 'b' ? $this->tag_s['t'] : '';
				$mask = "/".$this->tag_s['m']."/s";
			}
			if ($mask) {
				$template = preg_replace($mask, $foo, $template, 1);
			}
		}

		if ($breadcrumbs) {
			$template = str_replace('[+bsm_breadcrumbs+]', $bc_1, $template);
		}

		if ($this->c[2]['city_replace']) {
			$template = preg_replace("/\[hide\](.*?)\[hide\]/U", '', $template);
			foreach ($this->declension AS $key => $decl) {
				$template = preg_replace("/\[city_{$key}\](.*?)\[city\]/U", $decl, $template);
			}
		}

		if (true) {
			$uid = uniqid();
			$_SESSION['buranseomodule']['watch'][$uid] = time();
			$script = '
<script>
document.addEventListener("readystatechange",(event)=>{
	if (document.readyState != "interactive") return;
	if ( ! window.XMLHttpRequest) return;
	setTimeout(function(){
		var m = new XMLHttpRequest;
		let url = "'.dirname($this->module_folder).'/'.$this->module_file.'?a=watch";
		url += "&b='.$this->seotext_alias.'";
		url += "&s="+(document.getElementById("sssmodulebox") ? "y" : "n");
		url += "&u='.$uid.'";
		try {
			m.open("GET",url,1);
		} catch (f) {
			return;
		}
		m.send();
	},2000);
});
</script>
';
			$template = preg_replace("/<\/body>/s", $script.'</body>', $template, 1);
		}

		return $template;
	}

	function meta_parse($template)
	{
		$st = $this->seotext;
		$c  = $this->c[2];

		$regmask = $this->regmask;

		$meta = '';

		if (
			$st &&
			in_array($c['meta'], array(
				'replace_or_add',
				'delete'
			))
		) {
			$titl = '<title>'.$st['title'].'</title>';
			$desc = '<meta name="description" content="'.$st['description'].'" />';
			$keyw = '<meta name="keywords" content="'.$st['keywords'].'" />';
			if ($c['meta'] == 'replace_or_add') {
				$meta .= "\n\t".$titl;
				$meta .= "\n\t".$desc;
				$meta .= "\n\t".$keyw;
			}
			$template = preg_replace($regmask['description'], '', $template, 2, $count1);
			$template = preg_replace($regmask['keywords'], '', $template, 2, $count2);
			$template = preg_replace($regmask['title'], '${3}', $template, 2, $count3);
			if ($count1 === 2 || $count2 === 2 || $count3 === 2) {
				$this->log('[61]');
			}

		} elseif (in_array($c['meta_neseo'], array(
			'add_if_not_exists',
			'replace_or_add',
			'delete'
		))) {
			preg_match_all($regmask['h1'], $template, $matches);
			$h1tit = trim(html_entity_decode($matches[2][0]));
			if ($h1tit) {
				$h1tit = $h1tit.', '.$c['company_name'].', '.$c['city'];

				$titl = '<title>'.$h1tit.'</title>';
				$desc = '<meta name="description" content="'.$h1tit.'" />';
				$keyw = '<meta name="keywords" content="'.$h1tit.'" />';

				if ($c['meta_neseo'] == 'add_if_not_exists') {
					preg_match_all($regmask['title'], $template, $matches);
					if ( ! $matches[1][0]) $meta .= "\n\t".$titl;

					preg_match_all($regmask['description'], $template, $matches);
					if ( ! $matches[3][0]) $meta .= "\n\t".$desc;

					preg_match_all($regmask['keywords'], $template, $matches);
					if ( ! $matches[3][0]) $meta .= "\n\t".$keyw;

				} else {
					if ($c['meta'] == 'replace_or_add') {
						$meta .= "\n\t".$titl;
						$meta .= "\n\t".$desc;
						$meta .= "\n\t".$keyw;
					}
					$template = preg_replace($regmask['description'], '', $template, 2, $count1);
					$template = preg_replace($regmask['keywords'], '', $template, 2, $count2);
					$template = preg_replace($regmask['title'], '${3}', $template, 2, $count3);
					if ($count1 === 2 || $count2 === 2 || $count3 === 2) {
						$this->log('[61]');
					}
				}
			}
		}

		$canonical = $this->c[1]['website'];
		if ( ! $this->c[12]['obrabotka'] || ! $this->c[12]['o_canonical']) {
			$canonical .= $this->requesturi;
		}
		$canonical = '<link rel="canonical" href="'.$canonical.'" />';
		if (
			$st &&
			in_array($c['canonical'], array(
				'replace_or_add',
				'delete'
			))
		) {
			if ($c['meta'] == 'replace_or_add') {
				$meta .= "\n\t".$canonical;
			}
			$template = preg_replace($regmask['canonical'], '', $template);

		} elseif (in_array($c['canonical_neseo'], array(
			'add_if_not_exists',
			'replace_or_add',
			'delete'
		))) {
			if ($c['canonical_neseo'] == 'add_if_not_exists') {
				preg_match_all($regmask['canonical'], $template, $matches);
				if ( ! $matches[4][0]) $meta .= "\n\t".$canonical;

			} else {
				if ($c['meta'] == 'replace_or_add') {
					$meta .= "\n\t".$canonical;
				}
				$template = preg_replace($regmask['canonical'], '', $template);
			}
		}

		if (in_array($c['base'], array(
			'replace_or_add',
			'delete'
		))) {
			$base = '<base href="'.$this->c[1]['website'].'/" />';
			if ($c['meta'] == 'replace_or_add') {
				$meta .= "\n\t".$base;
			}
			$template = preg_replace($regmask['base'], '', $template);
		}

		if ($meta) {
			$meta .= "\n";
			$template = preg_replace("/<head>/isU", '<head>'.$meta, $template, 1, $count);
			if ($count) {
				return $template;
			} else {
				$this->log('[61]');
			}
		}
		return false;
	}

	function save_url_info($template=false)
	{
		if ($this->module_mode) return;

		$this->bsm_sqlite();
		if ( ! $this->db_op) return false;

		$requesturi = $this->db->escapeString($this->requesturi);
		$loadtime = round((microtime(true)-$this->mct_start),3);

		if ($template) {
			$size = strlen($template);

			preg_match_all($this->regmask['h1'], $template, $matches);
			$h1tit = trim(html_entity_decode($matches[2][0]));

			preg_match_all($this->regmask['title'], $template, $matches);
			$m_titl = trim(html_entity_decode($matches[2][0]));

			preg_match_all($this->regmask['description'], $template, $matches);
			if ($matches[0][0]) {
				preg_match_all($this->regmask['description_v'], $matches[0][0], $matches);
				$m_desc = trim(html_entity_decode($matches[2][0]));
			}

			preg_match_all($this->regmask['keywords'], $template, $matches);
			if ($matches[0][0]) {
				preg_match_all($this->regmask['keywords_v'], $matches[0][0], $matches);
				$m_keyw = trim(html_entity_decode($matches[2][0]));
			}

			preg_match_all($this->regmask['canonical'], $template, $matches);
			if ($matches[0][0]) {
				preg_match_all($this->regmask['canonical_v'], $matches[0][0], $matches);
				$m_cancl = trim(html_entity_decode($matches[2][0]));
			}

			$h1tit = $this->db->escapeString($h1tit);
			$m_titl = $this->db->escapeString($m_titl);
			$m_desc = $this->db->escapeString($m_desc);
			$m_keyw = $this->db->escapeString($m_keyw);
			$m_cancl = $this->db->escapeString($m_cancl);
		}

		$res = $this->db->querySingle("SELECT id FROM url
			WHERE url='{$requesturi}' LIMIT 1");
		if ($res) {
			$this->db->query("UPDATE url SET
					resp_code = '".$this->http_code."',
					h1 = '{$h1tit}',
					m_titl = '{$m_titl}',
					m_desc = '{$m_desc}',
					m_keyw = '{$m_keyw}',
					m_cancl = '{$m_cancl}',
					size = '{$size}',
					loadtime = '{$loadtime}',
					freq = ((freq+(".time()."-lastload))/2),
					lastload = '".time()."'
				WHERE id = '{$res}'");
		} elseif ($res !== false) {
			$this->db->query("INSERT INTO url
				(url, resp_code, h1, m_titl, m_desc, m_keyw, m_cancl, size, loadtime, freq, lastload)
				VALUES (
					'{$requesturi}',
					'".$this->http_code."',
					'{$h1tit}',
					'{$m_titl}',
					'{$m_desc}',
					'{$m_keyw}',
					'{$m_cancl}',
					'{$size}',
					'{$loadtime}',
					'".(60*60*24)."',
					'".time()."'
				)");
		}

		$this->bsm_sqlite(true);
		return true;
	}

// ------------------------------------------------------------------

	function bsmfile($type, $act='get', $prm='', $body=false)
	{
		$subfolder = '';
		$hashfolder = false;
		$base64_e = false;
		$base64_d = false;
		$filepath = $act == 'file' ? true : false;
		$dirpath = $act == 'dir' ? true : false;
		$set = $act == 'set' ? true : false;
		$get = in_array($act, array('set', 'file', 'dir'))
			? false : true;

		if ($get) $body = '';

		$folder = $this->droot.$this->module_folder;

		switch ($type) {
			case 'module':
				if ($set) {
					$body = base64_decode($body);
					if (strpos($body, "<?php\n/**\n * seoModule") !== 0) {
						return false;
					}
				}
				$subfolder = '/../';
				$file = $this->module_file;
				break;

			case 'config':
				$hashfolder = true;
				$file = $type.'.txt';
				break;
			case 'config_value':
				$serialize = true;
				$base64_e = true;
				$hashfolder = true;
				$file = 'config.txt';
				break;

			case 'text':
				$subfolder = '/txt/';
				$file = 'txt_'.$prm.'.txt';
				break;
			case 'text_value':
				$serialize = true;
				$base64_e = true;
				$subfolder = '/txt/';
				$file = 'txt_'.$prm.'.txt';
				break;

			case 'txt_cache':
				$serialize = true;
				$base64_e = true;
				$hashfolder = true;
				$subfolder = '/txt_cache/';
				$file = 'txt_'.$prm.'.txt';
				break;

			case 'txt_info':
				$serialize = true;
				$base64_e = true;
				$hashfolder = true;
				$subfolder = '/txt_info/';
				$file = 'txt_'.$prm.'.txt';
				break;

			case 'style':
				$base64_d = $set ? true : false;
				$hashfolder = true;
				$file = $type.'.css';
				break;

			case 'transit':
				$hashfolder = true;
				$file = $type.'.txt';
				break;
			case 'transit_list':
				$serialize = true;
				$base64_e = true;
				$hashfolder = true;
				$file = 'transit.txt';
				break;

			case 'imgs':
				$subfolder = '/img/';
				break;

			case 'reverse':
				$serialize = true;
				$base64_e = true;
				$hashfolder = true;
				$file = $type.'.txt';
				break;

			case 'errors':
				$hashfolder = true;
				$file = $type.'.txt';
				break;

			case 'phperrors':
				$hashfolder = true;
				$file = $type.'.txt';
				break;

			case 'test':
				$file = $type.$prm.'.txt';
				break;

			default:
				return false;
		}

		if ($hashfolder) $folder .= '/'.$this->domain_h;
		if ($subfolder) $folder .= $subfolder; else $folder .= '/';

		if (($dirpath || $filepath) && ! file_exists($folder)) {
			mkdir($folder, 0755, true);
		}
		if ($dirpath) return $folder;
		if ($filepath) return $folder.$file;

		if ('module' == $type) {
			$back = $folder.$file.'_'.date('YmdHis');
			$res = copy($folder.$file, $back.'_'.substr(md5($back),0,4));
			if ( ! $res) return false;
		}

		if ('imgs' == $type) {
			if ( ! file_exists($folder)) {
				mkdir($folder, 0755, true);
			}
			$cc    = intval($body['filescount']);
			$files = $body['files'];
			if ( ! $cc) return true;
			for ($k=1; $k<=$cc; $k++) {
				$file = $files['f'.$k];
				$ext  = substr($file['name'], strpos($file['name'],'.'));
				if ($ext != '.jpg' && $ext != '.png') continue;
				$res = move_uploaded_file($file['tmp_name'], $folder.$file['name']);
				if ( ! $res) return false;
			}
			return true;
		}

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

			$fh = fopen($folder.$file, 'wb');
			if ( ! $fh) return false;
			$res = fwrite($fh, $body);
			if ($res === false) return false;
			fclose($fh);

			return true;
		}

		if ($get) {
			if ( ! file_exists($folder.$file)) return false;
			$fh = fopen($folder.$file, 'rb');
			if ( ! $fh) return false;
			while ( ! feof($fh)) $body .= fread($fh, 1024*8);
			fclose($fh);

			if ($base64_e) $body = base64_decode($body);
			if ($serialize) $body = unserialize($body);

			return $body;
		}
	}

	function cache_save($alias)
	{
		$this->seotext['cache'] = time();
		$this->bsmfile('txt_cache', 'set', $alias, $this->seotext);
	}

	function cache_clear()
	{
		$folder = $this->bsmfile('txt_cache', 'dir');
		if ( ! file_exists($folder)) return;
		if ( ! ($open = opendir($folder))) return;
		while ($file = readdir($open)) {
			if ( ! is_file($folder.$file)) continue;
			unlink($folder.$file);
		}
	}

	function send_reverse_request($urgently=false)
	{
		$interval = 60*60*6;
		if ($urgently) $interval = 60*5;

		$data = $this->bsmfile('reverse', 'get');

		if ( ! is_array($data)) {
			$data = array(
				'module' => 0,
				'info'   => 0,
				'config' => 0,
				'style'  => 0,
				'head'   => 0,
				'body'   => 0,
			);
		}

		if (time() - $data['module'] >= $interval) {
			$update = true;
			$action = 'get_code';
			$type   = 'module';
			$time   = $type;

		} elseif (time() - $data['info'] >= $interval) {
			$update = false;
			$action = 'set';
			$type   = 'info';
			$time   = $type;
			$prms   = array(
				'info' => $this->info_parse()
			);

		} elseif (time() - $data['config'] >= $interval) {
			$update = true;
			$action = 'get_code';
			$type   = 'config';
			$time   = $type;

		} elseif (time() - $data['style'] >= $interval) {
			$update = true;
			$action = 'get_code';
			$type   = 'style';
			$time   = $type;

		} elseif (time() - $data['head'] >= $interval) {
			$update = true;
			$action = 'get_code';
			$type   = 'head';
			$time   = $type;

		} elseif (time() - $data['body'] >= $interval) {
			$update = true;
			$action = 'get_code';
			$type   = 'body';
			$time   = $type;

		} else {
			if (is_array($this->c[3])) {
				foreach ($this->c[3] AS $rowalias => $row) {
					if (time() - $data['text_'.$rowalias] >= $interval) {
						$update = true;
						$action = 'get_code';
						$type   = 'text';
						$alias  = $rowalias;
						$time   = $type.'_'.$alias;
						$prms   = array('alias'=>$alias);
						break;
					}
				}
			}
		}

		if ( ! $action) return true;

		$code = $this->reverse_request($action, $type, false, $prms);

		if ($update && $code) {
			$update_res = $this->bsmfile($type, 'set', $alias, $code);
		}

		$data[$time] = time();

		$this->bsmfile('reverse', 'set', '', $data);

		if ( ! $update) return true;

		if ( ! $code || ! $update_res) return false;

		$this->cache_clear();
		$this->htaccess();

		$this->reverse_request($action, $type, 'ok', $prms);
		return true;
	}

	function reverse_request($a, $b=false, $c=false, $data=false)
	{
		if ( ! $this->curl_ext) return false;
		if (is_array($data)) {
			$data = serialize($data);
		}
		$data = base64_encode($data);
		$a = urlencode($a);
		$ac = $this->accesscode;
		$reqres = $this->request(
			'http://bunker-yug.ru/__buran/seoModule_service.php',
			false, false,
			array(
				'idc'  => $this->c[1]['bunker_id'],
				'ws'   => $this->domain,
				'ac'   => $ac,
				'a'    => $a, 'b' => $b, 'c' => $c,
				'data' => $data,
			)
		);
		if (
			$reqres['res']
			&& $reqres['response']
			&& $reqres['info']['http_code'] == 200
		) {
			return $reqres['response'];
		}
		return false;
	}

	function transit_list_check()
	{
		if ( ! $this->curl_ext) return false;

		if (time()-$_SESSION['buranseomodule']['transit'] < 10) {
			return false;
		}
		$_SESSION['buranseomodule']['transit'] = time();

		$list = $this->bsmfile('transit_list', 'get');
		if ( ! $list || ! is_array($list)) return false;

		$page = false;
		foreach ($list AS $key => $row) {
			if (
				$row['dt'] || ! $row['id'] || ! $row['id']
				|| ! $row['ws'] || ! $row['url']
			) continue;
			$page_id = $key;
			$page    = $row;
			break;
		}
		if ( ! $page) return false;

		$reqres = $this->request(
			$page['ws'].$page['url'],
			array(
				'nobody' => true,
				'follow' => false,
			)
		);
		$list[$page_id]['error']    = $reqres['errno'];
		$list[$page_id]['httpcode'] = $reqres['info']['http_code'];
		$list[$page_id]['delay']    = $reqres['info']['total_time'];
		$list[$page_id]['dt']       = time();

		$res = $this->bsmfile('transit_list', 'set', '', $list);
		return $res;
	}

	function request($url, $prms=false, $headers=false, $post=false)
	{
		if ( ! $this->curl_ext) return false;
		$options = array(
			CURLOPT_URL            => $url,
			CURLOPT_HEADERFUNCTION => array(&$this,'request_headers'),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FRESH_CONNECT  => true,
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERAGENT      => 'Buran/',
		);
		$follow = true;
		if ($prms) {
			foreach ($prms AS $prm => $val) {
				if ($prm == 'nobody') $options[CURLOPT_NOBODY] = $val;

				if ($prm == 'follow') {
					$options[CURLOPT_FOLLOWLOCATION] = $val;
					$follow = $val;
				}
			}
		}
		if ($headers) {
			$options[CURLOPT_HTTPHEADER] = $headers;
		}
		if ($post) {
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = $post;
		}
		if ($this->c[2]['proxy']) {
			$options[CURLOPT_PROXY] = $this->c[2]['proxy'];
		}
		$this->request_headers(false, false, true);
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = $follow && $this->c[2]['curl_auto_redirect']
			? $this->curl_exec_followlocation($curl, $url)
			: curl_exec($curl);
		$request_info    = curl_getinfo($curl);
		$request_headers = $this->curl_request_headers;
		$errno = curl_errno($curl);
		$error = curl_error($curl);
		curl_close($curl);
		$res_a = array(
			'res'      => $errno ? false : true,
			'errno'    => $errno ? $errno : 0,
			'error'    => $errno ? $error : false,
			'response' => $response,
			'info'     => $request_info,
			'headers'  => $request_headers,
		);
		return $res_a;
	}

	function request_headers($curl, $header_line, $clear=false)
	{
		if ($clear) {
			$this->curl_request_headers = array();
			return;
		}
		$this->curl_request_headers[] = $header_line;
		return strlen($header_line);
	}

	function alist()
	{
		$green = '#16782b';
		$red   = '#b52020';

		$p = '<style>
			.row {display: flex; margin-bottom: 3px;}
			.col1 {flex: 0 0 150px; text-align: right; margin-right: 12px;}
			.col2 {flex: 0 0 auto; margin-right: 5px;}
			.green {color: #16782b;}
			.red {color: #b52020;}
		</style>';

		$p .= '<div class="row"><span class="col1">Module</span><span class="col2">'.$this->module_hash.'</span></div>';

		$p .= '<div class="row"><span class="col1">Domain Hash</span><span class="col2">'.$this->domain_h.'</span></div>';

		$flag = $this->website === $this->c[1]['website'] ? true : false;
		$p .= '<div class="row"><span class="col1">Domain</span><span class="col2 '.($flag?'green':'red').'">'.$this->website.' == '.$this->c[1]['website'].'</span></div>';

		$uname = php_uname();
		$flag = stripos($uname, 'window') !== false ? false : true;
		$p .= '<div class="row"><span class="col1">OS</span><span class="col2 '.($flag?'green':'red').'">'.$uname.'</span></div>';

		$flag = version_compare(PHP_VERSION, '5.4.0', '<') ? false : true;
		$p .= '<div class="row"><span class="col1">PHP Version</span><span class="col2 '.($flag?'green':'red').'">'.PHP_VERSION.'</span></div>';

		$flag = stripos($this->sapi_name, 'apache') !== false ? true : false;
		$p .= '<div class="row"><span class="col1">Type of interface</span><span class="col2 '.($flag?'green':'red').'">'.$this->sapi_name.'</span></div>';

		$flag = extension_loaded('openssl') ? true : false;
		$p .= '<div class="row"><span class="col1">OpenSSL</span><span class="col2 '.($flag?'green':'red').'">'.OPENSSL_VERSION_TEXT.' ['.OPENSSL_VERSION_NUMBER.']</span></div>';

		if ( ! $this->curl_ext) {
			$p .= '<div class="row"><span class="col1">cURL</span><span class="col2 red">&mdash;</span></div>';
		}

		if ( ! function_exists('ini_get')) {
			$p .= '<div class="row"><span class="col1">ini_get()</span><span class="col2 red">&mdash;</span></div>';
		}

		$disable_functions = @ini_get('disable_functions');
		if ($disable_functions) {
			$disable_functions = ','.str_replace(' ','',$disable_functions).',';
			$funcs = array('rename', 'filetype', 'readdir', 'opendir',
				'file_exists', 'base64_encode', 'ucwords',
				'preg_match', 'curl_exec', 'curl_init', 'time',
				'file_get_contents', 'stream_socket_client',
				'fopen', 'fwrite', 'feof', 'fread',
				'session_start', 'session_id', 'session_name',
				'session_write_close',
				'file_exists', 'filectime', 'date', 'rewind',
				'ftruncate', 'fgets', 'fseek', 'filesize', 'md5_file',
				'is_array', 'ini_get', 'function_exists',
				'extension_loaded', 'version_compare', 'php_uname',
				'urlencode', 'serialize', 'unlink', 'unserialize',
				'mkdir', 'move_uploaded_file',
				'md5', 'preg_replace', 'in_array', 'strtotime',
				'addslashes', 'getimagesize', 'each', 'reset',
				'end', 'key', 'preg_match_all', 'array_shift',
				'preg_quote', 'getallheaders', 'header_remove',
				'strcmp', 'header', 'headers_list', 'http_response_code',
				'zlib_encode', 'zlib_decode', 'gzinflate', 'gzencode',
				'ob_start', 'php_sapi_name', 'parse_url', 'dirname',
				'error_reporting', 'ini_set', 'is_writable', 'is_readable',
				'fileowner', 'filegroup', 'posix_getpwuid', 'posix_getgrgid',
				'fileperms', 'set_error_handler','headers_sent','copy',);
			foreach ($funcs AS $func) {
				$flag1 = function_exists($func) ? true : false;
				$flag2 = stripos($disable_functions,','.$func.',') !== false
					? true : false;
				if ( ! $flag1 || $flag2) {
					$p .= '<div class="row">
						<span class="col1">'.$func.'()</span>
						<span class="col2 '.($flag1?'green':'red').'">'.($flag1?'+':'&mdash;').'</span>
						<span class="col2 '.(!$flag2?'green':'red').'">'.(!$flag2?'+':'&mdash;').'</span>
					</div>';
				}
			}
		} else {
			$flag = function_exists('ini_get') ? true : false;
			$p .= '<div class="row"><span class="col1">ini disable_functions</span><span class="col2 red">&mdash;</span></div>';
		}

		if ( ! is_writable($this->droot.'/_buran/') || ! is_readable($this->droot.'/_buran/')) {
			$p .= '<div class="row"><span class="col1 red">&mdash;</span><span class="col2">/_buran/</span></div>';
		}
		$queue[] = $this->module_folder.'/';
		do {
			$nextfolder = array_shift($queue);
			if (
				! is_writable($this->droot.$nextfolder) ||
				! is_readable($this->droot.$nextfolder)
			) {
				$p .= '<div class="row"><span class="col1 red">&mdash;</span><span class="col2">'.$nextfolder.'</span></div>';
			}
			if ( ! ($open = opendir($this->droot.$nextfolder))) continue;
			while ($file = readdir($open)) {
				if (filetype($this->droot.$nextfolder.$file) == 'link'
					|| $file == '.' || $file == '..') continue;
				if (is_dir($this->droot.$nextfolder.$file)) {
					$queue[] = $nextfolder.$file.'/';
					continue;
				}
				if ( ! is_file($this->droot.$nextfolder.$file)) continue;
				if (
					! is_writable($this->droot.$nextfolder.$file) ||
					! is_readable($this->droot.$nextfolder.$file)
				) {
					$nm1 = posix_getpwuid(fileowner($this->droot.$nextfolder.$file));
					$nm2 = posix_getgrgid(filegroup($this->droot.$nextfolder.$file));
					$prm = decoct(fileperms($this->droot.$nextfolder.$file) & 0777);
					$p .= '<div class="row"><span class="col1 red">&mdash;</span><span class="col2">'.$nm1['name'].' | </span><span class="col2">'.$nm2['name'].' | </span><span class="col2">'.$prm.' | </span><span class="col2">'.$nextfolder.$file.'</span></div>';
				}
			}
		} while ($queue[0]);

		$p .= '<br><br><br>';
		return $p;
	}

	function info_parse()
	{
		$files = $this->c[2]['include_files'];
		$files = explode('  ', $files);
		if (is_array($files)) {
			foreach ($files AS $file) {
				$file = trim($file);
				if ( ! $file) continue;
				if ( ! file_exists($this->droot.$file)) {
					$this->log('[05]');
					continue;
				}
				$fh = fopen($this->droot.$file, 'rb');
				if ( ! $fh) {
					$this->log('[05.2]');
					continue;
				}
				$incfiles_hash .= md5_file($this->droot.$file).' : '.$file."\n";
				$code = '';
				while ( ! feof($fh)) $code .= fread($fh, 1024*8);
				fclose($fh);
				if ( ! strpos($code, substr($this->module_folder,1))) {
					$this->log('[06]');
				}
			}
		}

		$hash_1_f = $this->droot.dirname($this->module_folder).'/'.$this->module_file;
		if (file_exists($hash_1_f)) $hash_1 = md5_file($hash_1_f);

		$hash_2_f = $this->droot.'/.htaccess';
		if (file_exists($hash_2_f)) $hash_2 = md5_file($hash_2_f);

		$hash_3_f = $this->bsmfile('config', 'file');
		if (file_exists($hash_3_f)) $hash_3 = md5_file($hash_3_f);

		$res = '[seomoduleversion_'.$this->version.']
[datetime_'.date('d.m.Y, H:i:s').']

[website_'.$this->c[1]['website'].']
[modulehash_'.$hash_1.']
[confighash_'.$hash_3.']
[htaccess_'.$hash_2.']
[droot_'.$this->droot.']'."\n";
		$res .= "\n";
		$res .= '[incfiles_]'."\n";
		$res .= $incfiles_hash;
		$res .= '[_incfiles]'."\n";
		$res .= "\n";
		$res .= '[pages_]'."\n";
		if (is_array($this->c[3])) {
			foreach ($this->c[3] AS $alias => $row) {
				if ($row[0] == $this->c[1]['articles']) continue;
				$text = $this->seofile($alias);
				$info = $this->bsmfile('txt_info', 'get', $alias);
				$hash = '';
				if ($text) $hash = md5_file($text['file']);
				$res .= $hash.' : '.$alias.' : '.$info['type'].' : '.$info['last'].' : '.$info['interval'].' : '.$info['seotext_exists'].' : '.$info['seotext_js']."\n";
			}
		}
		$res .= '[_pages]'."\n";
		$res .= "\n";
		$res .= '[errors_]'."\n";
		$file = $this->bsmfile('errors', 'file');
		if (file_exists($file)) {
			$fh = fopen($file,'rb');
			if ($fh) {
				$content = '';
				while ( ! feof($fh)) $content .= fread($fh, 1024*8);
				fclose($fh);
				$res .= $content;
			}
		}
		$res .= '[_errors]'."\n";
		$res .= "\n";
		$res .= "[errinfo_]\n[01] Основная ошибка запуска модуля\n[02] Нет файла контрольной суммы\n[03] Нет файла конфигурации или неверного формата\n[04] Не удалось включить буферизацию вывода\n[05] Файл с подключением модуля не прочитан\n[06] Модуль не подключен в файл\n[07] Команда деактивации модуля\n[08] Модуль отключен по этому пути\n[10] Файл с текстом не прочитан или неверного формата\n[12] В блоке статей не хватает записей\n[13] Несколько статей на одной странице\n[14] Страница стала S\n[15] Страница стала A\n[20] Другой код ответа (200, 404)\n[21] Пустой шаблон\n[22] Заголовки уже отправлены\n[31] Файл шаблона не записан\n[40] Тег не найден\n[41] S-страница без текста\n[50] Много H1\n[51] Meta Robots\n[61] Проблема с Meta\n[62] Проблема с Base\n[64] Проблема с Canonical\n[70] Head не добавлен\n[71] Body не добавлен\n[72] Head не прочитан\n[73] Body не прочитан\n[_errinfo]\n";

		if (isset($_GET['phperrors'])) {
			$data = $this->bsmfile('phperrors');
			$res .= "\n";
			$res .= '[phperrors_]'."\n";
			$res .= $data;
			$res .= '[_phperrors]'."\n";
		}
		return $res;
	}

	function log($text, $description=false, $type='errors', $clear=false)
	{
		if ($this->c[2]['ignore_errors'] && $type == 'errors' && ! $clear) {
			$foo = str_replace(array('[',']'), '', $text);
			if (stripos(','.$this->c[2]['ignore_errors'].',', ','.$foo.',') !== false) {
				return;
			}
		}
		$fh = $this->logs_files[$type];
		if ( ! $fh) {
			$file = $this->bsmfile('errors', 'file');
			if ($clear) {
				$fh = fopen($file, 'wb');
				if ($fh) {
					$data = time() ."\t";
					$data .= date('Y-m-d-H-i-s') ."\t";
					$data .= '(truncate)' ."\n";
					fwrite($fh, $data."\n");
					fclose($fh);
				}
				return true;
			}
			if (file_exists($file) && filesize($file) >= 1024*64) {
				$fh = fopen($file, 'c+b');
				if ($fh) {
					fseek($fh,-1024*8,SEEK_END);
					$data = '';
					while ($line = fgets($fh)) $data .= $line;
					$data .= time() ."\t";
					$data .= date('Y-m-d-H-i-s') ."\t";
					$data .= '(truncated)' ."\n";
					ftruncate($fh, 0);
					rewind($fh);
					fwrite($fh, $data."\n");
					fclose($fh);
				}
			}
			$fh = fopen($file, 'ab');
			if ( ! $fh) return false;
			$this->logs_files[$type] = $fh;
		}
		if ($text) {
			$data = time() ."\t";
			$data .= date('Y-m-d-H-i-s') ."\t";
			$data .= $text ."\t";
			$data .= $this->requesturi;
			if ($description) {
				$data .= "\t". $description;
			}
			fwrite($fh, $data."\n");
		}
	}

	function error_handler($errno, $errstr, $errfile, $errline)
	{
		$file = $this->bsmfile('phperrors','file');
		$fh = fopen($file, 'ab');
		if ( ! $fh) return false;
		$line = date('d.m.Y, H:i:s').' [errno '.$errno.'] '.$errstr.' in '.$errfile.' [line '.$errline.']';
		fwrite($fh, $line."\n\n");
		return false;
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

	function htaccess()
	{
		$htaccess = '
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteBase /
	RewriteCond %{REQUEST_URI} \.txt$
	RewriteRule . - [R=404,L,NC]
	RewriteRule \.txt$ /index.php [L,QSA]
</IfModule>
';
		$fh = fopen($this->droot.$this->module_folder.'/.htaccess','wb');
		if ( ! $fh) return;
		fwrite($fh, $htaccess);
		fclose($fh);
	}

	function auth($get_w)
	{
		session_name('sssm');
		session_start();
		if (time() - $_SESSION['buranseomodule']['auth'][$get_w] < 60*30) {
			return true;
		}
		$http  = 'http://';
		$host  = 'bunker-yug.ru';
		$url   = '/__buran/secret_key.php';
		$url  .= '?h='.$this->domain;
		$url  .= '&w='.$get_w;

		if ($this->curl_ext) {
			$options = array(
				CURLOPT_URL => $http.$host.$url,
				CURLOPT_RETURNTRANSFER => true,
			);
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			$ww = curl_exec($curl);
			$curl_errno = curl_errno($curl);
			curl_close($curl);
		}
		if ($curl_errno && $this->sock_ext) {
			$headers = "GET ".$url." HTTP/1.0\nHost: {$host}\n\n";
			$res = stream_socket_client($host.':80', $errno, $errstr, 10);
			if ($res) {
				fwrite($res, $headers);
				while ( ! feof($res)) {
					$ww .= fread($res, 1024*1024); 
				}
				fclose($res);
				$ww = $this->parse_response_headers($ww);
				$ww = $ww[1];
			}
		}
		if ( ! $ww && $this->fgc_ext) {
			$ww = file_get_contents($http.$host.$url);
		}
		if ($ww && $get_w && $ww === $get_w) {
			$_SESSION['buranseomodule']['auth'][$get_w] = time();
			return true;
		}
		unset($_SESSION['buranseomodule']);
		return false;
	}

	function parse_response_headers($data)
	{
		$data = str_replace("\r", '', $data);
		$data = explode("\n\n", $data, 2);
		return $data;
	}

	function curl_exec_followlocation(&$curl, &$url)
	{
		/**
		 * curl_exec_followlocation()
		 * @version 2.3
		 * @date 18.01.2019
		 */
		if (preg_match("/^(http(s){0,1}:\/\/[a-z0-9\.-]+)(.*)$/i",
			$url, $matches) !==1) {
			return;
		}
		$website = $matches[1];
		do {
			$this->request_headers(false, false, true);
			// if($referer) curl_setopt($curl, CURLOPT_REFERER, $referer);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this,'request_headers'));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			$headers  = implode("\n", $this->curl_request_headers);
			if (curl_errno($curl)) return false;
			if (preg_match("/^location: (.*)$/im", $headers, $matches) === 1) {
				$location = true;
				$referer  = $url;
				$url      = trim($matches[1]);
				if (preg_match("/^http(s){0,1}:\/\/[a-z0-9\.-]+/i",
					$url, $matches) !== 1) {
					$url = $website.(substr($url,0,1)!='/'?'/':'').$url;
				}
			} else {
				$location = false;
			}
			if ($location) {
				if ($redirects_list[$url] <= 1) $redirects_list[$url]++;
				else $location = false;
			}
		} while ($location);
		return $response;
	}

	//https://github.com/ralouphie/getallheaders
	function getallheaders_bsm()
	{
		$headers = array();
		$copy_server = array(
			'CONTENT_TYPE'   => 'Content-Type',
			'CONTENT_LENGTH' => 'Content-Length',
			'CONTENT_MD5'    => 'Content-Md5',
		);
		foreach ($_SERVER as $key => $value) {
			if (substr($key, 0, 5) === 'HTTP_') {
				$key = substr($key, 5);
				if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
					$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
					$headers[$key] = $value;
				}
			} elseif (isset($copy_server[$key])) {
				$headers[$copy_server[$key]] = $value;
			}
		}
		if (!isset($headers['Authorization'])) {
			if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
				$headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
			} elseif (isset($_SERVER['PHP_AUTH_USER'])) {
				$basic_pass = isset($_SERVER['PHP_AUTH_PW'])
					? $_SERVER['PHP_AUTH_PW'] : '';
				$headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
			} elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
				$headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
			}
		}
		return $headers;
	}

	function bsm_sqlite($close=false)
	{
		$dbfile = $this->droot.$this->module_folder.'/'.$this->domain_h.'/data.db';
		$this->db_file = $dbfile;
		$dbfile_ext = file_exists($dbfile);

		if (
			$close
			&& $this->db_op
			&& $this->db
			&& ($this->db instanceof SQLite3)
		) {
			$this->db->close();
			$this->db_op = false;
			return true;
		}

		if ($dbfile_ext) {

			$this->db = new SQLite3($dbfile);
			$res = $this->db->querySingle("SELECT id FROM bots LIMIT 1");
			$this->db_ok = $res !== false && ! $this->db->lastErrorCode() ? true : false;
			$this->db_op = $this->db_ok;

			$dbfiletm = $this->filetime($dbfile);
			if ( ! $this->db_op && time() - $dbfiletm > 60) {
				/*$fh = fopen($this->db_file.'.log', 'ab');
				if ($fh) {
					$log = $this->mct_start.' | '.$this->requesturi.' | id='.$res.' | er='.$this->db->lastErrorCode().' | ert='.$this->db->lastErrorMsg()."\n";
					$res = fwrite($fh, $log);
					fclose($fh);
				}*/
				unlink($dbfile);
			}
		}

		if ( ! file_exists($dbfile)) {
			$this->db = new SQLite3($dbfile);
			$this->db_ok = ! $this->db->lastErrorCode() ? true : false;
			$this->db_op = $this->db_ok;

			if ($this->db_op) {
				$this->db->exec("BEGIN TRANSACTION");
				$this->db->exec("CREATE TABLE 'url' (
					'id' INTEGER PRIMARY KEY AUTOINCREMENT,
					'url' TEXT,
					'resp_code' INTEGER,
					'h1' TEXT,
					'm_titl' TEXT,
					'm_desc' TEXT,
					'm_keyw' TEXT,
					'm_cancl' TEXT,
					'size' INTEGER,
					'loadtime' REAL,
					'freq' INTEGER,
					'lastload' INTEGER
				)");
				$this->db->exec("CREATE TABLE 'url_refr' (
					'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
					'url' TEXT,
					'referer' TEXT,
					'freq' INTEGER,
					'lastload' INTEGER
				)");
				$this->db->exec("CREATE TABLE 'bots' (
					'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
					'day' TEXT,
					'bot' TEXT,
					'ip' INTEGER,
					'cnt' INTEGER
				)");
				$this->db->exec("CREATE UNIQUE INDEX 'ind_url' ON 'url' ('url')");
				$this->db->exec("CREATE UNIQUE INDEX 'ind_url_referer' ON 'url_refr' ('url', 'referer')");
				$this->db->exec("CREATE UNIQUE INDEX 'ind_day_bot_ip' ON 'bots' ('day', 'bot', 'ip')");
				$res = $this->db->exec("COMMIT");

				$this->db_ok = $res !== false && ! $this->db->lastErrorCode() ? true : false;
				$this->db_op = $this->db_ok;
			}
		}

		return $this->db_ok;
	}

	function upgrade()
	{
		$folder = $this->droot.$this->module_folder;

		if ( ! file_exists($folder.'/'.$this->domain_h.'/')) {
			$res = mkdir($folder.'/'.$this->domain_h.'/', 0755, true);
			if ($res !== true) {
				$errors = true;
				print 'hash folder'."\n";
			}
		}
		if (file_exists($folder.'/t/')) {
			$res = rename($folder.'/t/', $folder.'/txt/');
			if ($res !== true) {
				$errors = true;
				print 'text folder'."\n";
			}
		}
		if (file_exists($folder.'/i/')) {
			$res = rename($folder.'/i/', $folder.'/img/');
			if ($res !== true) {
				$errors = true;
				print 'imgs folder'."\n";
			}
		}
		if ( ! file_exists($folder.'/transit.txt') &&
			file_exists($folder.'/transit_'.$this->domain_h.'.txt')) {
			$res = rename($folder.'/transit_'.$this->domain_h.'.txt',
				$folder.'/transit.txt');
			if ($res !== true) {
				$errors = true;
				print 'transit file'."\n";
			}
		}
		if ( ! file_exists($folder.'/'.$this->domain_h.'/config.txt') &&
			file_exists($folder.'/config_'.$this->domain_h.'.txt')) {
			$res = rename($folder.'/config_'.$this->domain_h.'.txt',
				$folder.'/'.$this->domain_h.'/config.txt');
			if ($res !== true) {
				$errors = true;
				print 'config file'."\n";
			}
		}
		if ( ! file_exists($folder.'/'.$this->domain_h.'/head.txt') &&
			file_exists($folder.'/head_'.$this->domain_h.'.txt')) {
			$res = rename($folder.'/head_'.$this->domain_h.'.txt',
				$folder.'/'.$this->domain_h.'/head.txt');
			if ($res !== true) {
				$errors = true;
				print 'head file'."\n";
			}
		}
		if ( ! file_exists($folder.'/'.$this->domain_h.'/body.txt') &&
			file_exists($folder.'/body_'.$this->domain_h.'.txt')) {
			$res = rename($folder.'/body_'.$this->domain_h.'.txt',
				$folder.'/'.$this->domain_h.'/body.txt');
			if ($res !== true) {
				$errors = true;
				print 'body file'."\n";
			}
		}
		if ( ! file_exists($folder.'/'.$this->domain_h.'/errors.txt') &&
			file_exists($folder.'/errors_'.$this->domain_h.'.txt')) {
			$res = rename($folder.'/errors_'.$this->domain_h.'.txt',
				$folder.'/'.$this->domain_h.'/errors.txt');
			if ($res !== true) {
				$errors = true;
				print 'errors file'."\n";
			}
		}
		if ( ! file_exists($folder.'/'.$this->domain_h.'/style.css') &&
			file_exists($folder.'/style_'.$this->domain_h.'.css')) {
			$res = rename($folder.'/style_'.$this->domain_h.'.css',
				$folder.'/'.$this->domain_h.'/style.css');
			if ($res !== true) {
				$errors = true;
				print 'style file'."\n";
			}
		}

		if (($open = opendir($folder.'/img/'))) {
			$errors = false;
			while ($file = readdir($open)) {
				if (filetype($folder.'/img/'.$file) == 'link'
					|| $file == '.' || $file == '..'
					|| $file == '.th')
					continue;
				if (is_dir($folder.'/img/'.$file))
					continue;
				if ( ! is_file($folder.'/img/'.$file))
					continue;
				if (substr($file, strpos($file, '.')-2, 1) == '_')
					continue;
				$ext = substr($file, strpos($file, '.')-1);
				$name = substr($file, 0, strpos($file, '.')-1);
				$name = $name.'_'.$ext;
				$res = rename($folder.'/img/'.$file, $folder.'/img/'.$name);
				if ($res !== true) {
					$errors = true;
					print 'img file'."\n";
				}
			}
		}
		return $errors ? false : true;
	}
}
//---------
