<?php
/**
 * seoModule
 * @version 3.4
 * 02.08.2018
 * DELTA
 * sergey.it@delta-ltd.ru
 * @filesize 36000
 */
$seomoduleversion = '3.4';

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'off');

include_once('seoModule_config.php');

$http = (
	$_SERVER['SERVER_PORT'] == '443' ||
	$_SERVER['HTTP_PORT']   == '443' ||
	$_SERVER['HTTP_HTTPS']  == 'on' ||
	(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ||
	(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
		$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
		? 'https://' : 'http://');
$domain      = isset($_SERVER['HTTP_HOST'])
				? $_SERVER['HTTP_HOST']
				: $_SERVER['SERVER_NAME'];
$domain      = explode(':', $domain);
$domain      = $domain[0];
$www         = strpos($domain,'www.')===0 ? 'www.' : '';
if($www=='www.') $domain = substr($domain,4);
$scriptname  = isset($_SERVER['SCRIPT_NAME'])
				? $_SERVER['SCRIPT_NAME']
				: $_SERVER['PHP_SELF'];
$requesturi  = $_SERVER['REQUEST_URI'];
$pageurl     = parse_url($requesturi, PHP_URL_PATH);
$querystring = substr($requesturi, strpos($requesturi, '?')+1);
$droot       = dirname(dirname(__FILE__));
$sapi_type   = php_sapi_name();
if(substr($sapi_type,0,3) == 'cgi')
	$protocol = 'Status:';
else 
	$protocol = $_SERVER['HTTP_X_PROTOCOL']
				? $_SERVER['HTTP_X_PROTOCOL']
				: ($_SERVER['SERVER_PROTOCOL']
					? $_SERVER['SERVER_PROTOCOL']
					: 'HTTP/1.1');
$test = isset($_POST['seomodule_test']) &&
	isset($_POST['seomodule_s_title']) &&
	isset($_POST['seomodule_s_text'])
		? true : false;
$logsfile = array();
// ------------------------------------------------------------------
$website_num = 1;
foreach ($websites AS $key => $ws) {
	if (
		strpos($ws[0].'/', '//'.$domain.'/') ||
		strpos($ws[0].'/', '//www.'.$domain.'/')
	) $website_num = $key;
}
if ($website_num) {
	$config = $configs['global'];
	if (isset($configs[$website_num])) {
		$config = array_merge($config, $configs[$website_num]);
	}
	$config['in_charset'] = strtolower($config['in_charset']);
	$config['out_charset'] = strtolower($config['out_charset']);

	$website = $websites[$website_num];

	if ($config['urldecode']) $requesturi = urldecode($requesturi);

	$redirect = $redirects['global'];
	if (isset($redirects[$website_num])) {
		$redirect = array_merge($redirect, $redirects[$website_num]);
	}

	$redirect_to = $requesturi;

	if ($redirect[$redirect_to]) {
		$redirect_to = $redirect[$redirect_to];
	}
	if (is_array($redirect)) {
		foreach ($redirect AS $reg => $foo) {
			if (substr($reg,0,1) == '+') {
				$reg = substr($reg,1);
				if (preg_match($reg, $redirect_to) === 1) {
					$redirect_to = preg_replace($reg, $foo, $redirect_to);
				}
			}
		}
	}
	if ($redirect[$redirect_to]) {
		$redirect_to = $redirect[$redirect_to];
	}
	if ($redirect_to == $requesturi) $redirect_to = false;
	if ( ! $redirect_to && $http.$www.$domain !== $website[0]) {
		$redirect_to = $requesturi;
	}
	if ($redirect_to) {
		header('Location: '.$website[0].$redirect_to, true, 301);
		exit();
	}
}bsm_tolog('[test]');
// ------------------------------------------------------------------
if (
	$website_num &&
	basename($pageurl) != 'seoModule.php' &&
	(
		$config['module_enabled'] === true ||
		$_SERVER['REMOTE_ADDR'] === $config['module_enabled'] ||
		$test
	) && (
		strpos($config['requets_methods'],
			'/'.$_SERVER['REQUEST_METHOD'].'/') !== false ||
		$test
	) &&
	strpos($_SERVER['HTTP_USER_AGENT'], 'buran_seo_module') === false &&
	file_exists($droot.'/_buran/'.bsm_server())
) {
	while (preg_match("/((&|^)(_openstat|utm_.*|yclid)=.*)(&|$)/U",
		$querystring, $matches)===1) {
		$querystring
			= preg_replace("/((&|^)(_openstat|utm_.*|yclid)=.*)(&|$)/U",
				'${4}', $querystring);
		$bez_utm = true;
	}
	if ($bez_utm) {
		if (strpos($querystring,'&') === 0) {
			$querystring = substr($querystring, 1);
		}
		$requesturi = $pageurl . ($querystring ? '?'.$querystring : '');
	}

	$seopage = $seopages['global'];
	if (isset($seopages[$website_num])) {
		$seopage = array_merge($seopage, $seopages[$website_num]);
	}

	$seopage_row = trim($seopage[$requesturi]);

	if ($seopage_row) {
		$seopage_row = explode(':', $seopage_row);
		$seoalias    = end($seopage_row);

		$seotype  = $seopage_row[0]=='A'?'A':($seopage_row[0]=='W'?'W':'S');

		$hideflag = $config['hide_opt'] === true
			? true
			: ($config['hide_opt'] === false
				? false
				: (strpos($config['hide_opt'],$seotype) !== false
					? true
					: false));

		$hideflag = $seopage_row[1] === '-'
			? true
			: ($seopage_row[1] === '+'
				? false
				: $hideflag);

		if ($website[5]) $declension = $declension[$website[5]];

		if (file_exists($droot.$config['tx_path'].'/'.$seoalias.'.php')) {
			$donor = $website[0];
			$donor .= $seotype=='S' ? $website[2] : $requesturi;

			$useragent_flag  = false;
			$requestsheaders = array();
			$getallheaders   = function_exists('getallheaders')
				? getallheaders()
				: bsm_getallheaders();
			if (is_array($getallheaders)) {
				foreach ($getallheaders AS $key=>$row) {
					if ( ! $config['cookie'] && stripos($key, 'cookie')!==false)
						continue;

					if (stripos($key, 'x-forwarded')!==false) continue;
					if (stripos($key, 'accept-encoding')!==false) continue;
					if (stripos($key, 'x-real-ip')!==false) continue;
					if ($config['get_content_method']=='stream' &&
						stripos($key, 'connection')!==false) continue;
					if (stripos($key, 'connection')!==false) $row = 'keep-alive';

					if ($test) {
						if (stripos($key, 'Content-Length')!==false) continue;
					}

					$header = $key.': '.$row;

					if (stripos($key, 'user-agent') !== false) {
						$useragent_flag = true;
						$header .= ' /buran_seo_module';
					}

					$requestsheaders[] = $header;
				}
			}

			if ('curl' == $config['get_content_method']) {
				$curloptions = array(
					CURLOPT_URL            => $donor,
					CURLOPT_HTTPHEADER     => $requestsheaders,
					CURLOPT_HEADER         => true,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_CONNECTTIMEOUT => 10,
					CURLOPT_TIMEOUT        => 10,
				);
				if ($http == 'https://' && $config['https_test']) {
					$curloptions[CURLOPT_SSL_VERIFYHOST] = false;
					$curloptions[CURLOPT_SSL_VERIFYPEER] = true;
				}
				if ( ! $useragent_flag) {
					$curloptions[CURLOPT_USERAGENT] = ' /buran_seo_module';
				}

				$curl = curl_init();
				curl_setopt_array($curl, $curloptions);
				$template = $config['curl_auto_redirect']
					? curl_exec_followlocation($curl, $donor)
					: curl_exec($curl);
				$request_info = curl_getinfo($curl);
				list($headers, $template) = explode("\n\r", $template, 2);
				$http_code = $request_info['http_code'];
				if (curl_errno($curl)) {
					$break = true;
					bsm_tolog('[10]');
				} else {
					$headers = str_replace("\r",'',$headers);
					$headers = explode("\n", $headers);
				}
				curl_close($curl);

			} elseif ('stream' == $config['get_content_method']) {
				$options = array(
					'http' => array(
						'method' => 'GET',
						'header' => $requestsheaders,
					)
				);
				if( ! $useragent_flag) {
					$options['http']['user_agent'] = ' /buran_seo_module';
				}
				$context = stream_context_create($options);
				$stream  = fopen($donor, 'r', false, $context);
				if ($stream) {
					$template = stream_get_contents($stream);
					$headers  = stream_get_meta_data($stream);
					fclose($stream);
					$headers   = $headers['wrapper_data'];
					$http_code = 200;

				} else bsm_tolog('[11]');
			}

			if ($http_code != 200) {
				$break = true;
				bsm_tolog('[20]');
			}

			$template = trim($template);
			if ($break){}elseif($template) {
				if ($seotype == 'S') {
					header($protocol .' 200 OK');
				}

				if ($config['set_header'] && is_array($headers)) {
					foreach ($headers AS $key => $header) {
						if (stripos($header, 'Transfer-Encoding:') !== false)
							continue;
						if (stripos($header, 'Content-Length:') !== false)
							continue;
						header($header);
					}
				}

				$optfile   = $droot.$config['tx_path'].'/'.$seoalias.'.php';
				$optfile_d = filectime($optfile);
				if ( ! (include($optfile))) {
					bsm_tolog('[21]');
					print $template;
					exit();
				}

				$st = array(
					'title'       => $title,
					'description' => $description,
					'keywords'    => $keywords,
					's_title'     => $s_title,
					's_text'      => $s_text,
				);
				for ($pic=1; $pic<=99; $pic++) {
					if ( ! ${'pic'.$pic}) break;
					$st['pic'.$pic] = ${'pic'.$pic};
				}

				if ($test) {
					$st['s_text'] = $_POST['seomodule_s_text'];
				}

				$s_text_multi = is_array($st['s_text']) ? true : false;

				$encode = $config['in_charset'] === $config['out_charset']
					? false
					: true;
				$eti = '//TRANSLIT//IGNORE';

				foreach ($st AS $txtk => $txt) {
					if ( ! $encode) break;
					if (is_array($txt)) {
						foreach ($txt AS $key => $row) {
							if ($encode)
								$st[$txtk][$key] = iconv($config['in_charset'],
									$config['out_charset'].$eti, $row);
						}
					} else {
						if ($encode)
							$st[$txtk] = iconv($config['in_charset'],
								$config['out_charset'].$eti, $txt);
					}
				}

				if ($s_text_multi) {
					$s_text_single = '';
					foreach ($st['s_text'] AS $key => $row) {
						$foo = "<\!-- sssmodule_start_".($key+1)." -->(.*)<!-- sssmodule_finish_".($key+1)." -->";
						$template = preg_replace("/".$foo."/s", $row,
							$template, 1, $matches);
						if ( ! $matches) $s_text_single .= $row;
					}
					$st['s_text'] = $s_text_single;
				}

				if ($requesturi == $website[3]) {
					$txt = '<div class="sssmb_clr"></div><div class="sssmb_articles">';
					foreach ($seopage AS $key => $row) {
						$row = explode(':', $row);
						$row = end($row);
						if ($key == $website[3]) continue;
						$img = false;
						for ($k=1; $k<=10; $k++) {
							$img = $config['img_path'].'/'.$row.$k.'.jpg';
							if ( ! file_exists($droot.$img)) continue;
							if ($config['img_crop']) {
								$img = bsm_imgcrop($img, $config['img_width'],
									$config['img_height'], $droot);
							}
							break;
						}
						@include_once($droot.$config['tx_path'].'/'.$row.'.php');
						$txt .= '<div class="sssmba_itm">
							<div class="sssmba_img"><img src="'.$img.'" alt="" /></div>
							<div class="sssmba_inf">
								<div class="sssmba_tit"><a href="'.$key.'">'.$s_title.'</a></div>
								<div class="sssmba_txt">'.$description.'</div>
							</div>
						</div>';
					}
					$txt .= '</div>';
					$st['s_text'] .= $txt;
				}

				$content_finish_my = $content_finish['global'];
				if (isset($content_finish[$website_num])) {
					$content_finish_my = array_merge($content_finish_my, $content_finish[$website_num]);
				}
				if (is_array($content_finish_my)) {
					foreach ($content_finish_my AS $cf) {
						$cftype = substr($cf,0,1);
						if ($cftype !== '@' && $cftype !== '#') $cftype = '%';
						$cf    = substr($cf,1);
						$cf2   = preg_quote($cf,"/");
						$cf2   = str_replace("\n", '\n', $cf2);
						$cf2   = str_replace("\r", '', $cf2);
						$cf2   = str_replace("\t", '\t', $cf2);
						$cf_cc = preg_match("/".$cf2."/s", $template);
						if ($cf_cc===1) break;
					}
				}

				if ($cf_cc===1 && $st['s_text']) {
					$st['s_text'] .= '<br>';
					$st['s_text'] = str_replace('<p>[img]</p>', '[img]', $st['s_text']);
					$st['s_text'] = str_replace('<p>[col]</p>', '[col]', $st['s_text']);

					$seoimages = array();
					$imgs = glob($droot.$config['img_path'].'/'.$seoalias.'[0-9]*.{jpg,png}', GLOB_BRACE);
					if (is_array($imgs)) {
						foreach ($imgs AS $key => $row) {
							$crop = str_replace($droot, '', $row);
							if ($config['img_crop']) {
								$crop = bsm_imgcrop($crop, $config['img_width'],
									$config['img_height'], $droot);
							}
							$seoimages[] = array(
								'src' => $crop,
								'alt' => $st['pic'.($key+1)],
							);
						}
					}

					$seoimages_cc = count($seoimages);
					preg_match_all("/\[img\]/U", $st['s_text'], $imgtags);
					$imgtags = is_array($imgtags) ? count($imgtags[0]) : 0;
					if ( ! $imgtags) {
						$seoimages_cc_1 = floor($seoimages_cc/2);
						$seoimages_cc_2 = $seoimages_cc - $seoimages_cc_1;
						if ( ! $seoimages_cc_1 && $seoimages_cc_2) {
							$seoimages_cc_1 = 1;
							$seoimages_cc_2 = 0;
						}
					} else {
						$seoimages_cc_2 = $seoimages_cc - $imgtags;
					}
					if ($seoimages_cc_1 == 1) $imgtags = 1;
					if ($seoimages_cc_1) {
						if ($seoimages_cc_1 > 1) {
							$st['s_text'] = '<div class="sssmb_clr"></div></div>' .$st['s_text'];
						}
						for ($ii=1; $ii <= $seoimages_cc_1; $ii++) {
							$st['s_text'] = '[img]' ."\n" .$st['s_text'];
						}
						if ($seoimages_cc_1 > 1) {
							$st['s_text'] = '<div class="sssmb_imgs sssmb_imgs_1">' .$st['s_text'];
						}
					}
					if ($seoimages_cc_2) {
						$st['s_text'] .= '<div class="sssmb_imgs sssmb_imgs_2">';
						for ($ii=1; $ii <= $seoimages_cc_2; $ii++) {
							$st['s_text'] .= "\n". '[img]';
						}
						$st['s_text'] .= '<div class="sssmb_clr"></div></div>';
					}

					$ii = 0;
					do {
						$img = array_shift($seoimages);

						if ( ! $img['src']) $imgp = '';
						else {
							$ii++;
							$img['attr'] = $img['alt'].
								' ('.($ii==1 ? 'рисунок' : 'фото').')';
							$imgp = '
<div class="sssmb_img '.($ii==1?'sssmb_ir':'').' '.($ii<=$imgtags?'sssmb_img1':'sssmb_img2').'">
	<img itemprop="image" src="'.$img['src'].'" alt="'.$img['attr'].'" title="'.$img['attr'].'" />
	<div class="sssmb_bck">
		<div class="sssmb_ln"></div>
		<div class="sssmb_alt">'.$img['alt'].'</div>
	</div>
</div>';
						}

						$st['s_text'] = preg_replace("/\[img\]/U",
							$imgp, $st['s_text'], 1, $cc);
					} while ($cc);

					preg_match_all("/\[col\]/U", $st['s_text'], $coltags);
					$coltags = is_array($coltags) ? count($coltags[0]) : 0;
					$ii = 0;
					do {
						$ii = $ii == 3 ? 1 : $ii+1;
						if($ii == 1) {
							$colp = '<div class="sssmb_clr"></div>
							<div class="sssmb_col sssmb_col_l">';
						} elseif ($ii == 2) {
							$colp = '</div>
							<div class="sssmb_col sssmb_col_r">';
						} else {
							$colp = '</div>
							<div class="sssmb_clr"></div>';
						}
						$st['s_text'] = preg_replace("/\[col\]/U",
							$colp, $st['s_text'], 1, $cc);
					} while ($cc);

					preg_match_all("/\[tab(.*)\]/U", $st['s_text'], $tabtags);
					if (is_array($tabtags[0])) {
						$tabs_flag = true;
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

					$body = $config['styles'];

					if ($hideflag) {
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

					if ($tabs_flag) {
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

					$body .= '
<section id="sssmodulebox" class="sssmodulebox turbocontainer" '.($hideflag?'style="display:none;"':'').' itemscope itemtype="http://schema.org/Article">
	<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="'.$website[0].$requesturi.'" />
	<div class="sssmb_clr">&nbsp;</div>';

					if ($test) {
						$st['s_title'] = $_POST['seomodule_s_title'];
					}

					if ($seotype == 'A' || $seotype == 'W') {
						$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU",
							'<h2 ${1}>${2}</h2>', $template, -1, $hcc);
					} else {
						$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU",
							'<h1 ${1} itemprop="name">'.$st['s_title'].'</h1>',
							$template, -1, $hcc);
					}

					if ($hcc >= 2) {
						$template = preg_replace("/<h1(.*)>(.*)<\/h1>/isU",
							'', $template);
					}

					if ($seotype == 'A' || $seotype == 'W' || ! $hcc) {
						$body .= '<div class="sssmb_h1"><h1 itemprop="name">'.$st['s_title'].'</h1></div>';
					}

					list($foo_width, $foo_height, $foo_1, $foo_2)
						= getimagesize($droot.$website[8]);

					$body .= '
<div class="sssmb_cinf">
	<p itemprop="author">Автор: '.$website[7].'</p>
	<div itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
		<meta itemprop="name" content="'.$domain.'" />
		<div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
			<img itemprop="url" itemprop="image" src="'.$website[8].'" />
			<meta itemprop="width" content="'.$foo_width.'" />
			<meta itemprop="height" content="'.$foo_height.'" />
		</div>
	</div>
	<p>Дата публикации: <time itemprop="datePublished">'.date('Y-m-d',strtotime($website[6])).'</time></p>
	<p>Дата изменения: <time itemprop="dateModified">'.date('Y-m-d',$optfile_d).'</time></p>
	<noindex><p itemprop="headline">'.$st['title'].'</p></noindex>
</div>';

					$body .= '<div class="sssmb_stext">';

					$body .= $st['s_text'];

					$body .= '<div class="sssmb_clr">&nbsp;</div></div>';
					if($config['use_share'])
						$body .= '<div class="yasharebox">'.$config['share_code'].'</div>';
					$body .= '</section>';

					if ($seotype == 'A') {
						$template = preg_replace("/".$cf2."/s",
							($cftype=='#'?$cf:'').$body.($cftype=='%'?$cf:''),
							$template, 1);

					} elseif ($seotype == 'S' || $seotype == 'W') {
						$content_start_my = $content_start['global'];
						if (isset($content_start[$website_num])) {
							$content_start_my = array_merge($content_start_my, $content_start[$website_num]);
						}
						if (is_array($content_start_my)) {
							foreach ($content_start_my AS $cs) {
								$cstype = substr($cs,0,1);
								if ($cstype!=='@' && $cstype!=='#') $cstype = '%';
								$cs     = substr($cs,1);
								$cs2    = preg_quote($cs,"/");
								$cs2    = str_replace("\n", '\n', $cs2);
								$cs2    = str_replace("\r", '', $cs2);
								$cs2    = str_replace("\t", '\t', $cs2);
								$cs_cc  = preg_match("/".$cs2."/s", $template);
								if ($cs_cc===1) break;
							}
							if ($cs_cc===1) {
								$template = preg_replace("/".$cs2."(.*)".$cf2."/s", ($cstype=='#'?$cs:'').$body.($cftype=='%'?$cf:''), $template,1);
								
							} else bsm_tolog('[32]');
						} else bsm_tolog('[31]');
					}
				} else bsm_tolog('[30]');

				// meta
				if ($config['meta'] == 'replace_or_add' ||
					$config['meta'] == 'replace_if_exists' ||
					$config['meta'] == 'delete') {
					$meta_title = '<title>'.$st['title'].'</title>';
					$meta_description = '<meta name="description" content="'.$st['description'].'" />';
					$meta_keywords = '<meta name="keywords" content="'.$st['keywords'].'" />';
					if ($config['meta'] == 'replace_or_add')
						$meta_title .= "\n\t".$meta_description."\n\t".$meta_keywords."\n";
					if ($config['meta'] == 'delete' ||
						$config['meta'] == 'replace_or_add') {
						if ($config['meta'] == 'delete') $meta_title = '';
						$meta_description = '';
						$meta_keywords = '';
					}
					$template = preg_replace("/<meta [.]*name=('|\")description('|\")(.*)>/isU", $meta_description, $template, 2, $count1);
					$template = preg_replace("/<meta [.]*name=('|\")keywords('|\")(.*)>/isU", $meta_keywords, $template, 2, $count2);
					$template = preg_replace("/<title>(.*)<\/title>/isU", $meta_title, $template, 2, $count3);
					if ($count1 !== 1 || $count2 !== 1 || $count3 !== 1) {
						bsm_tolog('[50]');
					}
				}

				// base
				if ($config['base'] == 'replace_or_add' ||
					$config['base'] == 'replace_if_exists' ||
					$config['base'] == 'delete') {
					$base = '<base href="'.$website[0].'/" />';
					if ($config['base'] == 'replace_or_add' ||
						$config['base'] == 'delete') {
						$template = preg_replace("/<base (.*)>/iU", '', $template);
					}
					if ($config['base'] == 'replace_or_add') {
						$template = preg_replace("/<title>/i", $base."\n\t".'<title>', $template, 2, $count);
						if ($count !== 1) bsm_tolog('[51]');

					} elseif ($config['base'] == 'replace_if_exists') {
						$template = preg_replace("/<base (.*)>/iU", $base, $template, 2, $count);
						if ($count === 2) bsm_tolog('[52]');
					}
				}

				// canonical
				if ($config['canonical'] == 'replace_or_add' ||
					$config['canonical'] == 'replace_if_exists' ||
					$config['canonical'] == 'delete') {
					$canonical = '<link rel="canonical" href="'.$website[0].$requesturi.'" />';
					if ($config['canonical'] == 'replace_or_add' ||
						$config['canonical'] == 'delete') {
						$template = preg_replace("/<link (.*)rel=('|\")canonical('|\")(.*)>/iU", '', $template);
					}
					if ($config['canonical'] == 'replace_or_add') {
						$template = preg_replace("/<title>/i", $canonical."\n\t".'<title>', $template, 2, $count);
						if ($count !== 1) bsm_tolog('[53]');

					} elseif ($config['canonical'] == 'replace_if_exists') {
						$template = preg_replace("/<link (.*)rel=('|\")canonical('|\")(.*)>/iU", $canonical, $template, 2, $count);
						if ($count === 2) bsm_tolog('[54]');
					}
				}

				if ($config['city_replace']) {
					$template = preg_replace("/\[hide\](.*?)\[hide\]/U", '', $template);
					foreach ($declension AS $deklkey => $decl) {
						$template = preg_replace("/\[city_{$deklkey}\](.*?)\[city\]/U", $decl, $template);
					}
				}

				print $template;
				exit();

			} else bsm_tolog('[41]');
		} else bsm_tolog('[40]');
	}
}

if ('seoModule.php' == basename($pageurl)) {
	if ('list' == $_GET['a']) {
		header('Content-type: text/html; charset=utf-8');

		$green = '#089c29';
		$red   = '#d41717';

		print bsm_server().'<br><br>';

		$files = glob($droot.$config['tx_path'].'/'.'*.php');
		print '<div>Кол-во файлов: '.count($files).'</div><br>';
		if (is_array($files) && count($files)) {
			foreach ($files AS $key => $file) {
				$filename = basename($file);
				print '<div>Файл '.($key+1).' | '.$filename;
				$target = false;
				$s_text = array();
				include_once($file);
				$seotype = ! trim($target) ? 'S' : 'A';
				if ($seotype == 'S') {
					$target = '/'.substr($filename,0,-4) . $config['s_page_suffix'];
				}

				$pagesurl[$seotype] .= '<div><a style="text-decoration:none;" target="_blank" href="'.$target.'">'.$target.'</a></div>';
				print '</div>';

				if (strlen($target) > $max) {
					$max= strlen($target);
				}
				$printarray[$seotype] .= "\t\t'{$target}'[".strlen($target)."]=> '{$seotype}:".substr($filename,0,-4)."',\n\n";
			}
			print '<div style="font-weight:bold;color:#47ad00;">OK</div>';
			print '<br>';

			$printarray = $printarray['A'] . $printarray['S'];
			preg_match_all("/\[([0-9]+)\]=>/U", $printarray, $matches);
			if (is_array($matches[1])) {
				foreach ($matches[1] AS $row) {
					$tmp = $max - $row + 2;
					$printarray = str_replace('['.$row.']=>', str_repeat(' ',$tmp).'=>', $printarray);
				}
			}
		}

		$flag = version_compare(PHP_VERSION, '5.4.0', '<') ? false : true;
		print '<div>Версия PHP: <span style="color:'.($flag ? $green : $red).'">'.PHP_VERSION.'</span></div>';

		$flag = $http.$www.$domain === $website[0] ? true : false;
		print '<div>Домен: <span style="color:'.($flag ? $green : $red).'">'.$http.$www.$domain.' == '.$website[0].'</span></div>';

		$flag = $website[4] ? true : false;
		print '<div>ID в бункере: <span style="color:'.($flag ? $green : $red).'">'.$website[4].'</span></div>';

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

		print '<br><br>';
		print $pagesurl['A'].$pagesurl['S'];
		print '<br>';
		print "<pre>\tarray(\n".$printarray."\t);</pre>";

		print '<br><br><br>';
	}

	if ('info' == $_GET['a']) {
		$seopage = $seopages['global'];
		if (isset($seopages[$website_num])) {
			$seopage = array_merge($seopage, $seopages[$website_num]);
		}

		$hash_1 = md5_file($droot.'/_buran/seoModule.php');
		$hash_2 = md5_file($droot.'/_buran/seoModule_config.php');

		print '[seomoduleversion_'.$seomoduleversion.']'."\n";
		print '[seohash_'.$hash_1.']'."\n";
		print '[modulehash_'.$hash_1.']'."\n";
		print '[confighash_'.$hash_2.']'."\n";
		print '[droot_'.$droot.']'."\n";
		print '[website_'.$website[0].']'."\n";
		print '[mainpage_'.$website[1].']'."\n";
		print '[donor_'.$website[2].']'."\n";
		print '[articlespage_'.$website[3].']'."\n";
		print '[bunkerid_'.$website[4].']'."\n";
		print '[city_'.$website[5].']'."\n";
		print '[date_'.$website[6].']'."\n";
		print '[compname_'.$website[7].']'."\n";
		print '[logo_'.$website[8].']'."\n";
		print '[pages_]'."\n";
		foreach ($seopage AS $key => $row) {
			$alias = explode(':', $row);
			$alias = end($alias);
			$hash  = md5_file($droot.$config['tx_path'].'/'.$alias.'.php');
			print $key.' => '.$row.' => '.$hash."\n";
		}
		print '[_pages]'."\n";
		print '[config_]'."\n";
		foreach ($config AS $key => $row) {
			if (strpos($row,"\n") !== false) continue;
			print $key.'|'.$row."\n";
		}
		print '[_config]'."\n";

		print '[errors_]'."\n";
		$fh = fopen('seoModule_errors','r');
		if ($fh) {
			$content = '';
			while ( ! feof($fh)) $content .= fread($fh, 1024*8);
			fclose($fh);
			print $content;
		}
		print '[_errors]'."\n";
	}

	if ('file' == $_GET['a']) {
		$file = urldecode($_GET['f']);
		$file = str_replace(array('/','..'), '', $file);
		$file = $droot.$config['tx_path'].'/'.$file.'.php';
		if ( ! file_exists($file)) {
			exit();
		}
		$fh = fopen($file,'rb');
		if ( ! $fh) exit();
		while ( ! feof($fh)) $content .= fread($fh, 1024*8);
		fclose($fh);
		header('Content-Type: text/plain');
		print $content;
	}

	if ('validation' == $_GET['a']) {
		$uri = 'http://bunker-yug.ru/__buran/seoModule_validation.php?ws='.
			urlencode($website[0]).'&idc='.$website[4];

		if ($config['get_content_method'] == 'curl') {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL,            $uri);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($curl);
			curl_close($curl);

		} elseif ($config['get_content_method'] == 'stream') {
			$options = array(
				'http' => array(
					'method' => 'GET'
				)
			);
			$context = stream_context_create($options);
			$stream = fopen($uri, 'r', false, $context);
			if ($stream) {
				$response = stream_get_contents($stream);
				fclose($stream);
			}
		}
		$response = trim($response);
		$bsm_server = bsm_server();
		if ($response=='no' && file_exists($droot.'/_buran/'.$bsm_server)) {
			unlink($droot.'/_buran/'.$bsm_server);
		}
	}

	if ('update' == $_GET['a']) {
		if ( ! bsm_auth($domain, $_GET['w'])) exit();
		$url = 'http://bunker-yug.ru/__buran/update/seoModule';
		$curloptions = array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
		);
		$curl = curl_init();
		curl_setopt_array($curl, $curloptions);
		$code = curl_exec($curl);
		if ( ! $code ||
			strpos($code, "<?php\n/**\n * seoModule") !== 0)
			exit();
		$fh = fopen(__FILE__, 'wb');
		if ( ! $fh) exit();
		$res = fwrite($fh, $code);
		if ( ! $res) exit();
		fclose($fh);
		exit('ok');
	}
}

/*
 *
 *
 *
 *
 */
// ------------------------------------------------------------------
function bsm_auth($h, $w)
{
	$url = 'http://bunker-yug.ru/__buran/secret_key.php';
	$url .= '?h='.$h;
	$url .= '&w='.$w;
	$curloptions = array(
		CURLOPT_URL            => $url,
		CURLOPT_RETURNTRANSFER => true,
	);
	$curl = curl_init();
	curl_setopt_array($curl, $curloptions);
	$ww = curl_exec($curl);
	if ($ww && $_GET['w'] && $_GET['w'] == $ww) {
		return true;
	}
	return false;
}

function bsm_tolog($text, $type='errors')
{
	global $droot;
	global $logsfile;
	global $requesturi;
	$fh = $logsfile[$type];
	if ( ! $fh) {
		$file = $droot.'/_buran/seoModule_'.$type;
		if (filesize($file) >= 1024*64) {
			$fh = fopen($file, 'c+b');
			if ($fh) {
				fseek($fh, -1024*8, SEEK_END);
				while ($line = fgets($fh)) {
					$data .= $line;
				}
				$data .= time() ."\t";
				$data .= date('Y-m-d-H-i-s') ."\t";
				$data .= '(truncate)' ."\n";
				ftruncate($fh, 0);
				rewind($fh);
				fwrite($fh, $data."\n");
				fclose($fh);
			}
		}
		$fh = fopen($file, 'ab');
		if ( ! $fh) return false;
		$logsfile[$type] = $fh;
	}
	$data  = time() ."\t";
	$data .= date('Y-m-d-H-i-s') ."\t";
	$data .= $text ."\t";
	$data .= $requesturi;
	fwrite($fh, $data."\n");
}

function bsm_server()
{
	return md5(__FILE__);
}

function curl_exec_followlocation(&$curl, &$uri)
{
	// v2.1
	// Date 16.02.2017
	// -----------------------------------------
	if(preg_match("/^(http(s){0,1}:\/\/[a-z0-9\.-]+)(.*)$/i", $uri, $matches)!==1) return;
	$website= $matches[1];
	do{
		// if($referer) curl_setopt($curl, CURLOPT_REFERER, $referer);
		curl_setopt($curl, CURLOPT_URL, $uri);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		$response= curl_exec($curl);
		if(curl_errno($curl)) return false;
		$headers= str_replace("\r",'',$response);
		$headers= explode("\n\n",$headers,2);
		if(preg_match("/^location: (.*)$/im", $headers[0], $matches)===1)
		{
			$location= true;
			$referer= $uri;
			$uri= trim($matches[1]);
			if(preg_match("/^http(s){0,1}:\/\/[a-z0-9\.-]+/i", $uri, $matches)!==1)
				$uri= $website.(substr($uri,0,1)!='/'?'/':'').$uri;
		}else $location= false;
		if($location)
		{
			if($redirects_list[$uri]<=1) $redirects_list[$uri]++;
				else $location= false;
		}
	}while($location);
	return $response;
}

function bsm_imgcrop($img, $w, $h, $droot, $website='', $baseurl='/')
{
	$w= intval($w);
	$h= intval($h);
	$refresh= (empty($r) ? false : true);
	$base= ltrim($baseurl, DIRECTORY_SEPARATOR);
	$img= trim(urldecode($img));
	$slashflag= (strpos($img, DIRECTORY_SEPARATOR)===0 ? true : false);
	if($slashflag) $img= ltrim($img, DIRECTORY_SEPARATOR);
	$baseflag= ($base && strpos($img, $base)===0 ? true : false);
	if($baseflag) $img= ltrim($img, $base);
	$root= $droot.(substr($droot,-1,1)!='/'?'/':'');
	if( ! file_exists($root.$img) || ! is_file($root.$img)) return false;
	$imgrassh= substr($img, strrpos($img,'.'));
	$newimg= '_th'.md5($img . $w . $h) . $imgrassh;
	$newimg_dir= dirname($img) .DIRECTORY_SEPARATOR.'.th'.DIRECTORY_SEPARATOR;
	if( ! file_exists($root.$newimg_dir)) mkdir($root.$newimg_dir, 0777);
	$newimg_path= $root.$newimg_dir.$newimg;
	$newimg_path_return= ($fullpath ? $website : ($slashflag?DIRECTORY_SEPARATOR:'').($baseflag?$base:'')) .$newimg_dir .$newimg;
	if( ! file_exists($newimg_path) || filectime($root.$img) >filectime($newimg_path)) $refresh= true;
	if(filesize($root.$img) > 1024*1024*10) return $img;
	//--------------------------------------------------------------------------------------
	if($refresh)
	{
		$img1_info= getimagesize($root.$img);
		$srcW= $img1_info[0];
		$srcH= $img1_info[1];
		if( ! $srcH) return false;
		$ot= $srcW /$srcH;
		$dstW= ($w >0 ? $w : $srcW);
		$dstH= ($h >0 ? $h : $srcH);
		if(($srcW>$w && $w>0) || ($srcH>$h && $h>0))
		{
			$dstH= round($dstW /$ot);
			if($dstH>$h && $h>0)
			{
				$dstH= $h;
				$dstW= round($dstH *$ot);
			}
		}else{
			$dstW= $srcW;
			$dstH= $srcH;
		}
		$crW= $dstW;
		$crH= $dstH;
		//----------------
		if($img1_info[2] ==1) $img1= imagecreatefromgif($root.$img);
		elseif($img1_info[2] ==2) $img1= imagecreatefromjpeg($root.$img);
		elseif($img1_info[2] ==6) $img1= imagecreatefromwbmp($root.$img);
		elseif($img1_info[2] ==3){ $img1= imagecreatefrompng($root.$img); $png= true; }
		$img2= ImageCreateTrueColor($crW, $crH);
		if($png)
		{
			imagealphablending($img2, true);
			imagesavealpha($img2, true);
			$col= imagecolorallocatealpha($img2, 255,255,255,127);
		}else{
			$col= imagecolorallocate($img2, 255,255,255);
		}
		imagefill($img2, 0,0, $col);
		imagecopyresampled($img2, $img1, 0,0,0,0, $dstW,$dstH, $srcW,$srcH);
		//------
		if($png) imagepng($img2, $newimg_path);
		elseif($img1_info[2] == 1) imagegif($img2, $newimg_path, 80);
		elseif($img1_info[2] == 2) imagejpeg($img2, $newimg_path, 80);
		elseif($img1_info[2] == 6) imagewbmp($img2, $newimg_path);
			
		chmod($newimg_path, 0755);
		imagedestroy($img1);
		imagedestroy($img2);
	}
	return $newimg_path_return;
}


/**
 * https://github.com/ralouphie/getallheaders
 * 23.12.2016
 * Get all HTTP header key/values as an associative array for the current request.
 * @return string[string] The HTTP header key/value pairs.
 */
function bsm_getallheaders()
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
//-----------------------------------------------
//-----------------------------------------------
//-----------------------------------------------
//-----------------------------------------------
//-----------------
