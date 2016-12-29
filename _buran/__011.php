<?php
/**
 * Buran_011 - Sitemap
 * @version 1.31
 * 29.12.2016
 * DELTA
 * sergey.it@delta-ltd.ru
 * Буран
 */

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 'on');

define('_', DIRECTORY_SEPARATOR);
$http= ($_SERVER['SERVER_PORT']=='443' || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])=='on') ? 'https' : 'http');
$domain= (isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME']);
$www= (strpos($domain,'www.')===0?'www.':'');
if($www=='www.') $domain= substr($domain,4);
$website= $http.'://'.$www.$domain;
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
if( ! file_exists($droot.'/_buran/b011/')) mkdir($droot.'/_buran/b011/',0777,true);
$logs= fopen($droot.'/_buran/b011/logs', 'a');
// ------------------------------------------------------------------

if(strpos($_SERVER['HTTP_USER_AGENT'], 'buran_sm_sitemap_')===false && (true || $_SERVER['REMOTE_ADDR']==='80.80.109.182') && $pageurl!='/_buran/__011.php')
{
	$uri= $website.$requesturi;

	$curlheaders= array();
	$getallheaders= (function_exists('getallheaders') ? getallheaders() : my2_getallheaders());
	if(is_array($getallheaders) && count($getallheaders))
	{
		foreach($getallheaders AS $key=>$row)
		{
			if($key=='Host') continue;
			if($key=='Accept-Encoding') continue;
			if($key=='X-Forwarded-For') continue;
			if($key=='X-Real-Ip') continue;
			if($key=='Connection') $row= 'keep-alive';
			$header= $key.': '.$row;
			if($key=='User-Agent') $header .= ' /buran_sm_sitemap_1';
			$curlheaders[]= $header;
		}
	}
	$curloptions= array(
		CURLOPT_HTTPHEADER     => $curlheaders,
		CURLOPT_NOBODY         => true,
		CURLOPT_HEADER         => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FRESH_CONNECT  => true,
		CURLOPT_CONNECTTIMEOUT => 1,
		CURLOPT_TIMEOUT_MS     => 100,
	);
	$curl= curl_init();
	curl_setopt_array($curl, $curloptions);
	$response= curl_followlocation($curl, $uri, $redirects_flag, $logs);
	curl_close($curl);







}elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'buran_sm_sitemap_1')!==false){
	$file= $droot.'/_buran/b011/_'.md5($domain);
	if(file_exists($file))
	{
		$fo= fopen($file, 'r');
		if($fo)
		{
			$content= '';
			while( ! feof($fo)) $content .= fread($fo, 1024*1024);
			fclose($fo);
			if($content)
			{
				$content= explode("\n",$content,2);
				$pages= unserialize($content[1]);
				if($content[0] != md5($content[1])) exit();
			}else exit();
		}else exit();
	}

	$website_mainpage= ($pages[$website.'/']['lvl']?$website:false);
	if( ! $website_mainpage)
	{
		$pages= array($website.'/'=>array(
			// 'out'  => false,
			'lvl'  => 1,
			// 'red'  => '', //редирект
			'code' => 0, //код ответ
			'ok'   => 0, //последний положительный ответ
			'tm'   => 0, //проверка
		));
	}else{
		foreach($pages AS $pagekey => $page)
		{
			if($pagekey!=$website.'/' && $page['tm'] && time()-$page['tm']>60*60*24*7) unset($pages[$pagekey]);
		}
		$counter= 0;
		foreach($pages AS $pagekey => $page)
		{

			if(
				! $page['out'] &&
				(
					! $page['tm'] ||
					($pagekey==$website.'/' && time()-$page['tm']>60*60*24) ||
					(
						$page['ok']==0 &&
						time()-$page['tm']>60*60*3
					)
				)
			){}else continue;
			$counter++;
			if($counter>=3) break;

			sleep(1);

			$pages[$pagekey]['tm']= time();

			if(preg_match("/^(http[s]{0,1}:\/\/([a-z0-9\.-]+))(.*)$/i", $pagekey, $matches)!==1) continue;

			// $i_website= $matches[1];
			// $i_domain= $matches[2];
			// $i_uri= $matches[3];
			$uri= $pagekey;


			$curlheaders= array();
			$getallheaders= (function_exists('getallheaders') ? getallheaders() : my2_getallheaders());
			if(is_array($getallheaders) && count($getallheaders))
			{
				foreach($getallheaders AS $key=>$row)
				{
					if($key=='Host') continue;
					if($key=='Accept-Encoding') continue;
					if($key=='X-Forwarded-For') continue;
					if($key=='X-Real-Ip') continue;
					if($key=='Connection') $row= 'keep-alive';
					$header= $key.': '.$row;
					if($key=='User-Agent') $header= str_replace('buran_sm_sitemap_1', 'buran_sm_sitemap_2', $header);
					$curlheaders[]= $header;
				}
			}
			$curloptions= array(
				CURLOPT_HTTPHEADER     => $curlheaders,
				CURLOPT_HEADER         => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FRESH_CONNECT  => true,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT        => 10,
			);
			$curl= curl_init();
			curl_setopt_array($curl, $curloptions);
			$redirects_flag= false;
			$response= curl_followlocation($curl, $uri, $redirects_flag, $logs);
			if(curl_errno($curl)) $request_code= 0; else $request_code= curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$response= str_replace("\r",'',$response);
			$response= explode("\n\n",$response,2);
			curl_close($curl);

			$body= $response[1];

			$pages[$pagekey]['code']= $request_code;
			if(preg_match("/^(http[s]{0,1}:\/\/([a-z0-9\.-]+))(.*)$/i", $uri, $matches)===1)
			{
				if($matches[1]!=$website_mainpage)
				{
					if($pagekey==$website_mainpage.'/')
					{
						if($request_code==200)
						{
							$pages= array();
							break;
						}
					}
				}
				if($matches[1]==$website_mainpage || $pagekey==$website_mainpage.'/') unset($pages[$pagekey]['out']);
					elseif(isset($pages[$pagekey]['out'])) $pages[$pagekey]['out']= true;
				if($uri!=$pagekey) $pages[$pagekey]['red']= $uri; elseif(isset($pages[$pagekey]['red'])) unset($pages[$pagekey]['red']);
			}

			if($request_code==200) $pages[$pagekey]['ok']= time();
			else continue;

			if($pages[$pagekey]['out']) continue;

			//-------------------------------------------------------

			$baseurl= '';
			$mask= "/"."<base (.*)href=['|\"](.*)['|\"](.*)>"."/iU";
			if(preg_match($mask, $body, $matches)===1)
			{
				$baseurl= trim($matches[2]);
				if(strpos($baseurl,'http://')===0 || strpos($baseurl,'https://')===0)
				{
					$baseurl= str_replace('https://','',$baseurl);
					$baseurl= str_replace('http://','',$baseurl);
					if(strpos($baseurl,'/')===false) $baseurl .= '/';
					$baseurl= explode('/',$baseurl,2);
					$baseurl= array_pop($baseurl);
				}
				if(substr($baseurl,0,1)!='/') $baseurl= '/'.$baseurl;
				if(substr($baseurl,-1)!='/') $baseurl .= '/';
			}
			if( ! $baseurl)
			{
				if(strpos($requesturi,'?')!==false) $baseurl= substr($requesturi,0,strpos($requesturi,'?')); else $baseurl= $requesturi;
				$baseurl= explode('/', $baseurl);
				array_pop($baseurl);
				$baseurl= implode('/', $baseurl);
				if(substr($baseurl,0,1)!='/') $baseurl= '/'.$baseurl;
				if(substr($baseurl,-1)!='/') $baseurl .= '/';
			}

			$mask= "/"."<!--(.*)-->"."/sU";
			$body= preg_replace($mask, '', $body);

			// $mask= "/" . "[ ]+(href|src)=(\"(.*)\"|'(.*)'|(.*)\ |(.*)>|(.*)$)" . "/imU";
			//$mask= "/" . "[ ]+(href)=(.+)('|\"|>|\ |$)" . "/imU";
			$mask= "/"."<a (.*)href=['|\"](.*)['|\"](.*)>"."/U";
			preg_match_all($mask, $body, $hrefs);
			if(is_array($hrefs[2]) && count($hrefs[2]))
			{
				foreach($hrefs[2] AS $hrefkey=>$href)
				{
					$href= trim($href);

					if(substr($href,0,7)=='mailto:') continue;
					if(substr($href,0,11)=='javascript:') continue;
					if(substr($href,0,6)=='phone:') continue;
					if(substr($href,0,6)=='skype:') continue;

					if(substr($href,0,2)=='//') $href= 'http:'.$href;

					$params= '';
					$paramspos= strpos($href,'?');
					if($paramspos!==false)
					{
						$params= substr($href,$paramspos);
						$params= str_replace('&amp;','&',$params);

						$href= substr($href,0,$paramspos);
					}

					if(strpos($href,'http://')===0 || strpos($href,'https://')===0)
					{
						preg_match("/^(http[s]{0,1}:\/\/(.*))\//iU", $href, $matches);
						$webs= $matches[1];
						$href= str_replace($webs, '', $href);
					}else{
						$webs= $website;
					}

					if(substr($href,0,1)!='/') $href= $baseurl.$href;
					$href= uri_bez_tochek($href);
					$fullhref= $href.$params;
					// $fullhref= urldecode($fullhref);

					if($pages[$webs.$fullhref]['lvl'])
					{
						if(time()-$pages[$webs.$fullhref]['tm']>60*60*20) $pages[$webs.$fullhref]['tm']= 0;
					}else{
						$pages[$webs.$fullhref]= array(
							// 'out'  => false,
							'lvl'  => $page['lvl']+1,
							'code' => 0,
							'ok'   => 0,
							'tm'   => 0,
						);
					}
				}
			}
		}
	}
	// fwrite($logs, print_r($pages,1)."===\n");

	$content= serialize($pages);
	$md5= md5($content);
	$fo= fopen($file, 'w');
	if($fo)
	{
		fwrite($fo, $md5."\n".$content);
		fclose($fo);
	}
	exit();





}elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'buran_sm_sitemap_2')!==false){
}


