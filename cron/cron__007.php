<?php
// Буран

	
	error_reporting( 0 );

	$raz_v= 60*60*24*10;
	
	$limit= 3;
	
	$table1= 'customer AS c';
	$table2= 'website_satellit AS ws';
	$table3= 'website_param AS wp';
	$table4= 'fn_delta';
	$table5= 'custmworks AS cw';
	$table6= 'workers AS w';
	$table8= 'extracts AS e';
	$table9= 'reports AS r';
	
	$table10= 'mh_domain AS dd';
	$table11= 'mh_poddomain AS pdd';
	
	$b3= "buran__007";
	
	
	$root= $_SERVER[ 'DOCUMENT_ROOT' ];
	$root= '/home/u333528/bunker-yug.ru/www';
	
	@include_once( $root ."/db.php" );
	@include_once( $root ."/idn/idna_convert.class.php" );
	
	
	$idn= new idna_convert();
	
//=======================================================================

	$rr= mysql_query( "SELECT c.id AS cidc, c.url AS maindomain, b3.dt AS b3dt FROM {$table1}
		INNER JOIN {$table8} ON e.idc=c.id
		INNER JOIN {$table9} ON r.idc=c.id
		LEFT JOIN {$b3} AS b3 ON b3.idc=c.id
			WHERE r.`status`<>2 AND r.`status`<>4 AND r.`status`<>6 AND r.`status`<>8 AND e.ntoday>0
			AND ( ".time()." - b3.dt > {$raz_v} OR b3.dt IS NULL )
				ORDER BY b3.dt, c.id LIMIT {$limit}" );

	if( $rr && mysql_num_rows( $rr ) > 0 )
	{
		while( $row= mysql_fetch_assoc( $rr ) )
		{
			if( time() - $row[ 'b3dt' ] > $raz_v )
			{
				main( $row, true );
			}
		}
	}
//=======================================================================
	$rr= mysql_query( "SELECT dd.domain AS maindomain, b3.dt AS b3dt FROM {$table10}
		LEFT JOIN {$b3} AS b3 ON b3.domain=dd.domain
			WHERE ( ".time()." - b3.dt > {$raz_v} OR b3.dt IS NULL ) AND dd.deleted=0
				ORDER BY b3.dt LIMIT {$limit}" );

	if( $rr && mysql_num_rows( $rr ) > 0 )
	{
		while( $row= mysql_fetch_assoc( $rr ) )
		{
			if( time() - $row[ 'b3dt' ] > $raz_v )
			{
				main( $row );
			}
		}
	}
//=======================================================================
	$rr= mysql_query( "SELECT pdd.poddomain AS maindomain, b3.dt AS b3dt FROM {$table11}
		LEFT JOIN {$b3} AS b3 ON b3.domain=pdd.poddomain
			WHERE ( ".time()." - b3.dt > {$raz_v} OR b3.dt IS NULL ) AND pdd.deleted=0
				ORDER BY b3.dt LIMIT {$limit}" );

	if( $rr && mysql_num_rows( $rr ) > 0 )
	{
		while( $row= mysql_fetch_assoc( $rr ) )
		{
			if( time() - $row[ 'b3dt' ] > $raz_v )
			{
				//main( $row );
			}
		}
	}




function main( $row, $onlywhois= false )
{
	global $b3;
	global $idn;
	
	$server= array(
		'ru' =>			'whois.tcinet.ru',
		'рф' =>			'whois.tcinet.ru',
		'su' =>			'whois.tcinet.ru',
		'biz' =>		'whois.biz',
		'com' =>		'whois.verisign-grs.com',
		'net' =>		'whois.verisign-grs.com',
		'org' =>		'whois.pir.org',
		'pro' =>		'whois.dotproregistry.net',
		'md' =>			'whois.nic.md',
		'info' =>		'whois.afilias.net',
		'us' =>			'whois.nic.us'
	);
	
	//	whois.iana.org
	
	$url= site_from_url( trim( $row[ 'maindomain' ] ) );
	$url2= $idn->encode( $url );
	$url3= $idn->decode( $url2 );
	
	$point= explode( ".", $url );
	$point= $point[ count( $point ) - 1 ];
	
	if( isset( $server[ $point ] ) )
	{
		$fp= fsockopen( $server[ $point ], 43, $errno, $errstr, 30 );

		if( ! $fp )
		{
			$response= false;

		}else{
			fputs( $fp, $url2 ."\r\n" );

			while( ! feof( $fp ) ) $response .= fread( $fp, 128 );

			fclose( $fp );
		}
		
	}else{
		$response= false;
	}
	
	$ourhosting= 0;
	if( $response )
	{
		if( stristr( $response, "yandex" ) ) $ourhosting= 2;
		if( stristr( $response, "masterhost" ) ) $ourhosting= 1;
	}
	if( $onlywhois ) $ourhosting= 3;
	
	if( true )
	{
		$rrr= mysql_query( "SELECT id, ii FROM {$b3} WHERE domain='{$url3}' LIMIT 1" );
		if( $rrr && mysql_num_rows( $rrr ) == 1 )
		{
			mysql_query( "UPDATE {$b3} SET idc=0 WHERE id<>". mysql_result( $rrr, 0, 'id' ) ." AND idc='{$row[cidc]}'" );
			
			mysql_query( "UPDATE {$b3} SET idc='{$row[cidc]}', ii='".( mysql_result( $rrr, 0, 'ii' ) + 1 )."',
				ourhosting='". mysql_escape_string( $ourhosting ) ."',
				whois='". mysql_escape_string( $response ) ."',
				dth='". date( 'Y-m-d-H-i-s' ) ."', dt='".time()."'
				WHERE id=". mysql_result( $rrr, 0, 'id' ) ." LIMIT 1" );
			
		}elseif( $rrr ){
			mysql_query( "INSERT INTO {$b3} SET idc='{$row[cidc]}', domain='{$url3}', ii='1',
				ourhosting='". mysql_escape_string( $ourhosting ) ."',
				whois='". mysql_escape_string( $response ) ."',
				dth='". date( 'Y-m-d-H-i-s' ) ."', dt='".time()."'" );
		}
	}
}



//=======================================================================


function curl( $url )
{
	$head= array(
		'User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36'
	);
	
	$curl= curl_init();
	
	$options= array(
				CURLOPT_URL => $url,
				CURLOPT_HTTPHEADER => $head,
				CURLOPT_AUTOREFERER => true,
				CURLOPT_COOKIESESSION => true,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_FRESH_CONNECT => true,
				//CURLINFO_HEADER_OUT => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_NOBODY => true,
				CURLOPT_TIMEOUT => 20
	);
	curl_setopt_array( $curl, $options );
	$result= curl_exec( $curl );
	$info= curl_getinfo( $curl );
	curl_close( $curl );
	
	return array( 'body' => $result, 'info' => $info );
}

function page_from_url( $str )
{
	$str= trim( $str );
	$str= str_replace( "https://", '', $str );
	$str= str_replace( "http://", '', $str );
	$str= str_replace( "../", '.../', $str );
	$str= str_replace( "./", '/', $str );
	$str= str_replace( "//", '/', $str );
	
	$str2= explode( "/", $str );
	$str= str_replace( $str2[0], '', $str );
	$str= trim( $str );
	
	return $str;
}


	//print get_full_url( 'javascript:post(sdf.ru', 'domain.ru', 'http://domain.ru/?bbb=222' );


function get_full_url( $url, $webs, $page )
{
	$tmp= trim( $url );
	
	$page= explode( "?", $page );
	$page= $page[ 0 ];
	
	$arr= explode( "/", page_from_url( $page ) );
	unset( $arr[ count( $arr ) - 1 ] );
	unset( $arr[ 0 ] );
	$dirname= '';
	foreach( $arr AS $val )
	{
		$dirname .= "/". $val;
	}
	$dirname .= "/";
	
	if( substr( $tmp, 0, 7 ) == 'mailto:' ) $tmp= 'http://' . $webs . '/';
	elseif( substr( $tmp, 0, 11 ) == 'javascript:' ) $tmp= 'http://' . $webs . '/';
	elseif( substr( $tmp, 0, 2 ) == '//' ) $tmp= 'http:' . $tmp;
	elseif( substr( $tmp, 0, 1 ) == '/' ) $tmp= 'http://' . $webs . get_url_bez_tochek( $tmp );
	elseif( substr( $tmp, 0, 7 ) == 'http://' ) NULL;
	elseif( substr( $tmp, 0, 8 ) == 'https://' ) NULL;
	elseif( substr( $tmp, 0, 1 ) == '?' ) $tmp= $page . $url;
	else $tmp= 'http://'. $webs . get_url_bez_tochek( ( $dirname != 'http:/' ? $dirname : '' ) . $tmp );
	
	$tmp= explode( "#", $tmp );
	$tmp= $tmp[ 0 ];
	
	$tmp= str_replace( "www.", '', $tmp );
	
	return $tmp;
}


function get_url_bez_tochek( $adres )
{
	$adres= str_replace( "../", "...//", $adres );
	$adres= str_replace( "./", "", $adres );
	$adres= ltrim( $adres, "\.\./" );
	
	$pattern = '/\w+\/\.\.\//';
	while( preg_match( $pattern, $adres ) )
	{
		$adres= preg_replace( $pattern, '', $adres );
		$adres= trim( $adres, "\.\./" );
	}
	
	if( substr( $adres, 0, 1 ) != '/' ) $adres= '/'. $adres;
	
	return $adres;
}



function site_from_url( $str )
{
	$str= trim( $str );
	$str= strtolower( $str );
	$str= str_replace( "https://www.", '', $str );
	$str= str_replace( "https://", '', $str );
	$str= str_replace( "http://www.", '', $str );
	$str= str_replace( "http://", '', $str );
	$str= str_replace( "www.", "", $str );
	$str= str_replace( "//", "/", $str );
	$str= explode( "/", $str );
	$str= explode( ":", $str[0] );
	$str= str_replace( "/", '', $str[0] );
	$str= str_replace( " ", '', $str );
	$str= str_replace( ",", '', $str );
	$str= str_replace( ";", '', $str );
	$str= trim( $str );
	
	return $str;
}
