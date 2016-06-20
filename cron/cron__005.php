<?php
// Буран


	//exit();
	
	
	
	error_reporting( 0 );

	$filesstate_raz_v= 60*60*24*30*3;
	
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
	
	$b3= "buran__005";
	
	
	$root= $_SERVER[ 'DOCUMENT_ROOT' ];
	$root= '/home/u333528/bunker-yug.ru/www';
	
	@include_once( $root ."/db.php" );
	@include_once( $root ."/idn/idna_convert.class.php" );
	
	
	$idn= new idna_convert();
	
//=======================================================================

if( intval( date( 'd' ) ) % 2 == 0 )
{
	$rr= mysql_query( "SELECT c.id AS cidc, c.url, b3.dt AS b3dt FROM {$table1}
		INNER JOIN {$table8} ON e.idc=c.id
		INNER JOIN {$table9} ON r.idc=c.id
		LEFT JOIN {$b3} AS b3 ON b3.idc=c.id
			WHERE r.`status`<>4 AND e.ntoday>0
			AND ( ".time()." - b3.dt > {$filesstate_raz_v} OR b3.dt IS NULL )
				ORDER BY b3.dt, c.id LIMIT 5" );

	if( $rr && mysql_num_rows( $rr ) > 0 )
	{
		while( $row= mysql_fetch_assoc( $rr ) )
		{
			if( time() - $row[ 'b3dt' ] > $filesstate_raz_v )
			{
				$url= site_from_url( trim( $row[ 'url' ] ) );
				$url2= $idn->encode( $url );
				$url3= $idn->decode( $url2 );
				
				$redirect= curl( $url2 );
				$redirect= $redirect[ 'info' ][ 'url' ];
				$redirect= site_from_url( $redirect );
				$redirect2= $idn->encode( $redirect );
				$redirect3= $idn->decode( $redirect2 );
				
				$w= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host={$redirect2}&pp=53_6EeSQsBK9_gWoZ8Uf" );
				if( $w )
				{
					$result= @file_get_contents( "http://{$url2}/_buran/__005.php?w={$w}&act=archive_files" );
					
					$rrr= mysql_query( "SELECT id, ii FROM {$b3} WHERE domain='{$url3}' LIMIT 1" );
					if( $rrr && mysql_num_rows( $rrr ) == 1 )
					{
						mysql_query( "UPDATE {$b3} SET idc=0 WHERE id<>". mysql_result( $rrr, 0, 'id' ) ." AND idc='{$row[cidc]}'" );
						
						mysql_query( "UPDATE {$b3} SET idc='{$row[cidc]}', ii='".( mysql_result( $rrr, 0, 'ii' ) + 1 )."',
							redirect='{$redirect3}',
							last='". mysql_escape_string( $result ) ."', dth='". date( 'Y-m-d-H-i-s' ) ."', dt='".time()."'
							WHERE id=". mysql_result( $rrr, 0, 'id' ) ." LIMIT 1" );
						
					}elseif( $rrr ){
						mysql_query( "INSERT INTO {$b3} SET idc='{$row[cidc]}', domain='{$url3}', ii='1',
							redirect='{$redirect3}',
							last='". mysql_escape_string( $result ) ."', dth='". date( 'Y-m-d-H-i-s' ) ."', dt='".time()."'" );
					}
					
					if( substr( $result, 0, 6 ) == '[[OK]]' )
					{
						mysql_query( "INSERT INTO {$b3}_2 SET idc='{$row[cidc]}', domain='{$url3}',
							redirect='{$redirect3}',
							result='". mysql_escape_string( $result ) ."', dth='". date( 'Y-m-d-H-i-s' ) ."', dt='".time()."'" );
						
						break 1;
					}
				}
			}
		}
	}
	
}elseif( true ){
	$rr= mysql_query( "SELECT c.id AS cidc, c.url, b3.db_dt AS b3dt FROM {$table1}
		INNER JOIN {$table8} ON e.idc=c.id
		INNER JOIN {$table9} ON r.idc=c.id
		LEFT JOIN {$b3} AS b3 ON b3.idc=c.id
			WHERE r.`status`<>4 AND e.ntoday>0
			AND ( ".time()." - b3.db_dt > {$filesstate_raz_v} OR b3.db_dt IS NULL )
				ORDER BY b3.db_dt, c.id LIMIT 5" );

	if( $rr && mysql_num_rows( $rr ) > 0 )
	{
		while( $row= mysql_fetch_assoc( $rr ) )
		{
			if( time() - $row[ 'b3dt' ] > $filesstate_raz_v )
			{
				$url= site_from_url( trim( $row[ 'url' ] ) );
				$url2= $idn->encode( $url );
				$url3= $idn->decode( $url2 );
				
				$redirect= curl( $url2 );
				$redirect= $redirect[ 'info' ][ 'url' ];
				$redirect= site_from_url( $redirect );
				$redirect2= $idn->encode( $redirect );
				$redirect3= $idn->decode( $redirect2 );
				
				$w= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host={$redirect2}&pp=53_6EeSQsBK9_gWoZ8Uf" );
				if( $w )
				{
					$result= @file_get_contents( "http://{$url2}/_buran/__005.php?w={$w}&act=archive_db" );
					
					$rrr= mysql_query( "SELECT id, ii FROM {$b3} WHERE domain='{$url3}' LIMIT 1" );
					if( $rrr && mysql_num_rows( $rrr ) == 1 )
					{
						mysql_query( "UPDATE {$b3} SET idc=0 WHERE id<>". mysql_result( $rrr, 0, 'id' ) ." AND idc='{$row[cidc]}'" );
						
						mysql_query( "UPDATE {$b3} SET idc='{$row[cidc]}',
							redirect='{$redirect3}',
							db_last='". mysql_escape_string( $result ) ."', db_dth='". date( 'Y-m-d-H-i-s' ) ."', db_dt='".time()."'
							WHERE id=". mysql_result( $rrr, 0, 'id' ) ." LIMIT 1" );
						
					}elseif( $rrr ){
						mysql_query( "INSERT INTO {$b3} SET idc='{$row[cidc]}', domain='{$url3}', ii='1',
							redirect='{$redirect3}',
							db_last='". mysql_escape_string( $result ) ."', db_dth='". date( 'Y-m-d-H-i-s' ) ."', db_dt='".time()."'" );
					}
					
					if( substr( $result, 0, 6 ) == '[[OK]]' )
					{
						mysql_query( "INSERT INTO {$b3}_2 SET idc='{$row[cidc]}', domain='{$url3}',
							redirect='{$redirect3}',
							result='". mysql_escape_string( $result ) ."', dth='". date( 'Y-m-d-H-i-s' ) ."', dt='".time()."'" );
						
						break 1;
					}
				}
			}
		}
	}
}
	
	
	
	
	
//=======================================================================

function curl( $url )
{
	$head= array( 'User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36' );
	
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