if($pageurl=='/_buran/__011.php' && $_GET['a']=='pages')
{
	$file= $droot.'/_buran/b011/_'.md5($domain);
	if(file_exists($file))
	{
		$fo= fopen($file, 'r');
		if($fo)
		{
			$content= '';
			while( ! feof($fo)) $content .= fread($fo, 1024*1024);
			fclose($fo);
			if($content)
			{
				$content= explode("\n",$content,2);
				$pages= unserialize($content[1]);
				if($content[0] != md5($content[1])) exit();
			}
			print'<pre>'.print_r($pages,1).'</pre>';
		}
	}
}


//-------------------------------------------------------------------

function curl_followlocation(&$curl, &$uri, &$redirects_flag, &$logs)
{
	preg_match("/^(http[s]{0,1}:\/\/[a-z0-9\.-]+)(.*)$/i", $uri, $matches);
	$website= $matches[1];
	do{
		// if($referer) curl_setopt($curl, CURLOPT_REFERER, $referer);
		// fwrite($logs, $uri."\n");
		curl_setopt($curl, CURLOPT_URL, $uri);
		$response= curl_exec($curl);
		if(curl_errno($curl)) return false;
		$headers= str_replace("\r",'',$response);
		$headers= explode("\n\n",$headers,2);
		if(preg_match("/^Location: (.*)$/im", $headers[0], $matches)===1)
		{
			$location= true;
			$referer= $uri;
			$uri= trim($matches[1]);
			if(preg_match("/^http[s]{0,1}:\/\/[a-z0-9\.-]+\//i", $uri, $matches)!==1)
				$uri= $website.(substr($uri,0,1)!='/'?'/':'').$uri;
		}else $location= false;
		if($location)
		{
			$redirects_flag= true;
			if($redirects_list[$uri]<=1) $redirects_list[$uri]++;
				else $location= false;
		}
	}while($location);
	return $response;
}

function uri_bez_tochek($uri)
{
	$uri2= str_replace("../", "...//", $uri);
	$uri2= str_replace("./", "", $uri2);
	$pattern= "/\w+\/\.\.\//";
	while(preg_match($pattern, $uri2))
	{
		$uri2= preg_replace($pattern, '', $uri2);
		$uri2= trim($uri2, "\.\./");
	}
	$uri2= ltrim($uri2, "\.\./");
	if(substr($uri,0,1)=='/' && substr($uri2,0,1)!='/') $uri2= '/'.$uri2;
	if(substr($uri,-1)=='/' && substr($uri2,-1)!='/') $uri2 .= '/';
	return $uri2;
}

/**
 * https://github.com/ralouphie/getallheaders
 * 23.12.2016
 * Get all HTTP header key/values as an associative array for the current request.
 * @return string[string] The HTTP header key/value pairs.
 */
function my2_getallheaders()
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