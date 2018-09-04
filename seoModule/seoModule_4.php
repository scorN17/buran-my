<?php
/**
 * seoModule
 * @version 4.06
 * @date 04.09.2018
 * @author <sergey.it@delta-ltd.ru>
 * @copyright 2018 DELTA http://delta-ltd.ru/
 * @size 38000
 */

$bsm = new buran_seoModule('4.06');
$bsm->init();

if (
	$bsm->c
	&& basename($bsm->pageurl) !== 'seoModule.php'
	&& (
		$bsm->c[2]['module_enabled']
		|| $_SERVER['REMOTE_ADDR'] === $bsm->c[2]['module_enabled']
		|| $bsm->test
	)
	&& (
		strpos($bsm->c[2]['requets_methods'],
			'/'.$_SERVER['REQUEST_METHOD'].'/') !== false
		|| $bsm->test
	)
	// не забудь настроить ELSE
) {
	if ($bsm->c[2]['redirect']) {
		$bsm->redirects();
	}

	if (strpos($_SERVER['HTTP_USER_AGENT'], 'buran_seomodule') === false) {
		$bsm->clear_request();

		$output = false;
		if ($bsm->module_hash) {
			$seotext = $bsm->seotext();
		} else {
			$bsm->log('[02]');
		}
		$template = false;
		if ($seotext || $bsm->c[2]['all_pages']) {
			$template = $bsm->get_content();
		}
		$tags = false;
		if ($template) {
			$tags = $bsm->get_tag('finish');
			if ($tags && (
				$bsm->seotext_tp == 'S' ||
				$bsm->seotext_tp == 'W'
			)) {
				$tags = $bsm->get_tag('start');
			}

			if ($bsm->c[2]['all_pages']) {
				$output = true;
			}
		}
		if ($seotext && $tags) {
			$bsm->text_parse();
			if ($bsm->requesturi == $bsm->c[1]['articles']) {
				$bsm->articles_parse();
			} elseif ($bsm->c[2]['re_linking']) {
				$bsm->articles_parse($bsm->seotext_alias, $bsm->c[2]['re_linking']);
			}
			$bsm->template_parse();
			$bsm->tdk_parse();
			$output = true;
		}
		if ($output) {
			if ($bsm->module_hash) {
				$bsm->meta_parse();
			}
			$bsm->head_body_parse();
			$bsm->output_content();
		}
	}
} elseif (
	basename($bsm->pageurl) !== 'seoModule.php'
	&& strpos($_SERVER['HTTP_USER_AGENT'], 'buran_seomodule') === false
	&& (
		strpos($bsm->c[2]['requets_methods'],
			'/'.$_SERVER['REQUEST_METHOD'].'/') !== false
		|| $bsm->test
	)
) {
	$bsm->log('[01]');
}

