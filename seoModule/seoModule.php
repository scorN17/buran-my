<?php
/**
 * seoModule
 * @version 2.03
 * 10.03.2017
 * DELTA
 * sergey.it@delta-ltd.ru
 */
$seomoduleversion= '2.03';

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'off');

include_once('seoModule_config.php');

$http= ($_SERVER['SERVER_PORT']=='443' || $_SERVER['HTTP_PORT']=='443' || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=='on') ? 'https://' : 'http://');
$domain= (isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME']);
$www= (strpos($domain,'www.')===0?'www.':'');
if($www=='www.') $domain= substr($domain,4);
$scriptname= (isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:$_SERVER['PHP_SELF']);
$requesturi= $_SERVER['REQUEST_URI'];
$pageurl= parse_url($requesturi, PHP_URL_PATH);
$querystring= $_SERVER['QUERY_STRING'];
$droot= dirname(dirname(__FILE__));
// ------------------------------------------------------------------
$website_num= 1;
foreach($websites AS $key => $ws) if(strpos($ws[0].'/', '/'.$domain.'/')!==false) $website_num= $key;
if($website_num)
{
	$config= $configs['global'];
	if(isset($configs[$website_num])) $config= array_merge($config, $configs[$website_num]);
	$config['toencoding']= strtolower($config['toencoding']);

	$website= $websites[$website_num];

	$redirect= $redirects['global'];
	if(isset($redirects[$website_num])) $redirect= array_merge($redirect, $redirects[$website_num]);

	if($http.$www.$domain !== $website[0]) $redirect_to= $requesturi;
	if($redirect[$requesturi]) $redirect_to= $redirect[$requesturi];

	if($redirect_to)
	{
		header('Location: '.$website[0].$redirect_to, true, 301);
		exit();
	}
}
// ------------------------------------------------------------------
if( ! file_exists($droot.'/_buran/'.bsm_server()))
{
	$logsfile['errors']= fopen($droot.'/_buran/seoModule_errors', 'a');
	bsm_tolog('[error_26]','errors');
}elseif(
	$website_num &&
	basename($pageurl)!='seoModule.php' &&
	(
		$config['module_enabled']===true ||
		$_SERVER['REMOTE_ADDR']===$config['module_enabled']
	) &&
	strpos($config['requets_methods'], '/'.$_SERVER['REQUEST_METHOD'].'/')!==false &&
	strpos($_SERVER['HTTP_USER_AGENT'], 'buran_seo_module')===false &&
	file_exists($droot.'/_buran/'.bsm_server())
){
	$seopage= $seopages['global'];
	if(isset($seopages[$website_num])) $seopage= array_merge($seopage, $seopages[$website_num]);

	if(substr($pageurl,(-1)*strlen($config['s_page_suffix']))==$config['s_page_suffix']) $pageurl_without_suffix= substr($pageurl,0,(-1)*strlen($config['s_page_suffix']));

	while(preg_match("/((&|^)(_openstat|utm_.*)=.*)(&|$)/U", $querystring, $matches)===1)
	{
		$querystring= preg_replace("/((&|^)(_openstat|utm_.*)=.*)(&|$)/U", '${4}', $querystring);
		$bez_utm= true;
	}
	if($bez_utm)
	{
		if(strpos($querystring,'&')===0) $querystring= substr($querystring,1);
		$requesturi= $pageurl.($querystring ? '?'.$querystring : '');
	}

	if(isset($seopage[$requesturi])) $seoalias= trim($seopage[$requesturi]);
	elseif(substr($seopage[$pageurl_without_suffix],0,2)=='S:') $seoalias= trim($seopage[$pageurl_without_suffix]);
	if($seoalias)
	{	
		// $logsfile['logs']= fopen($droot.'/_buran/seoModule_logs', 'a');
		$logsfile['errors']= fopen($droot.'/_buran/seoModule_errors', 'a');
		if( ! file_exists($droot.'/_buran/seoModule_hash') || filectime($droot.'/_buran/seoModule_hash')<time()-(60*60*24))
		{
			$logsfile['hash']= fopen($droot.'/_buran/seoModule_hash', 'a');
			$seoHash= bsm_seohash($droot, $config);
			bsm_tolog('['.date('Y-m-d-H-i-s').'_'.$seoHash.']','hash');
		}

		$seoalias= explode(':',$seoalias,2);
		$seotype= ($seoalias[0]=='A'?'A':($seoalias[0]=='W'?'W':'S'));
		$seoalias= $seoalias[1];
		$hideflag= ($config['hide_opt']===true?true :($config['hide_opt']===false?false :(strpos($config['hide_opt'],$seotype)!==false?true :false)));

		if(file_exists($droot.$config['tx_path'].'/'.$seoalias.'.php'))
		{
			@include_once($droot.$config['tx_path'].'/'.$seoalias.'.php');

			$encoding= $config['toencoding'];
			if($config['checkcharsetmethod']=='mb_detect_encoding' && function_exists('mb_detect_encoding')){
				$encoding= mb_detect_encoding($s_text);
			}else bsm_tolog('[error_12]','errors');
			$encoding= strtolower($encoding);
			$encode= ($encoding===$config['toencoding']?false:true);
			if($encode)
			{
				$title= iconv($encoding, $config['toencoding'], $title);
				$description= iconv($encoding, $config['toencoding'], $description);
				$keywords= iconv($encoding, $config['toencoding'], $keywords);
				$s_title= iconv($encoding, $config['toencoding'], $s_title);
				$s_text= iconv($encoding, $config['toencoding'], $s_text);
			}

			$donor= $website[0];
			$donor .= ($seotype=='S' ? $website[2] : $requesturi);

			$useragent_flag= false;
			$requestsheaders= array();
			$getallheaders= (function_exists('getallheaders') ? getallheaders() : bsm_getallheaders());
			if(is_array($getallheaders) && count($getallheaders))
			{
				foreach($getallheaders AS $key=>$row)
				{
					if(stripos($key, 'x-forwarded')!==false) continue;
					if(stripos($key, 'accept-encoding')!==false) continue;
					if(stripos($key, 'x-real-ip')!==false) continue;
					if(stripos($key, 'x-1gb-client-ip')!==false) continue;
					if($config['get_content_method']=='stream' && stripos($key, 'connection')!==false) continue;
					if(stripos($key, 'connection')!==false) $row= 'keep-alive';
					$header= $key.': '.$row;
					if(stripos($key, 'user-agent')!==false)
					{
						$useragent_flag= true;
						$header .= ' /buran_seo_module';
					}
					$requestsheaders[]= $header;
				}
			}

			if($config['get_content_method']=='curl')
			{
				$curloptions= array(
					CURLOPT_URL               => $donor,
					CURLOPT_HTTPHEADER        => $requestsheaders,
					CURLOPT_HEADER            => true,
					CURLOPT_RETURNTRANSFER    => true,
					CURLOPT_CONNECTTIMEOUT    => 10,
					CURLOPT_TIMEOUT           => 10,
				);
				if($http=='https://' && $config['https_test'])
				{
					$curloptions[CURLOPT_SSL_VERIFYHOST]= false;
					$curloptions[CURLOPT_SSL_VERIFYPEER]= true;
				}
				if( ! $useragent_flag) $curloptions[CURLOPT_USERAGENT]= ' /buran_seo_module';

				$curl= curl_init();
				curl_setopt_array($curl, $curloptions);
				if($config['curl_auto_redirect']) $template= curl_exec_followlocation($curl, $donor);
					else $template= curl_exec($curl);
				$request_info= curl_getinfo($curl);
				list($headers, $template)= explode("\n\r", $template, 2);
				$http_code= $request_info['http_code'];
				if(curl_errno($curl))
				{
					$break= true;
					bsm_tolog('[error_22]-'.$requesturi,'errors');
				}else{
					$headers= str_replace("\r",'',$headers);
					$headers= explode("\n", $headers);
				}
				curl_close($curl);

			}elseif($config['get_content_method']=='stream'){
				$options= array(
					'http' => array(
						'method'        => 'GET',
						'header'        => $requestsheaders,
					)
				);
				if( ! $useragent_flag) $options['http']['user_agent']= ' /buran_seo_module';
				$context= stream_context_create($options);
				$stream= fopen($donor, 'r', false, $context);
				if($stream)
				{
					$template= stream_get_contents($stream);
					$headers= stream_get_meta_data($stream);
					fclose($stream);
					$headers= $headers['wrapper_data'];
					$http_code= 200;

				}else bsm_tolog('[error_23]-'.$requesturi,'errors');
			}else{
				$break= true;
				bsm_tolog('[error_02]','errors');
			}

			if($http_code!=200)
			{
				$break= true;
				bsm_tolog('[error_24]-'.$requesturi,'errors');
			}

			$template= trim($template);
			if($break){}elseif($template)
			{
				if(is_array($headers) && count($headers))
				{
					foreach($headers AS $key => $header)
					{
						if(stripos($header, 'transfer-encoding')!==false) continue; //Transfer-Encoding: chunked
						header($header);
					}
				}
				if($seotype=='S')
				{
					header('Status: 200 OK');
					header('HTTP/1.1 200 OK');
				}

				$seoimages= array();
				$imgs= glob($droot.$config['img_path'].'/'.$seoalias.'[0-9].{jpg,png}', GLOB_BRACE);
				if(is_array($imgs) && count($imgs))
				{
					foreach($imgs AS $key => $row)
					{
						$crop= bsm_imgcrop(str_replace($droot, '', $row), $config['img_width'], $config['img_height'], $droot);
						$alt= ${'pic'.($key+1)};
						if($encode) $alt= iconv($encoding, $config['toencoding'], $alt);
						$seoimages[]= array(
							'src' => $crop,
							'alt' => $alt,
						);
					}
				}
				$seoimages_cc= count($seoimages);
				$seoimages_cc_half= false;
				if($seoimages_cc>2) $seoimages_cc_half= ceil($seoimages_cc/2);

				$body= $config['styles'];
				if($hideflag)
				{
					$body .= '<script>
						function chpoktext(){
							obj= document.getElementById("sssmodulebox");
							if(obj.style.display=="none") obj.style.display= "";
							else obj.style.display= "none";
						}
					</script>
					<article onclick="chpoktext()">&rarr;</article>';
				}
				$body .= '<div id="sssmodulebox" class="sssmodulebox" '.($hideflag?'style="display:none;"':'').'><div style="clear:both;font-size:0;line-height:0;">&nbsp;</div>
					<div class="sssmb_h1"><h1>'.$s_title.'</h1></div>';
				$body .= '<div class="sssmb_stext">';
				if($seoimages_cc_half) $body .= '<div style="margin-bottom:10px;text-align:center;">';
				for($i=0; $i<($seoimages_cc_half?$seoimages_cc_half:1); $i++)
					$body .= '<img src="'.$seoimages[$i]['src'].'" alt="'.$seoimages[$i]['alt'].'"
						style="'.($seoimages_cc_half?'padding:0 10px;':'float:right;margin:0 0 20px 30px;padding:0;width:auto;height:auto;').'" />';
				if($seoimages_cc_half) $body .= '</div>';

				$body .= $s_text;

				if($seoimages_cc_half) $body .= '<div style="margin-bottom:10px;text-align:center;">';
				for($i=($seoimages_cc_half?$seoimages_cc_half:1); $i<$seoimages_cc; $i++)
					$body .= '<img src="'.$seoimages[$i]['src'].'" alt="'.$seoimages[$i]['alt'].'"
						style="'.($seoimages_cc_half?'padding:0 10px;':'margin:0;padding:0;width:auto;height:auto;').'" />';
				if($seoimages_cc_half) $body .= '</div>';
				$body .= '</div>';
				if($config['use_share']) $body .= '<div class="yasharebox">'.$config['share_code'].'</div></div>';

				$content_finish_my= $content_finish['global'];
				if(isset($content_finish[$website_num])) $content_finish_my= array_merge($content_finish_my, $content_finish[$website_num]);
				if(is_array($content_finish_my) && count($content_finish_my))
				{
					foreach($content_finish_my AS $cf)
					{
						$cftype= substr($cf,0,1);
						if($cftype!=='@' && $cftype!=='#' && $cftype!=='%') $cftype= '%';
						$cf= substr($cf,1);
						$cf2= preg_quote($cf,"/");
						$cf2= str_replace("\n", '\n', $cf2);
						$cf2= str_replace("\r", '', $cf2);
						$cf2= str_replace("\t", '\t', $cf2);
						$cf_cc= preg_match("/".$cf2."/s", $template);
						if($cf_cc===1) break;
					}
				}else bsm_tolog('[error_04]','errors');
				if($cf_cc!==1) bsm_tolog('[error_06]-'.$requesturi,'errors');

				if($seotype=='A')
				{
					$template= preg_replace("/<h1(.*)>(.*)<\/h1>/isU", '<h2 ${1}>${2}</h2>', $template);
					if(preg_last_error()) bsm_tolog('[error_09]-'.$requesturi,'errors');
					if($cf_cc===1)
					{
						$template= preg_replace("/".$cf2."/s", ($cftype=='#'?$cf:'').$body.($cftype=='%'?$cf:''), $template,1);
						if(preg_last_error()) bsm_tolog('[error_10]-'.$requesturi,'errors');
					}

				}elseif($seotype=='S' || $seotype=='W'){
					if($cf_cc===1)
					{
						$content_start_my= $content_start['global'];
						if(isset($content_start[$website_num])) $content_start_my= array_merge($content_start_my, $content_start[$website_num]);
						if(is_array($content_start_my) && count($content_start_my))
						{
							foreach($content_start_my AS $cs)
							{
								$cstype= substr($cs,0,1);
								if($cstype!=='@' && $cstype!=='#' && $cstype!=='%') $cstype= '%';
								$cs= substr($cs,1);
								$cs2= preg_quote($cs,"/");
								$cs2= str_replace("\n", '\n', $cs2);
								$cs2= str_replace("\r", '', $cs2);
								$cs2= str_replace("\t", '\t', $cs2);
								$cs_cc= preg_match("/".$cs2."/s", $template);
								if($cs_cc===1)
								{
									$template= preg_replace("/".$cs2."(.*)".$cf2."/s", ($cstype=='#'?$cs:'').$body.($cftype=='%'?$cf:''), $template,1);
									if(preg_last_error()) bsm_tolog('[error_11]-'.$requesturi,'errors');
									break;
								}
							}
							if($cs_cc!==1) bsm_tolog('[error_08]-'.$requesturi,'errors');
						}else bsm_tolog('[error_07]','errors');
					}
				}else bsm_tolog('[error_05]-'.$requesturi,'errors');

				// meta
				if($config['meta']=='replace_or_add' || $config['meta']=='replace_if_exists' || $config['meta']=='delete')
				{
					$meta_title       = '<title>'.$title.'</title>';
					$meta_description = '<meta name="description" content="'.$description.'" />';
					$meta_keywords    = '<meta name="keywords" content="'.$keywords.'" />';
					if($config['meta']=='replace_or_add') $meta_title .= "\n\t".$meta_description."\n\t".$meta_keywords."\n";
					if($config['meta']=='delete' || $config['meta']=='replace_or_add')
					{
						if($config['meta']=='delete') $meta_title= '';
						$meta_description= '';
						$meta_keywords= '';
					}
					$template= preg_replace("/<meta [.]*name=('|\")description('|\")(.*)>/isU", $meta_description, $template);
					if(preg_last_error()) bsm_tolog('[error_13]-'.$requesturi,'errors');
					$template= preg_replace("/<meta [.]*name=('|\")keywords('|\")(.*)>/isU", $meta_keywords, $template);
					if(preg_last_error()) bsm_tolog('[error_14]-'.$requesturi,'errors');
					$template= preg_replace("/<title>(.*)<\/title>/isU", $meta_title, $template);
					if(preg_last_error()) bsm_tolog('[error_15]-'.$requesturi,'errors');
				}

				// base
				if($config['base']=='replace_or_add' || $config['base']=='replace_if_exists' || $config['base']=='delete')
				{
					$base= '<base href="'.$website[0].'/" />';
					if($config['base']=='replace_or_add' || $config['base']=='delete')
					{
						$template= preg_replace("/<base (.*)>/iU", '', $template,1);
						if(preg_last_error()) bsm_tolog('[error_16]-'.$requesturi,'errors');
					}
					if($config['base']=='replace_or_add')
					{
						$template= preg_replace("/<title>/i", $base."\n\t".'<title>', $template,1);
						if(preg_last_error()) bsm_tolog('[error_17]-'.$requesturi,'errors');
					}
					if($config['base']=='replace_if_exists')
					{
						$template= preg_replace("/<base (.*)>/iU", $base, $template,1);
						if(preg_last_error()) bsm_tolog('[error_18]-'.$requesturi,'errors');
					}
				}

				// canonical
				if($config['canonical']=='replace_or_add' || $config['canonical']=='replace_if_exists' || $config['canonical']=='delete')
				{
					$canonical= '<link rel="canonical" href="'.$website[0].$requesturi.'" />';
					if($config['canonical']=='replace_or_add' || $config['canonical']=='delete')
					{
						$template= preg_replace("/<link (.*)rel=('|\")canonical('|\")(.*)>/iU", '', $template,1);
						if(preg_last_error()) bsm_tolog('[error_19]-'.$requesturi,'errors');
					}
					if($config['canonical']=='replace_or_add')
					{
						$template= preg_replace("/<title>/i", $canonical."\n\t".'<title>', $template,1);
						if(preg_last_error()) bsm_tolog('[error_20]-'.$requesturi,'errors');
					}
					if($config['canonical']=='replace_if_exists')
					{
						$template= preg_replace("/<link (.*)rel=('|\")canonical('|\")(.*)>/iU", $canonical, $template,1);
						if(preg_last_error()) bsm_tolog('[error_21]-'.$requesturi,'errors');
					}
				}

				print $template;
				exit();

			}else bsm_tolog('[error_03]-'.$requesturi,'errors');
		}else bsm_tolog('[error_01]-'.$requesturi,'errors');
	}
}

if(basename($pageurl)=='seoModule.php')
{
	if($_GET['a']=='list')
	{
		header('Content-type: text/html; charset=utf-8');

		print bsm_server().'<br /><br />';

		$files= glob($droot.$config['tx_path'].'/'.'*.php');
		print '<div>Кол-во файлов: '.count($files).'</div><br />';
		if(is_array($files) && count($files))
		{
			foreach($files AS $key => $file)
			{
				$filename= basename($file);
				print '<div>Файл '.($key+1).' | '.$filename;
				$target= false;
				include_once($file);
				if( ! trim($target))
				{
					$seotype= 'S';
					$target= '/'.substr($filename,0,-4);
				}else{
					$seotype= 'A';
				}
				if($seotype=='A') $pagesurl_A .= '<div><a target="_blank" href="'.$target.'">'.$target.'</a></div>';
					else $pagesurl_S .= '<div><a target="_blank" href="'.$website[0].$target.$config['s_page_suffix'].'">'.$target.$config['s_page_suffix'].'</a></div>';
				$tmp= 50-strlen($target);
				if($seotype=='A') $printarray_A .= "\t\t'{$target}' ".($tmp>0?str_repeat(' ',$tmp):'')." => '{$seotype}:".substr($filename,0,-4)."',\r";
					else $printarray_S .= "\t\t'{$target}' ".($tmp>0?str_repeat(' ',$tmp):'')." => '{$seotype}:".substr($filename,0,-4)."',\r";
				print '</div>';
			}
			print '<div style="font-weight:bold;color:#47ad00;">OK</div>';
			print '<br />';
			print $pagesurl_A.$pagesurl_S;
			print '<br />';
			print "<pre>\t=array(\n".$printarray_A.$printarray_S."\t);</pre>";
		}
	}

	if($_GET['a']=='info')
	{
		$seopage= $seopages['global'];
		if(isset($seopages[$website_num])) $seopage= array_merge($seopage, $seopages[$website_num]);

		print '[seomoduleversion_'.$seomoduleversion.']'."\n";
		print '[seohash_'.bsm_seohash($droot, $config).']'."\n";
		print '[droot_'.$droot.']'."\n";
		print '[website_'.$website[0].']'."\n";
		print '[mainpage_'.$website[1].']'."\n";
		print '[donor_'.$website[2].']'."\n";
		print '[articlespage_'.$website[3].']'."\n";
		print '[pages_]'."\n";
		foreach($seopage AS $key => $row)
		{
			print $key .(substr($row,0,2)=='S:'?$config['s_page_suffix']:'') .' => '.$row ."\n";
		}
		print '[_pages]'."\n";
		print '[config_]'."\n";
		foreach($config AS $key => $row)
		{
			if(strpos($row,"\n")!==false) continue;
			print $key.'|'.$row."\n";
		}
		print '[_config]'."\n";

		print '[seohash_s_]'."\n";
		$fo= fopen('seoModule_hash','r');
		if($fo)
		{
			$content= '';
			while( ! feof($fo)) $content .= fread($fo, 1024*8);
			fclose($fo);
			print $content;
		}
		print '[_seohash_s]'."\n";

		print '[errors_]'."\n";
		$fo= fopen('seoModule_errors','r');
		if($fo)
		{
			$content= '';
			while( ! feof($fo)) $content .= fread($fo, 1024*8);
			fclose($fo);
			print $content;
		}
		print '[_errors]'."\n";
	}

	if($_GET['a']=='validation')
	{
		$uri= 'http://bunker-yug.ru/__buran/seoModule_validation.php?ws='.urlencode($website[0]).'&idc='.$website[4];

		if($config['get_content_method']=='curl')
		{
			$curl= curl_init();
			curl_setopt($curl, CURLOPT_URL,            $uri);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$response= curl_exec($curl);
			curl_close($curl);

		}elseif($config['get_content_method']=='stream'){
			$options= array(
				'http' => array(
					'method' => 'GET',
				)
			);
			$context= stream_context_create($options);
			$stream= fopen($uri, 'r', false, $context);
			if($stream)
			{
				$response= stream_get_contents($stream);
				fclose($stream);
			}
		}
		$response= trim($response);
		if($response=='no' && file_exists($droot.'/_buran/'.bsm_server()))
			unlink($droot.'/_buran/'.bsm_server());
	}
}

/*
 *
 *
 *
 *
 */
// ------------------------------------------------------------------
function bsm_tolog($text, $type='logs')
{
	global $logsfile;
	if($type=='errors') $text= date('Y-m-d-H-i-s-').$text;
	if(isset($logsfile[$type])) fwrite($logsfile[$type], $text."\n");
}

function bsm_seohash($droot, $config)
{
	$hash .= md5_file($droot.'/_buran/seoModule.php') ."\n";
	$hash .= md5_file($droot.'/_buran/seoModule_config.php') ."\n";
	$files= glob($droot.$config['tx_path'].'/'.'*.php');
	if(is_array($files) & count($files))
		foreach($files AS $file) $hash .= md5_file($file) ."\n";
	$hash= md5($hash);
	return $hash;
}

function bsm_server()
{
	return md5(php_uname().'/'.phpversion().getenv('DOCUMENT_ROOT'));
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
//--------------------------------------------------
