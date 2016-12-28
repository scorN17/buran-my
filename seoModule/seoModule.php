<?php
/**
 * seoModule
 * @version 1.6
 * 28.12.2016
 * DELTA
 * sergey.it@delta-ltd.ru
 */

include_once('seoModule_config.php');

error_reporting($configs['global']['error_reporting']);
ini_set('display_errors', $configs['global']['error_reporting']);

define('_', DIRECTORY_SEPARATOR);
$http= ($_SERVER['SERVER_PORT']=='443' || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=='on') ? 'https' : 'http');
$domain= (isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME']);
$www= (strpos($domain,'www.')===0?'www.':'');
if($www=='www.') $domain= substr($domain,4);
$scriptname= (isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:$_SERVER['PHP_SELF']);
$requesturi= $_SERVER['REQUEST_URI'];
$pageurl= parse_url($requesturi, PHP_URL_PATH);
$querystring= $_SERVER['QUERY_STRING'];
if($_SERVER['DOCUMENT_ROOT']) $droot= $_SERVER['DOCUMENT_ROOT'];
else{
	$droot= __FILE__;
	$droot= str_replace($scriptname,'',$droot);
	if(substr($droot,strlen($droot)-1,1)==_) $droot= substr($root,0,-1);
}
$logsfile['logs']= fopen($droot.'/_buran/seoModule_logs', 'a');
$logsfile['errors']= fopen($droot.'/_buran/seoModule_errors', 'a');
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

	if($http.'://'.$www.$domain !== $website[0]) $redirect_to= $requesturi;
	if($redirect[$requesturi]) $redirect_to= $redirect[$requesturi];

	if($redirect_to)
	{
		header('Location: '.$website[0].$redirect_to, true, 301);
		exit();
	}
}
if( ! file_exists($droot.'/_buran/seoModule_hash') || filectime($droot.'/_buran/seoModule_hash')<time()-(60*60*12))
{
	$logsfile['hash']= fopen($droot.'/_buran/seoModule_hash', 'a');
	$seoHash= seoHash($droot, $config);
	tolog('['.date('Y-m-d-H-i-s').'_'.$seoHash.']','hash');
}
// ------------------------------------------------------------------