if ('seoModule.php' === basename($bsm->pageurl)) {

if ('list' == $_GET['a']) {
	header('Content-type: text/html; charset=utf-8');

	$green = '#089c29';
	$red   = '#d41717';

	print $bsm->module_hash().'<br><br>';

	$flag = version_compare(PHP_VERSION, '5.4.0', '<') ? false : true;
	print '<div>Версия PHP: <span style="color:'.($flag ? $green : $red).'">'.PHP_VERSION.'</span></div>';

	$flag = $bsm->website === $bsm->c[1]['website'] ? true : false;
	print '<div>Домен: <span style="color:'.($flag ? $green : $red).'">'.$bsm->website.' == '.$bsm->c[1]['website'].'</span></div>';

	$flag = $bsm->c[1]['bunker_id'] ? true : false;
	print '<div>ID в бункере: <span style="color:'.($flag ? $green : $red).'">'.$bsm->c[1]['bunker_id'].'</span></div>';

	$flag = extension_loaded('gd') ? true : false;
	print '<div>Кроп картинок: <span style="color:'.($flag ? $green : $red).'">'.($flag ? 'Да' : 'Нет').'</span></div>';

	$flag = extension_loaded('iconv') ? true : false;
	print '<div>Перекодировка текстов: <span style="color:'.($flag ? $green : $red).'">'.($flag ? 'Да' : 'Нет').'</span></div>';

	$flag = extension_loaded('openssl') ? true : false;
	print '<div>OpenSSL: <span style="color:'.($flag ? $green : $red).'">'.OPENSSL_VERSION_TEXT.' ['.OPENSSL_VERSION_NUMBER.']</span></div>';

	$flag = extension_loaded('curl') && function_exists('curl_init') ? true : false;
	print '<div>cURL: <span style="color:'.($flag ? $green : $red).'">'.($flag ? 'Да' : 'Нет').'</span></div>';

	$flag = function_exists('stream_get_contents') ? true : false;
	print '<div>Stream: <span style="color:'.($flag ? $green : $red).'">'.($flag ? 'Да' : 'Нет').'</span></div>';

	print '<br><br><br>';
	exit();
}

if ('info' == $_GET['a']) {
	$files = $bsm->c[2]['include_files'];
	$files = explode('  ', $files);
	if (is_array($files)) {
		foreach ($files AS $file) {
			$file = trim($file);
			if ( ! $file) continue;
			$fh = fopen($bsm->droot.$file, 'rb');
			if ( ! $fh) {
				$bsm->log('[05]');
				continue;
			}
			$incfiles_hash .= md5_file($bsm->droot.$file).' : '.$file."\n";
			$code = '';
			while ( ! feof($fh)) $code .= fread($fh, 1024*8);
			fclose($fh);
			if ( ! strpos($code, '_buran/seoModule.php')) {
				$bsm->log('[06]');
			}
		}
	}

	$hash_1 = md5_file($bsm->droot.'/_buran/seoModule.php');
	$hash_2 = md5_file($bsm->droot.'/_buran/seoModule/config_'.$bsm->domain_h.'.txt');
	$hash_3 = md5_file($bsm->droot.'/_buran/seoModule/style_'.$bsm->domain_h.'.css');

	print '[seomoduleversion_'.$bsm->version.']'."\n";
	print '[modulehash_'.$hash_1.']'."\n";
	print '[confighash_'.$hash_2.']'."\n";
	print '[stylehash_'.$hash_3.']'."\n";
	print '[droot_'.$bsm->droot.']'."\n";
	print '[incfiles_]'."\n";
	print $incfiles_hash;
	print '[_incfiles]'."\n";
	print '[pages_]'."\n";
	if (is_array($bsm->c[3])) {
		foreach ($bsm->c[3] AS $alias => $row) {
			if ($row[0] == $bsm->c[1]['articles']) continue;
			$text = $bsm->seofile($alias);
			$hash = md5_file($text['file']);
			print $hash.' : '.$alias."\n";
		}
	}
	print '[_pages]'."\n";
	print '[errors_]'."\n";
	$fh = fopen($bsm->droot.'/_buran/seoModule/errors_'.$bsm->domain_h.'.txt','rb');
	if ($fh) {
		$content = '';
		while ( ! feof($fh)) $content .= fread($fh, 1024*8);
		fclose($fh);
		print $content;
	}
	print '[_errors]'."\n";
	exit();
}

if ('update' == $_GET['a']) {
	if ( ! ($bsm->auth())) exit('er');
	$bsm->htaccess();
	$code = $_POST['c'];
	if ( ! in_array($_GET['t'],
		array('module','config','style','head','body','text','imgs'))) {
		exit('er');
	}
	$alias = $_GET['n'];
	$alias = preg_replace("/[^a-z0-9_]/", '', $alias);
	switch ($_GET['t']) {
		case 'module':
			$code = base64_decode($code);
			if (strpos($code, "<?php\n/**\n * seoModule") !== 0) {
				exit('er');
			}
			$file = __FILE__;
			break;
		case 'config':
			$file = $bsm->droot.'/_buran/seoModule/config_'.$bsm->domain_h.'.txt';
			break;
		case 'style':
			$code = base64_decode($code);
			$file = $bsm->droot.'/_buran/seoModule/style_'.$bsm->domain_h.'.css';
			break;
		case 'head':
			$file = $bsm->droot.'/_buran/seoModule/head_'.$bsm->domain_h.'.txt';
			$delete_if_null = true;
			break;
		case 'body':
			$file = $bsm->droot.'/_buran/seoModule/body_'.$bsm->domain_h.'.txt';
			$delete_if_null = true;
			break;
		case 'text':
			$folder = $bsm->droot.'/_buran/seoModule/t/';
			if ( ! file_exists($folder)) {
				mkdir($folder, 0755, true);
			}
			$file = $folder.'txt_'.$alias.'.txt';
			break;
		case 'imgs':
			$folder = $bsm->droot.'/_buran/seoModule/i/';
			if ( ! file_exists($folder)) {
				mkdir($folder, 0755, true);
			}
			$cc = intval($_POST['fs']);
			if ( ! $cc) exit('ok');
			for ($k=1; $k<=$cc; $k++) {
				$file = $_FILES['f'.$k];
				$ext = substr($file['name'], strpos($file['name'],'.'));
				if ($ext != '.jpg' && $ext != '.png') continue;
				$res = move_uploaded_file($file['tmp_name'], $folder.$file['name']);
				if ( ! $res) exit('er');
			}
			exit('ok');
	}
	if ($delete_if_null && ! $code) {
		unlink($file);
		exit('ok');
	}
	$fh = fopen($file, 'wb');
	if ( ! $fh) exit('er');
	$res = fwrite($fh, $code);
	if ($res === false) exit('er');
	fclose($fh);
	exit('ok');
}

if ('deactivate' == $_GET['a']) {
	if ( ! ($bsm->auth())) exit('er');
	$module_hash = $bsm->droot.'/_buran/seoModule/'.$bsm->module_hash();
	if (file_exists($module_hash)) {
		unlink($module_hash);
		$this->log('[07]');
	}
	exit('ok');
}
if ('transform' == $_GET['a']) {
	if ( ! ($bsm->auth())) exit('er');

	exit('ok');
}

if ('clearlog' == $_GET['a']) {
	if ( ! ($bsm->auth())) exit('er');
	$bsm->log('', $_GET['t'], true);
	exit('ok');
}
}