if(
	$website_num &&
	basename($pageurl)!='seoModule.php' &&
	(
		$config['module_enabled']===true ||
		$_SERVER['REMOTE_ADDR']===$config['module_enabled']
	) &&
	strpos($config['requets_methods'], '/'.$_SERVER['REQUEST_METHOD'].'/')!==false &&
	strpos($_SERVER['HTTP_USER_AGENT'], 'buran_seo_module')===false
)
{
	$seopage= $seopages['global'];
	if(isset($seopages[$website_num])) $seopage= array_merge($seopage, $seopages[$website_num]);

	if(substr($pageurl,(-1)*strlen($config['s_page_suffix']))==$config['s_page_suffix']) $pageurl_without_suffix= substr($pageurl,0,(-1)*strlen($config['s_page_suffix']));

	if(isset($seopage[$requesturi]) || substr($seopage[$pageurl_without_suffix],0,2)=='S:')
	{
		if(isset($seopage[$requesturi]))
		{
			$seoalias= trim($seopage[$requesturi]);
		}else{
			$seoalias= trim($seopage[$pageurl_without_suffix]);
		}
		$seoalias= explode(':', $seoalias);
		$seotype= ($seoalias[0]=='A'?'A':'S');
		$seoalias= $seoalias[1];

		if(file_exists($droot.$config['tx_path']._.$seoalias.'.php'))
		{
			@include_once($droot.$config['tx_path']._.$seoalias.'.php');

			$encoding= mb_detect_encoding($s_text);
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

			if($seotype=='A') $donor= $website[0].$requesturi;
			else $donor= $website[0].$website[2];

			if($config['get_content_method']=='curl')
			{
				$curlheaders= array();
				$getallheaders= (function_exists('getallheaders') ? getallheaders() : my_getallheaders());
				if(is_array($getallheaders) && count($getallheaders))
				{
					foreach($getallheaders AS $key=>$row)
					{
						if($key=='Accept-Encoding') continue;
						if($key=='X-Forwarded-For') continue;
						if($key=='X-Real-Ip') continue;
						if($key=='Connection') $row= 'keep-alive';
						$header= $key.': '.$row;
						if($key=='User-Agent') $header .= ' /buran_seo_module';
						$curlheaders[]= $header;
					}
				}

				$curloptions= array(
					CURLOPT_URL               => $donor,
					CURLOPT_HTTPHEADER        => $curlheaders,
					CURLOPT_HEADER            => true,
					CURLOPT_RETURNTRANSFER    => true,
					// CURLINFO_HEADER_OUT    => true,
					// CURLOPT_COOKIESESSION  => true,
					// CURLOPT_FOLLOWLOCATION => true,
					// CURLOPT_FRESH_CONNECT  => true,
					CURLOPT_CONNECTTIMEOUT    => 10,
					CURLOPT_TIMEOUT           => 10,
				);
				if($http=='https' && $config['https_test'])
				{
					$curloptions[CURLOPT_SSL_VERIFYHOST]= false;
					$curloptions[CURLOPT_SSL_VERIFYPEER]= true;
				}

				$curl= curl_init();
				curl_setopt_array($curl, $curloptions);
				$template= curl_exec($curl);
				$request_info= curl_getinfo($curl);
				list($headers, $template)= explode("\n\r", $template, 2);
				if(curl_errno($curl) || $request_info['http_code']!=200)
				{
					$template= false;
				}else{
					$template= trim($template);
					if($headers)
					{
						$headers= str_replace("\r",'',$headers);
						$headers= explode("\n", $headers);
						if(is_array($headers) && count($headers))
						{
							foreach($headers AS $key => $header)
							{
								if(strpos($header, 'Transfer-Encoding')!==false) continue; //Transfer-Encoding: chunked
								header($header);
							}
						}
					}
				}
				curl_close($curl);

			}elseif($config['get_content_method']=='file_get_contents'){
				$template= false;
			}elseif($config['get_content_method']=='socket'){
				$template= false;
			}else tolog('[error_02]','errors');

			if($template)
			{
				$seoimages= array();
				$imgs= glob($droot.$config['img_path']._.$seoalias.'[0-9].{jpg,png}', GLOB_BRACE);
				if(is_array($imgs) && count($imgs))
				{
					foreach($imgs AS $key => $row)
					{
						$crop= seoImgCrop(str_replace($droot, '', $row), $config['img_width'], $config['img_height'], $droot);
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
				$body .= '<div class="sssmodulebox"><div style="clear:both;font-size:0;line-height:0;">&nbsp;</div>
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
				}else tolog('[error_04]','errors');
				if($cf_cc!==1) tolog('[error_06]','errors');

				if($seotype=='A')
				{
					$template= preg_replace("/<h1(.*)>(.*)<\/h1>/isU", '<h2 ${1}>${2}</h2>', $template);
					if(preg_last_error()) tolog('[error_09]','errors');
					if($cf_cc===1)
					{
						$template= preg_replace("/".$cf2."/s", ($cftype=='#'?$cf:'').$body.($cftype=='%'?$cf:''), $template,1);
						if(preg_last_error()) tolog('[error_10]','errors');
					}

				}elseif($seotype=='S'){
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
									if(preg_last_error()) tolog('[error_11]','errors');
									break;
								}
							}
							if($cs_cc!==1) tolog('[error_08]','errors');
						}else tolog('[error_07]','errors');
					}
				}else tolog('[error_05]','errors');

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
					$template= preg_replace("/<meta (.*)name=('|\")description('|\")(.*)>/isU", $meta_description, $template,1);
					if(preg_last_error()) tolog('[error_13]','errors');
					$template= preg_replace("/<meta (.*)name=('|\")keywords('|\")(.*)>/isU", $meta_keywords, $template,1);
					if(preg_last_error()) tolog('[error_14]','errors');
					$template= preg_replace("/<title>(.*)<\/title>/isU", $meta_title, $template,1);
					if(preg_last_error()) tolog('[error_15]','errors');
				}

				// base
				if($config['base']=='replace_or_add' || $config['base']=='replace_if_exists' || $config['base']=='delete')
				{
					$base= '<base href="'.$website[0].'/" />';
					if($config['base']=='replace_or_add' || $config['base']=='delete')
					{
						$template= preg_replace("/<base (.*)>/iU", '', $template,1);
						if(preg_last_error()) tolog('[error_16]','errors');
					}
					if($config['base']=='replace_or_add')
					{
						$template= preg_replace("/<title>/i", $base."\n\t".'<title>', $template,1);
						if(preg_last_error()) tolog('[error_17]','errors');
					}
					if($config['base']=='replace_if_exists')
					{
						$template= preg_replace("/<base (.*)>/iU", $base, $template,1);
						if(preg_last_error()) tolog('[error_18]','errors');
					}
				}

				// canonical
				if($config['canonical']=='replace_or_add' || $config['canonical']=='replace_if_exists' || $config['canonical']=='delete')
				{
					$canonical= '<link rel="canonical" href="'.$website[0].$requesturi.'" />';
					if($config['canonical']=='replace_or_add' || $config['canonical']=='delete')
					{
						$template= preg_replace("/<link (.*)rel=('|\")canonical('|\")(.*)>/iU", '', $template,1);
						if(preg_last_error()) tolog('[error_19]','errors');
					}
					if($config['canonical']=='replace_or_add')
					{
						$template= preg_replace("/<title>/i", $canonical."\n\t".'<title>', $template,1);
						if(preg_last_error()) tolog('[error_20]','errors');
					}
					if($config['canonical']=='replace_if_exists')
					{
						$template= preg_replace("/<link (.*)rel=('|\")canonical('|\")(.*)>/iU", $canonical, $template,1);
						if(preg_last_error()) tolog('[error_21]','errors');
					}
				}
				if($seotype=='S')
				{
					header('Status: 200 OK');
					header('HTTP/1.1 200 OK');
				}
				print $template;
				exit();

			}else tolog('[error_03]','errors');
		}else tolog('[error_01]','errors');
	}
}