// ------------------------------------------------------------------

class buran_seoModule
{
	public $version;

	public $c = false;
	public $module_hash = false;

	public $droot;
	public $website;
	public $http;
	public $www;
	public $domain;
	public $domain_h;
	public $scriptname;
	public $requesturi;
	public $pageurl;
	public $querystring;
	public $protocol;

	public $test = false;
	public $test_stitle;
	public $test_stext;

	public $declension;

	public $seotext = false;
	public $seotext_alias;
	public $seotext_tp;
	public $seotext_hide = false;
	public $seotext_date;
	public $seotext_multi = false;
	public $donor;
	public $encode;
	public $encode_e;
	public $tabs = false;

	public $template;
	public $body;
	public $headers;
	public $tag_s = false;
	public $tag_f = false;
	public $code = array();

	public $logs_files;

	function __construct($version)
	{
		$this->version = $version;
	}

	function init()
	{
		$this->droot = dirname(dirname(__FILE__));
		$this->http = (
			$_SERVER['SERVER_PORT'] == '443' || $_SERVER['HTTP_PORT']   == '443' ||
			$_SERVER['HTTP_HTTPS']  == 'on' ||
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
		$this->domain   = $domain;
		$this->domain_h = md5($domain);
		$this->website  = $this->http . $this->www . $this->domain;
		$this->scriptname = isset($_SERVER['SCRIPT_NAME'])
			? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF'];
		$this->requesturi  = $_SERVER['REQUEST_URI'];
		$this->pageurl     = parse_url($this->requesturi, PHP_URL_PATH);
		$this->querystring = substr($this->requesturi, strpos($this->requesturi, '?')+1);
		$sapi_type   = php_sapi_name();
		if (substr($sapi_type,0,3) == 'cgi') {
			$this->protocol = 'Status:';
		} else {
			$this->protocol = $_SERVER['HTTP_X_PROTOCOL']
				? $_SERVER['HTTP_X_PROTOCOL'] : ($_SERVER['SERVER_PROTOCOL']
					? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1');
		}
		if (isset($_POST['seomodule_test']) &&
			isset($_POST['s_title']) && isset($_POST['s_text'])) {
			$this->test        = true;
			$this->test_stitle = $_POST['s_title'];
			$this->test_stext  = $_POST['s_text'];
		}

		if (file_exists($this->droot.'/_buran/seoModule/'.$this->module_hash())) {
			$this->module_hash = true;
		} else {
			$this->log('[02]');
		}

		$file = $this->droot.'/_buran/seoModule/config_'.$this->domain_h.'.txt';
		$fh = fopen($file, 'rb');
		if ($fh) {
			$this->c = '';
			while ( ! feof($fh)) $this->c .= fread($fh, 1024*8);
			fclose($fh);
			if ($this->c) {
				$this->c = base64_decode($this->c);
				$this->c = unserialize($this->c);

				$this->c[2]['re_linking'] = intval($this->c[2]['re_linking']);

				$this->c[2]['out_charset'] = strtolower($this->c[2]['out_charset']);
				$this->encode = $this->c[2]['out_charset']
					&& $this->c[2]['out_charset'] != 'utf-8'
					&& $this->c[2]['out_charset'] != 'utf8'
					? true : false;
				$this->encode_e = '//TRANSLIT//IGNORE';

				if ($this->c[2]['urldecode']) {
					$this->requesturi = urldecode($this->requesturi);
				}

				$this->declension = $this->c[7][$this->c[1]['city']];

				$this->c[2]['ignore_errors'] = str_replace(' ', '', $this->c[2]['ignore_errors']);
			}
		}
	}

	function redirects()
	{
		if ( ! is_array($this->c[4])) {
			return;
		}
		$redirect_to = $this->requesturi;
		if ($this->c[4][$redirect_to]) {
			$redirect_to = $this->c[4][$redirect_to];
		}
		foreach ($this->c[4] AS $from => $to) {
			if (substr($from,0,1) !== '+') continue;
			$from = substr($from,1);
			if (preg_match($from, $redirect_to) === 1) {
				$redirect_to = preg_replace($from, $to, $redirect_to);
			}
		}
		if ($this->c[4][$redirect_to]) {
			$redirect_to = $this->c[4][$redirect_to];
		}
		if ($redirect_to == $this->requesturi) $redirect_to = false;
		if ( ! $redirect_to && $this->website !== $this->c[1]['website']) {
			$redirect_to = $this->requesturi;
		}
		if ($redirect_to) {
			header('Location: '.$this->c[1]['website'].$redirect_to, true, 301);
			exit();
		}
	}

	function clear_request()
	{
		while (preg_match("/((&|^)(_openstat|utm_.*|yclid)=.*)(&|$)/U",
			$this->querystring, $matches) === 1) {
			$this->querystring
				= preg_replace("/((&|^)(_openstat|utm_.*|yclid)=.*)(&|$)/U",
					'${4}', $this->querystring);
			if (strpos($this->querystring,'&') === 0) {
				$this->querystring = substr($this->querystring, 1);
			}
			$this->requesturi = $this->pageurl . ($this->querystring ? '?'.$this->querystring : '');
		}
	}

	function seofile($alias)
	{
		$file = $this->droot.'/_buran/seoModule/t/txt_'.$alias.'.txt';
		$fh = fopen($file, 'rb');
		if ( ! $fh) {
			$this->log('[10]');
			return false;
		}
		$text = '';
		while ( ! feof($fh)) $text .= fread($fh, 1024*8);
		fclose($fh);
		$text = base64_decode($text);
		$text = unserialize($text);
		$text['file'] = $file;
		if (
			! is_array($text)
			|| ! isset($text['title'])
			|| ! isset($text['description'])
			|| ! isset($text['keywords'])
			|| ! isset($text['s_title'])
			|| ! isset($text['s_text'])
		) {
			$this->log('[11]');
			return false;
		}
		return $text;
	}

	function seotext()
	{
		if ( ! is_array($this->c[3])) {
			return false;
		}
		$seotext_alias = false;
		$seotext_tp    = 's';
		$seotext_sh    = 's';
		foreach ($this->c[3] AS $alias => $prms) {
			if ($this->requesturi == $prms[0]) {
				$seotext_alias = $alias;
				$seotext_tp    = $prms[1];
				$seotext_sh    = $prms[2];
				break;
			}
		}

		if ( ! $seotext_alias) return false;
		$this->seotext_alias = $seotext_alias;
		$text = $this->seofile($seotext_alias);
		if ( ! $text) return false;
		$this->seotext = $text;

		$this->seotext_date = date('Y-m-d', filectime($text['file']));

		$this->seotext_tp = $seotext_tp=='a'?'A':($seotext_tp=='w'?'W':'S');

		$flag = $this->c['hide_opt'] === '1'
			? true : ($this->c['hide_opt'] === '0'
				? false
				: (strpos($this->c['hide_opt'], $this->seotext_tp) !== false
					? true : false));
		$flag = $seotext_sh === 'h'
			? true : ($seotext_sh === 's'
				? false : $flag);
		$this->seotext_hide = $flag;

		return true;
	}

	function get_content()
	{
		$this->donor = $this->c[1]['website'];
		$this->donor .= $this->seotext_tp == 'S'
			? $this->c[1]['donor'] : $this->requesturi;

		$allheaders     = function_exists('getallheaders')
			? getallheaders() : $this->getallheaders_bsm();
		$useragent_flag = false;
		$headers        = array();
		if (is_array($allheaders)) {
			foreach ($allheaders AS $key => $row) {
				if ( ! $this->c[2]['cookie'] && stripos($key, 'cookie') !== false)
					continue;

				if (stripos($key, 'x-forwarded') !== false) continue;
				if (stripos($key, 'accept-encoding') !== false) continue;
				if (stripos($key, 'x-real-ip') !== false) continue;
				if ($this->c[2]['get_content_method'] == 'stream' &&
					stripos($key, 'connection') !== false) continue;
				if (stripos($key, 'connection') !== false) $row = 'keep-alive';

				if ($this->test) {
					if (stripos($key, 'Content-Length') !== false) continue;
				}

				$header = $key.': '.$row;

				if (stripos($key, 'user-agent') !== false) {
					$useragent_flag = true;
					$header .= ' /buran_seomodule';
				}

				$headers[] = $header;
			}
		}

		if ('curl' == $this->c[2]['get_content_method']) {
			$curloptions = array(
				CURLOPT_URL            => $this->donor,
				CURLOPT_HTTPHEADER     => $headers,
				CURLOPT_HEADER         => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT        => 10,
			);
			if ($http == 'https://' && $this->c[2]['https_test']) {
				$curloptions[CURLOPT_SSL_VERIFYHOST] = false;
				$curloptions[CURLOPT_SSL_VERIFYPEER] = true;
			}
			if ( ! $useragent_flag) {
				$curloptions[CURLOPT_USERAGENT] = ' /buran_seomodule';
			}

			$curl = curl_init();
			curl_setopt_array($curl, $curloptions);
			$template = $this->c[2]['curl_auto_redirect']
				? $this->curl_exec_followlocation($curl, $this->donor)
				: curl_exec($curl);
			$request_info = curl_getinfo($curl);
			list($headers_req, $template) = explode("\n\r", $template, 2);
			$http_code = $request_info['http_code'];
			if (curl_errno($curl)) {
				$fail = true;
				$this->log('[20]');
			} else {
				$headers_req = str_replace("\r",'',$headers_req);
				$headers_req = explode("\n", $headers_req);
			}
			curl_close($curl);
		}

		if ('stream' == $this->c[2]['get_content_method']) {
			$options = array(
				'http' => array(
					'method' => 'GET',
					'header' => $headers,
				)
			);
			if( ! $useragent_flag) {
				$options['http']['user_agent'] = ' /buran_seomodule';
			}
			$context = stream_context_create($options);
			$stream  = fopen($this->donor, 'rb', false, $context);
			if ($stream) {
				$template    = stream_get_contents($stream);
				$headers_req = stream_get_meta_data($stream);
				fclose($stream);
				$headers_req = $headers_req['wrapper_data'];
				$http_code   = 200;
			} else {
				$fail = true;
				$this->log('[21]');
			}
		}

		$this->headers = array();
		if ($this->c[2]['set_header'] && is_array($headers_req)) {
			foreach ($headers_req AS $key => $header) {
				if (stripos($header, 'Transfer-Encoding:') !== false)
					continue;
				if (stripos($header, 'Content-Length:') !== false)
					continue;
				$this->headers[] = $header;
			}
		}
		if ($this->seotext_tp == 'S') {
			$this->headers[] = $this->protocol .' 200 OK';
		}

		if ($http_code != 200) {
			$fail = true;
			$this->log('[30]');
		}

		if (
			$http_code == 404
			&& $this->seotext
			&& $this->seotext_tp != 'S'
		) {
			$this->log('[31]');
			$this->seotext_tp = 'S';
			$res = $this->get_content();
			return $res;
		}

		if ($fail) {
			$this->template = false;
			return false;
		} else {
			$this->template = trim($template);
			return true;
		}
	}

	function get_tag($type='finish')
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
			$res = preg_match("/".$tag."/s", $this->template);
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
		if ($type == 'start')  $this->log('[40]');
		if ($type == 'finish') $this->log('[41]');
		return false;
	}

	function text_parse()
	{
		$template = &$this->template;
		$st       = &$this->seotext;

		if ($this->test) {
			$st['s_title'] = $this->test_stitle;
			$st['s_text']  = $this->test_stext;
		}

		if ($this->encode) {
			foreach ($st AS $txtk => $txt) {
				if ( ! is_array($txt)) {
					$st[$txtk] = iconv('utf-8',
						$this->c[2]['out_charset'].$this->encode_e, $txt);
					continue;
				}
				foreach ($txt AS $key => $row) {
					$st[$txtk][$key] = iconv('utf-8',
						$this->c[2]['out_charset'].$this->encode_e, $row);
				}
			}
		}

		$st['s_text'] = str_replace('<p>[img]</p>', '[img]', $st['s_text']);
		$st['s_text'] = str_replace('<p>[col]</p>', '[col]', $st['s_text']);
		$st['s_text'] = str_replace('<p>[part]</p>', '[part]', $st['s_text']);

		$imgs = glob($this->droot.'/_buran/seoModule/i/'.$this->seotext_alias.'_[0-9]*.{jpg,png}', GLOB_BRACE);
		$st['s_img_f'] = array();
		if (is_array($imgs)) {
			foreach ($imgs AS $key => $row) {
				$img = str_replace($this->droot, '', $row);
				$st['s_img_f'][] = array(
					'src' => $img,
					'alt' => $st['s_img'][$key],
				);
			}
		}

		$flag_dopimgs = false;
		$i = 0;
		while ($img = array_shift($st['s_img_f'])) {
			$img_p = '';
			if ($img['src']) {
				$i++;
				$img['attr'] = $img['alt'].' ('.($i==1 ? 'рисунок' : 'фото').')';
				$img_p = '
<div class="sssmb_img '.($i%2===0 ? 'sssmb_img_l' : 'sssmb_img_r').'">
	<img itemprop="image" src="'.$img['src'].'" alt="'.$img['attr'].'" title="'.$img['attr'].'" />
	<div class="sssmb_bck">
		<div class="sssmb_ln"></div>
		<div class="sssmb_alt">'.$img['alt'].'</div>
	</div>
</div>';
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
			$st['s_text'] .= '</div>'; //.sssmb_dopimgs
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
			$this->tabs = true;
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

		$this->seotext_multi = strpos($st['s_text'], '[part]') !== false
			? true : false;
		if ($this->seotext_multi) {
			$st['s_text'] = explode('[part]', $st['s_text']);
		}
		if ($this->seotext_multi) {
			$s_text_single = '';
			foreach ($st['s_text'] AS $key => $row) {
				$row = trim($row);
				$foo = "<\!--bsm_start_".($key+1)."-->(.*)<!--bsm_finish_".($key+1)."-->";
				$template = preg_replace("/".$foo."/s", $row,
					$template, 1, $matches);
				if ( ! $matches) $s_text_single .= $row;
			}
			$st['s_text'] = $s_text_single;
		}
	}

	function articles_parse($alias_start=false, $limit=0)
	{
		$imgs = '/_buran/seoModule/i/';
		$flag = $alias_start ? true : false;
		foreach ($this->c[3] AS $alias => $row) {
			if ($row[0] == $this->c[1]['articles']) continue;
			if ($flag) {
				if ($alias == $alias_start) {
					$flag = false;
				}
				continue;
			}
			if ($alias_start && $limit<=0) break;
			$limit--;
			$counter++;
			$text = $this->seofile($alias);
			if ( ! $text) continue;
			for ($k=1; $k<=10; $k++) {
				$img = $imgs.$alias.'_'.$k.'.jpg';
				if (file_exists($this->droot.$img)) break;
				$img = false;
			}
			if ($this->encode) {
				$text['description'] = iconv('utf-8',
					$this->c[2]['out_charset'].$this->encode_e, $text['description']);
				$text['s_title'] = iconv('utf-8',
					$this->c[2]['out_charset'].$this->encode_e, $text['s_title']);
			}
			$txt .= '<div class="sssmba_itm">
				<div class="sssmba_img">';
			if ($img) $txt .= '<img src="'.$img.'" alt="" />';
			$txt .= '</div>
				<div class="sssmba_inf">
					<div class="sssmba_tit"><a href="'.$row[0].'">'.$text['s_title'].'</a></div>
					<div class="sssmba_txt">'.$text['description'].'</div>
				</div>
			</div>';
			$counter--;
		}
		if ($txt) {
			if ($alias_start) {
				$txt = '<div class="sssmb_h2 sssmb_h2_cols">
					<div class="col"><h2>Статьи</h2></div>
					<div class="col rght"><a href="'.$this->c[1]['articles'].'">Все статьи</a></div>
				</div>'.$txt;
			}
			$txt = '<div class="sssmb_clr"></div><div class="sssmb_articles">'.$txt.'</div>';
			$this->seotext['s_text'] .= $txt;
		}
		if ($counter) $this->log('[12]');
	}

	function template_parse()
	{
		$template = &$this->template;
		$body     = &$this->body;
		$st       = $this->seotext;

		$body = '<link rel="stylesheet" href="/_buran/seoModule/style_'.$this->domain_h.'.css" />';

		if ($this->seotext_hide) {
			$body .= '
<script>
	function sssmb_chpoktext(){
		obj= document.getElementById("sssmodulebox");
		if(obj.style.display=="none") obj.style.display= "";
		else obj.style.display= "none";
	}
</script>
<article onclick="sssmb_chpoktext()">&rarr;</article>';
		}

		if ($this->tabs) {
			$body .= '
<script>
document.onreadystatechange = function(){
	if (document.readyState != "interactive") return;
	var tabs = document.getElementById("sssmb_tabs");
	if ( ! tabs) return;
	var butts = tabs.getElementsByClassName("sssmbt_butt");
	Array.prototype.filter.call(butts, function(butt){
		butt.onclick = function(e){
			if (butt.classList.contains("sssmbt_butt_a")) {
				return;
			}
			let tabid = butt.dataset.tabid;
			tabs.getElementsByClassName("sssmbt_butt_a")[0].classList.remove("sssmbt_butt_a");
			this.classList.add("sssmbt_butt_a");
			tabs.getElementsByClassName("sssmbt_itm_a")[0].classList.remove("sssmbt_itm_a");
			tabs.getElementsByClassName("sssmbt_itm_"+tabid)[0].classList.add("sssmbt_itm_a");
		};
	});
};
</script>';
		}

		if ($this->test) {
			$body .= '
<script>
window.onload = function(){
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
};
</script>';
		}

		$body .= '
<section id="sssmodulebox" class="sssmodulebox turbocontainer '.$this->c[2]['classname'].'" '.($this->seotext_hide?'style="display:none;"':'').' itemscope itemtype="http://schema.org/Article">
	<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="'.$this->c[1]['website'].$this->requesturi.'" />
	<div class="sssmb_clr">&nbsp;</div>';

		if ($this->seotext_tp == 'A' || $this->seotext_tp == 'W') {
			$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU",
				'<h2 ${1}>${2}</h2>', $template, -1, $hcc);
		} else {
			$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU",
				'<h1 ${1} itemprop="name">'.$st['s_title'].'</h1>',
				$template, -1, $hcc);
		}

		if ($hcc >= 2) {
			$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU", '', $template);
			$this->log('[50]');
		}

		if ($this->seotext_tp == 'A' || $this->seotext_tp == 'W' || ! $hcc) {
			$body .= '<div class="sssmb_h1"><h1 itemprop="name">'.$st['s_title'].'</h1></div>';
		}

		list($logo_w, $logo_h) = getimagesize($this->droot.$this->c[1]['logo']);

		$body .= '
<div class="sssmb_cinf">
	<p itemprop="author">Автор: '.$this->c[1]['company_name'].'</p>
	<div itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
		<meta itemprop="name" content="'.$this->domain.'" />
		<meta itemprop="telephone" content="'.$this->c[1]['phone'].'" />
		<meta itemprop="address" content="'.addslashes($this->c[1]['address']).'" />
		<div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
			<img itemprop="url" itemprop="image" src="'.$this->website.$this->c[1]['logo'].'" />
			<meta itemprop="width" content="'.$logo_w.'" />
			<meta itemprop="height" content="'.$logo_h.'" />
		</div>
	</div>
	<p>Дата публикации: <time itemprop="datePublished">'.date('Y-m-d',strtotime($this->c[1]['date_start'])).'</time></p>
	<p>Дата изменения: <time itemprop="dateModified">'.$this->seotext_date.'</time></p>
	<noindex><p itemprop="headline" itemprop="description">'.$st['title'].'</p></noindex>
</div>';

		$body .= '<div class="sssmb_stext" itemprop="articleBody">';

		$body .= $st['s_text'];

		$body .= '<div class="sssmb_clr">&nbsp;</div></div>';
		if($this->c[2]['share_code'])
			$body .= '<div class="yasharebox">'.$this->c[2]['share_code'].'</div>';
		$body .= '</section>';

		if ($this->seotext_tp == 'A') {
			$foo = $this->tag_f['p'] == 'a' ? $this->tag_f['t'] : '';
			$foo .= $body;
			$foo .= $this->tag_f['p'] == 'b' ? $this->tag_f['t'] : '';
			$template = preg_replace("/".$this->tag_f['m']."/s", $foo, $template, 1);

		} elseif ($this->seotext_tp == 'S' || $this->seotext_tp == 'W') {
			$foo = $this->tag_s['p'] == 'a' ? $this->tag_s['t'] : '';
			$foo .= $body;
			$foo .= $this->tag_f['p'] == 'b' ? $this->tag_f['t'] : '';
			$template = preg_replace("/".$this->tag_s['m']."(.*)".$this->tag_f['m']."/s", $foo, $template, 1);
		}

		if ($this->c[2]['city_replace']) {
			$template = preg_replace("/\[hide\](.*?)\[hide\]/U", '', $template);
			foreach ($this->declension AS $key => $decl) {
				$template = preg_replace("/\[city_{$key}\](.*?)\[city\]/U", $decl, $template);
			}
		}
	}

	function tdk_parse()
	{
		$template = &$this->template;
		$st       = $this->seotext;
		$c        = $this->c[2];

		if (in_array($c['meta'],
			array('replace_or_add', 'replace_if_exists', 'delete'))) {
			$title = '<title>'.$st['title'].'</title>';
			$description = '<meta name="description" content="'.$st['description'].'" />';
			$keywords = '<meta name="keywords" content="'.$st['keywords'].'" />';
			if ($c['meta'] == 'replace_or_add')
				$title .= "\n\t".$description."\n\t".$keywords."\n";
			if ($c['meta'] == 'delete' ||
				$c['meta'] == 'replace_or_add') {
				if ($c['meta'] == 'delete') $title = '';
				$description = '';
				$keywords = '';
			}
			$template = preg_replace("/<meta [.]*name=('|\")description('|\")(.*)>/isU", $description, $template, 2, $count1);
			$template = preg_replace("/<meta [.]*name=('|\")keywords('|\")(.*)>/isU", $keywords, $template, 2, $count2);
			$template = preg_replace("/<title>(.*)<\/title>/isU", $title, $template, 2, $count3);
			if ($count1 !== 1 || $count2 !== 1 || $count3 !== 1) {
				$this->log('[61]');
			}
		}
	}

	function meta_parse()
	{
		$template = &$this->template;
		$st       = $this->seotext;
		$c        = $this->c[2];

		if (in_array($c['base'],
			array('replace_or_add', 'replace_if_exists', 'delete'))) {
			$base = '<base href="'.$this->c[1]['website'].'/" />';
			if ($c['base'] == 'replace_or_add' ||
				$c['base'] == 'delete') {
				$template = preg_replace("/<base (.*)>/iU", '', $template);
			}
			if ($c['base'] == 'replace_or_add') {
				$template = preg_replace("/<title>/i", $base."\n\t".'<title>', $template, 2, $count);
				if ($count !== 1) $this->log('[62]');

			} elseif ($c['base'] == 'replace_if_exists') {
				$template = preg_replace("/<base (.*)>/iU", $base, $template, 2, $count);
				if ($count === 2) $this->log('[63]');
			}
		}

		if (in_array($c['canonical'],
			array('replace_or_add', 'replace_if_exists', 'delete'))) {
			$canonical = '<link rel="canonical" href="'.$this->c[1]['website'].$this->requesturi.'" />';
			if ($c['canonical'] == 'replace_or_add' ||
				$c['canonical'] == 'delete') {
				$template = preg_replace("/<link (.*)rel=('|\")canonical('|\")(.*)>/iU", '', $template);
			}
			if ($c['canonical'] == 'replace_or_add') {
				$template = preg_replace("/<title>/i", $canonical."\n\t".'<title>', $template, 2, $count);
				if ($count !== 1) $this->log('[64]');

			} elseif ($c['canonical'] == 'replace_if_exists') {
				$template = preg_replace("/<link (.*)rel=('|\")canonical('|\")(.*)>/iU", $canonical, $template, 2, $count);
				if ($count === 2) $this->log('[65]');
			}
		}
	}

	function get_code($type)
	{
		$type = $type == 'head' ? 'head' : 'body';
		$file = $this->droot.'/_buran/seoModule/'.$type.'_'.$this->domain_h.'.txt';
		$fh = fopen($file, 'rb');
		if ($fh) {
			$code = '';
			while ( ! feof($fh)) $code .= fread($fh, 1024*8);
			fclose($fh);
			if ($code) {
				$code = base64_decode($code);
				$this->code[$type] = $code;
				return true;
			}
		} else {
			$this->log('[00]');
		}
		return false;
	}

	function head_body_parse()
	{
		$template = &$this->template;
		$c        = $this->c[2];

		if ($this->c[2]['use_head']) {
			$head = $this->get_code('head');
			if ($head) {
				$head = "\n".'<!--bsm_head_code-->'."\n".$this->code['head']."\n".'</head>';
				$template = preg_replace("/<\/head>/i", $head, $template, 1, $count);
				if ( ! $count) {
					$this->log('[70]');
				}
			}
		}

		if ($this->c[2]['use_body']) {
			$body = $this->get_code('body');
			if ($body) {
				$body = "\n".'<!--bsm_body_code-->'."\n".$this->code['body']."\n".'</body>';
				$template = preg_replace("/<\/body>/i", $body, $template, 1, $count);
				if ( ! $count) {
					$this->log('[71]');
				}
			}
		}
	}

	function output_content()
	{
		if (is_array($this->headers)) {
			foreach ($this->headers AS $key => $header) {
				header($header);
			}
		}
		print $this->template;
		exit();
	}

// ------------------------------------------------------------------

	function log($text, $type='errors', $clear=false)
	{
		if ($this->c[2]['ignore_errors'] && $type == 'errors' && ! $clear) {
			$foo = str_replace(array('[',']'), '', $text);
			if (stripos(','.$this->c[2]['ignore_errors'].',', ','.$foo.',') !== false) {
				return;
			}
		}
		$fh = $this->logs_files[$type];
		if ( ! $fh) {
			$file = $this->droot.'/_buran/seoModule/'.$type.'_'.$this->domain_h.'.txt';
			if ($clear) {
				$fh = fopen($file, 'wb');
				if ($fh) {
					$data .= time() ."\t";
					$data .= date('Y-m-d-H-i-s') ."\t";
					$data .= '(truncate)' ."\n";
					fwrite($fh, $data."\n");
					fclose($fh);
				}
				return true;
			}
			if (filesize($file) >= 1024*64) {
				$fh = fopen($file, 'c+b');
				if ($fh) {
					fseek($fh, -1024*8, SEEK_END);
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
			$data .= time() ."\t";
			$data .= date('Y-m-d-H-i-s') ."\t";
			$data .= $text ."\t";
			$data .= $this->requesturi;
			fwrite($fh, $data."\n");
		}
	}

	function module_hash()
	{
		return md5(__FILE__);
	}

	function htaccess()
	{
		$htaccess  = '<FilesMatch "\.txt$">'. "\n";
		$htaccess .= 'Order Deny,Allow'. "\n";
		$htaccess .= 'Deny from all'. "\n";
		$htaccess .= 'RewriteEngine On'. "\n";
		$htaccess .= 'RewriteRule ^(.*)$ index.html [L,QSA]'. "\n";
		$htaccess .= '</FilesMatch>'. "\n";
		$fh = fopen($this->droot.'/_buran/seoModule/.htaccess', 'wb');
		if ( ! $fh) return;
		fwrite($fh, $htaccess);
		fclose($fh);
	}

	function auth()
	{
		$get_w = $_GET['w'];
		$url   = 'http://bunker-yug.ru/__buran/secret_key.php';
		$url  .= '?h='.$this->domain;
		$url  .= '&w='.$get_w;
		$curloptions = array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
		);
		$curl = curl_init();
		curl_setopt_array($curl, $curloptions);
		$ww = curl_exec($curl);
		if ($ww && $get_w && $ww === $get_w) {
			return true;
		}
		return false;
	}

	function curl_exec_followlocation(&$curl, &$uri)
	{
		// v2.1
		// Date 16.02.2017
		// -----------------------------------------
		if (preg_match("/^(http(s){0,1}:\/\/[a-z0-9\.-]+)(.*)$/i", $uri, $matches) !==1) {
			return;
		}
		$website = $matches[1];
		do {
			// if($referer) curl_setopt($curl, CURLOPT_REFERER, $referer);
			curl_setopt($curl, CURLOPT_URL, $uri);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, true);
			$response = curl_exec($curl);
			if (curl_errno($curl)) return false;
			$headers = str_replace("\r",'',$response);
			$headers = explode("\n\n",$headers,2);
			if (preg_match("/^location: (.*)$/im", $headers[0], $matches) ===1) {
				$location = true;
				$referer  = $uri;
				$uri      = trim($matches[1]);
				if (preg_match("/^http(s){0,1}:\/\/[a-z0-9\.-]+/i", $uri, $matches) !==1) {
					$uri= $website.(substr($uri,0,1)!='/'?'/':'').$uri;
				}
			} else {
				$location = false;
			}
			if ($location) {
				if ($redirects_list[$uri]<=1) $redirects_list[$uri]++;
					else $location = false;
			}
		} while ($location);
		return $response;
	}

	/**
	 * https://github.com/ralouphie/getallheaders
	 * 23.12.2016
	 * Get all HTTP header key/values as an associative array for the current request.
	 * @return string[string] The HTTP header key/value pairs.
	 */
	/**
	 * https://github.com/ralouphie/getallheaders
	 * 11.02.2016
	 * Get all HTTP header key/values as an associative array for the current request.
	 * @return string[string] The HTTP header key/value pairs.
	 */
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
				$basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
				$headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
			} elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
				$headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
			}
		}
		return $headers;
	}
}
// ----------------------------------------------
// ----------------------------------------------
// ----------------------------------------------
// ----------------------------------------------
// --------------------------------------------