if(basename($pageurl)=='seoModule.php')
{
	if($_GET['a']=='list')
	{
		header('Content-type: text/html; charset=utf-8');

		$files= glob($droot.$config['tx_path']._.'*.php');

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
					$target= _.substr($filename,0,-4);
				}else{
					$seotype= 'A';
				}
				print ' | <span style="color:#47ad00;">ok</span>';
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

		print '[seohash_'.seoHash($droot, $config).']'."\n";
		print '[droot_'.$droot.']'."\n";
		print '[website_'.$website[0].']'."\n";
		print '[mainpage_'.$website[1].']'."\n";
		print '[donor_'.$website[2].']'."\n";
		print '[articlespage_'.$website[3].']'."\n";
		print '[pages_]'."\n";
		foreach($seopage AS $key => $row)
		{
			print $key .(substr($row,0,2)=='S:'?$config['s_page_suffix']:'') ."\n";
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
}

/*
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */
// ------------------------------------------------------------------
function tolog($text, $type='logs')
{
	global $logsfile;
	if($type=='errors') $text= date('Y-m-d-H-i-s-').$text;
	if(isset($logsfile[$type])) fwrite($logsfile[$type], $text."\n");
}

function seoHash($droot, $config)
{
	$hash .= md5_file($droot.'/_buran/seoModule.php') ."\n";
	$hash .= md5_file($droot.'/_buran/seoModule_config.php') ."\n";
	$files= glob($droot.$config['tx_path']._.'*.php');
	if(is_array($files) & count($files))
		foreach($files AS $file) $hash .= md5_file($file) ."\n";
	$hash= md5($hash);
	return $hash;
}

function seoImgCrop($img, $w, $h, $droot, $website='', $baseurl='/')
{
	// v7.2
	// 15.09.2016
	// ImgCrop
	/*
		$img= assets/images/img.jpg
		$dopimg= assets/images/dopimg.jpg
		$toimg= assets/images/toimg.jpg
		
		$w= (int)156
		$h= (int)122
		$backgr= 0/1
		$fill= 0/1
		$x= center/left/right
		$y= center/top/bottom
		$bgcolor= R,G,B,A / x:y / fill:a;b;c;d|b;c;d
		$wm= 0/1
		$filter= a;b;c;d|b;c;d
		$png= 0/1
		$ellipse= max / (int)56
		$degstep= (int)5
		$dopimg_xy= x:y
		$quality= (int)80
		$fullpath
		$r= 0/1
	*/
	//--------------------------------------------------------------------------------------
	$ipathnotphoto= 'template/images/nophoto.png';
	$ipathwatermark= 'template/images/watermark.png';
	//--------------------------------------------------------------------------------------
	//
	//
	//
	//
	//
	//
	//
	//
	//--------------------------------------------------------------------------------------
	$w= intval($w);
	$h= intval($h);
	$backgr= ($backgr===true || $backgr==='true' ? true : false);
	$fill= ($fill===true || $fill==='true' ? true : false);
	$x= ($x=='right' ? $x : 'center');
	$y= ($y=='bottom' ? $y : 'center');
	$bgcolor= (empty($bgcolor) ? '255,255,255,127' : $bgcolor);
	$wm= (empty($wm) ? false : $wm);
	$png= (empty($png) ? false : $png);
	$filter= (empty($filter) ? -1 : $filter);
	$refresh= (empty($r) ? false : true);
	$ellipse= ($ellipse == 'max' ? 'max' : intval($ellipse));
	$quality= intval($quality);
	if($quality === 0) $quality= ($_GET['ww']<=800 ? 60 : 80);
	else $quality= ($quality<0 || $quality>100 ? 80 : $quality);
	$base= ltrim($baseurl, DIRECTORY_SEPARATOR);
	$img= trim(urldecode($img));
	$slashflag= (strpos($img, DIRECTORY_SEPARATOR)===0 ? true : false);
	if($slashflag) $img= ltrim($img, DIRECTORY_SEPARATOR);
	$baseflag= ($base && strpos($img, $base)===0 ? true : false);
	if($baseflag) $img= ltrim($img, $base);
	$root= $droot.(substr($droot,-1,1)!='/'?'/':'');
	if($dopimg)
	{
		$dopimg= trim(urldecode($dopimg));
		$dopimg= ltrim($dopimg, DIRECTORY_SEPARATOR);
		$dopimg= ltrim($dopimg, $base);
		$dopimg= $root.$dopimg;
	}
	if($toimg)
	{
		$toimg= trim(urldecode($toimg));
		$toimg= ltrim($toimg, DIRECTORY_SEPARATOR);
		$toimg= ltrim($toimg, $base);
	}
	if( ! file_exists($root.$img) || ! is_file($root.$img))
	{
		$img= $ipathnotphoto;
		if($fill){ $fill= false; $backgr= true; $bgcolor= '1:1'; }
	}
	if( ! file_exists($root.$img) || ! is_file($root.$img)) return false;
	if($wm && ( ! file_exists($root.$ipathwatermark) || ! is_file($root.$ipathwatermark))) return false;
	if( ! $toimg)
	{
		$imgrassh= substr($img, strrpos($img,'.'));
		$newimg= '_th'.md5($img . $w . $h . $backgr . $fill . $x . $y . $bgcolor . $wm . $filter . $ellipse . $dopimg . $quality) . ($png ? '.png' : $imgrassh);
		$newimg_dir= dirname($img) .DIRECTORY_SEPARATOR.'.th'.DIRECTORY_SEPARATOR;
		if( ! file_exists($root.$newimg_dir)) mkdir($root.$newimg_dir, 0777);
		$newimg_path= $root.$newimg_dir.$newimg;
		$newimg_path_return= ($fullpath ? $website : ($slashflag?DIRECTORY_SEPARATOR:'').($baseflag?$base:'')) .$newimg_dir .$newimg;
	}else{
		$newimg_path= $root.$toimg;
		$newimg_path_return= ($fullpath ? $website : ($slashflag?DIRECTORY_SEPARATOR:'').($baseflag?$base:'')) .$toimg;
	}
	if( ! file_exists($newimg_path) || filectime($root.$img) > filectime($newimg_path)) $refresh= true;
	if(filesize($root.$img) > 1024*1024*10) return $img;
	//--------------------------------------------------------------------------------------
	if( $refresh )
	{
		$img1_info= getimagesize( $root . $img );
		if( ! $img1_info[ 1 ] ) return false;
		$ot= $img1_info[ 0 ] / $img1_info[ 1 ];
		$dstW= ( $w > 0 ? $w : $img1_info[ 0 ] );
		$dstH= ( $h > 0 ? $h : $img1_info[ 1 ] );
		$dstX= 0;
		$dstY= 0;
		$srcW= $img1_info[ 0 ];
		$srcH= $img1_info[ 1 ];
		$srcX= 0;
		$srcY= 0;
		if( $fill )
		{
			$srcW= $img1_info[ 0 ];
			$srcH= round( $img1_info[ 0 ] / ( $dstW / $dstH ) );
			if( $srcH > $img1_info[ 1 ] )
			{
				$srcW= round( $img1_info[ 1 ] / ( $dstH / $dstW ) );
				$srcH= $img1_info[ 1 ];
			}
			if( $x == 'center' ) $srcX= round( ( $img1_info[ 0 ] - $srcW ) / 2 );
			if( $x == 'right' ) $srcX= $img1_info[ 0 ] - $srcW;
			if( $y == 'center' ) $srcY= round( ( $img1_info[ 1 ] - $srcH ) / 2 );
			if( $y == 'bottom' ) $srcY= $img1_info[ 1 ] - $srcH;
		}else{
			if( ( $img1_info[ 0 ] > $w && $w > 0 ) || ( $img1_info[ 1 ] > $h && $h > 0 ) )
			{
				$dstH= round( $dstW / $ot );
				if( $dstH > $h && $h > 0 )
				{
					$dstH= $h;
					$dstW= round( $dstH * $ot );
				}
			}else{
				$dstW= $img1_info[ 0 ];
				$dstH= $img1_info[ 1 ];
			}
			if( $backgr )
			{
				if( $dstW < $w )
				{
					if( $x == 'center' ) $dstX= round( ( $w - $dstW ) / 2 );
					if( $x == 'right' ) $dstX= $w - $dstW;
				}
				if( $dstH < $h )
				{
					if( $y == 'center' ) $dstY= round( ( $h - $dstH ) / 2 );
					if( $y == 'bottom' ) $dstY= $h - $dstH;
				}
			}
		}
		$crW= ( $backgr && $w > 0 ? $w : $dstW );
		$crH= ( $backgr && $h > 0 ? $h : $dstH );
		if( strstr( $bgcolor, "," ) )
		{
			$rgba_arr= explode( ",", $bgcolor );
			for( $kk=0; $kk<=3; $kk++ )
			{
				$rgba_arr[ $kk ]= intval( $rgba_arr[ $kk ] );
				if( $kk <= 2 && ( $rgba_arr[ $kk ] < 0 || $rgba_arr[ $kk ] > 255 ) ) $rgba_arr[ $kk ]= 255;
				if( $kk == 3 && ( $rgba_arr[ $kk ] < 0 || $rgba_arr[ $kk ] > 127 ) ) $rgba_arr[ $kk ]= 127;
			}
			$bgcolor= 'rgba';
		}elseif( strpos( $bgcolor, 'fill:' ) === 0 ){
			$effect= substr( $bgcolor, strpos( $bgcolor, ':' )+1 );
			$bgcolor= 'fill';
		}else{
			$coord_arr= explode( ":", $bgcolor );
			$bgcolor= 'coord';
		}
		//--------------------------------------------------------------------------------------
		if($img1_info[2] == 1) $img1= imagecreatefromgif($root.$img);
		elseif($img1_info[2] == 2) $img1= imagecreatefromjpeg($root.$img);
		elseif($img1_info[2] == 6) $img1= imagecreatefromwbmp($root.$img);
		elseif($img1_info[2] == 3){ $img1= imagecreatefrompng($root.$img); $png= true; }
		if( $bgcolor == 'coord' )
		{
			$col= imagecolorat( $img1, $coord_arr[ 0 ], $coord_arr[ 1 ] );
			$bgcolor= imagecolorsforindex( $img1, $col );
			$rgba_arr[ 0 ]= $bgcolor[ 'red' ];
			$rgba_arr[ 1 ]= $bgcolor[ 'green' ];
			$rgba_arr[ 2 ]= $bgcolor[ 'blue' ];
			$rgba_arr[ 3 ]= $bgcolor[ 'alpha' ];
		}
		$img2= ImageCreateTrueColor( $crW, $crH );
		if( $png )
		{
			imagealphablending( $img2, true );
			imagesavealpha( $img2, true );
			$col= imagecolorallocatealpha( $img2, $rgba_arr[ 0 ], $rgba_arr[ 1 ], $rgba_arr[ 2 ], $rgba_arr[ 3 ] );
		}else{
			$col= imagecolorallocate( $img2, $rgba_arr[ 0 ], $rgba_arr[ 1 ], $rgba_arr[ 2 ] );
		}
		if( $bgcolor == 'fill' )
		{
			imagecopyresampled( $img2, $img1, 0, 0, 0, 0, $crW, $crH, $img1_info[0], $img1_info[1] );
			$effect= explode( '|', $effect );
			if( ! empty( $effect ) )
			{
				foreach( $effect AS $row )
				{
					$tmp= explode( ';', $row );
					if( $tmp[ 0 ] == 2 || $tmp[ 0 ] == 3 || $tmp[ 0 ] == 10 ) imagefilter( $img2, $tmp[ 0 ], $tmp[ 1 ] );
					elseif( $tmp[ 0 ] == 4 ) imagefilter( $img2, $tmp[ 0 ], $tmp[ 1 ], $tmp[ 2 ], $tmp[ 3 ], $tmp[ 4 ] );
					elseif( $tmp[ 0 ] == 11 ) imagefilter( $img2, $tmp[ 0 ], $tmp[ 1 ], $tmp[ 2 ] );
					else imagefilter( $img2, $tmp[ 0 ] );
				}
			}
		}else{
			imagefill( $img2, 0,0, $col );
		}
		imagecopyresampled( $img2, $img1, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH );
		if( $wm )
		{
			$wm_info= getimagesize( $root . $ipathwatermark );
			$img3= imagecreatefrompng( $root . $ipathwatermark );
			$wm_ot= $wm_info[ 0 ] / $wm_info[ 1 ];
			$wmW= $wm_info[ 0 ];
			$wmH= $wm_info[ 1 ];
			if( $crW < $wm_info[ 0 ] )
			{
				$wmW= $crW - round( $crW / 30 );
				$wmH= round( $wmW / $wm_ot );
			}
			if( $crH < $wmH )
			{
				$wmH= $crH - round( $crH / 30 );
				$wmW= round( $wmH * $wm_ot );
			}
			$wmX= round( ( $crW - $wmW ) / 2 );
			$wmY= round( ( $crH - $wmH ) / 2 );
			imagecopyresampled( $img2, $img3, $wmX, $wmY, 0, 0, $wmW, $wmH, $wm_info[ 0 ], $wm_info[ 1 ] );
			imagedestroy( $img3 );
		}
		$filter= explode( '|', $filter );
		if( ! empty( $filter ) )
		{
			foreach( $filter AS $row )
			{
				$tmp= explode( ';', $row );
				if( $tmp[ 0 ] == 2 || $tmp[ 0 ] == 3 || $tmp[ 0 ] == 10 ) imagefilter( $img2, $tmp[ 0 ], $tmp[ 1 ] );
				elseif( $tmp[ 0 ] == 4 ) imagefilter( $img2, $tmp[ 0 ], $tmp[ 1 ], $tmp[ 2 ], $tmp[ 3 ], $tmp[ 4 ] );
				elseif( $tmp[ 0 ] == 11 ) imagefilter( $img2, $tmp[ 0 ], $tmp[ 1 ], $tmp[ 2 ] );
				else imagefilter( $img2, $tmp[ 0 ] );
			}
		}
		if( $ellipse )
		{
			$degstep= ( $degstep ? intval( $degstep ) : 5 );
			$w= ( $crW > $crH ? $crH : $crW );
			$cntr= ($w/2);
			$coord= array();
			$opacitycolor= imagecolorallocatealpha( $img2, 255, 255, 255, 127 );
			if( $ellipse == 'max' ) $ellipse_r= $cntr-1; else $ellipse_r= $ellipse;
			for( $part=1; $part<=4; $part++ )
			{
				for( $deg=0; $deg<90; $deg+=$degstep )
				{
					$mydeg= $deg;
					if( $part == 2 || $part == 4 ) $mydeg= 90 - $deg;
					if( ! $coord[ $mydeg ][ 'x' ] ) $coord[ $mydeg ][ 'x' ]= round( $ellipse_r * cos( deg2rad( $mydeg ) ) );
					if( ! $coord[ $mydeg ][ 'y' ] ) $coord[ $mydeg ][ 'y' ]= round( $ellipse_r * sin( deg2rad( $mydeg ) ) );
					$x= $coord[ $mydeg ][ 'x' ];
					$y= $coord[ $mydeg ][ 'y' ];
					if( $part == 4 ){ $y *= -1; }
					if( $part == 3 ){ $x *= -1; $y *= -1; }
					if( $part == 2 ){ $x *= -1; }
					$points[]= $cntr + $x;
					$points[]= $cntr + $y;
				}
			}
			$points[]= $cntr + $ellipse_r; $points[]= $cntr;
			$points[]= $w; $points[]= $cntr;
			$points[]= $w; $points[]= $w;
			$points[]= 0; $points[]= $w;
			$points[]= 0; $points[]= 0;
			$points[]= $w; $points[]= 0;
			$points[]= $w; $points[]= $cntr;
			$png= true;
			imagealphablending( $img2, false );
			imagesavealpha( $img2, true );
			imagefilledpolygon( $img2, $points, count($points)/2, $opacitycolor );
			//$autrum= imagecolorallocate( $img2, 216, 181, 85 );
			//imageellipse( $img2, $cntr, $cntr, $ellipse_r*2, $ellipse_r*2, $autrum );
		}
		if($dopimg)
		{
			if($dopimg_xy) $dopimg_xy= explode(':', $dopimg_xy);
			imagealphablending($img2, true);
			imagesavealpha($img2, true);
			$dopimg_info= getimagesize($dopimg);
			$img3= imagecreatefrompng($dopimg);
			$diX= round(($crW - $dopimg_info[0]) /2) + ($dopimg_xy[0] ? intval($dopimg_xy[0]) : 0);
			$diY= round(($crH - $dopimg_info[1]) /2) + ($dopimg_xy[1] ? intval($dopimg_xy[1]) : 0);
			imagecopyresampled($img2, $img3, $diX, $diY, 0, 0, $dopimg_info[0], $dopimg_info[1], $dopimg_info[0], $dopimg_info[1]);
			imagedestroy($img3);
		}
		//--------------------------------------------------------------------------------------
		if($png) imagepng($img2, $newimg_path);
		elseif($img1_info[2] == 1) imagegif($img2, $newimg_path, $quality);
		elseif($img1_info[2] == 2) imagejpeg($img2, $newimg_path, $quality);
		elseif($img1_info[2] == 6) imagewbmp($img2, $newimg_path);
			
		chmod($newimg_path, 0755);
		imagedestroy($img1);
		imagedestroy($img2);
	} //if($refresh)
	return $newimg_path_return;
}


/**
 * https://github.com/ralouphie/getallheaders
 * 23.12.2016
 * Get all HTTP header key/values as an associative array for the current request.
 * @return string[string] The HTTP header key/value pairs.
 */
function my_getallheaders()
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
