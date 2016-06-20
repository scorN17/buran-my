<?php

	
	error_reporting( 0 );

	
	
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
	$table12= 'mh_synonym AS snm';
	
	$b3= "buran__004";
	
	
	$root= $_SERVER[ 'DOCUMENT_ROOT' ];
	$root= '/home/u333528/bunker-yug.ru/www';
	
	@include_once( $root ."/db.php" );
	@include_once( $root ."/idn/idna_convert.class.php" );
	
	
	$idn= new idna_convert();
	
//=======================================================================

?>
	<h1>Ошибки обновления</h1>
	
<?php


	$rr= mysql_query( "SELECT b3.* FROM {$table10}
		LEFT JOIN {$b3} AS b3 ON b3.domain=dd.domain
			WHERE b3.last NOT LIKE '[[OK]]%' AND dd.deleted=0" );

	if( $rr && mysql_num_rows( $rr ) > 0 )
	{
		print '<table border="1" cellpadding="5" cellspacing="0">';
		
		while( $row= mysql_fetch_assoc( $rr ) )
		{
			$url= site_from_url( trim( $row[ 'domain' ] ) );
			$url2= $idn->encode( $url );
			
			print '<tr>';
			print '<td><a target="_blank" href="http://'. $url2 .'/">'. $url .'</a></td>';
			print '<td><a target="_blank" href="http://'. $row[ 'redirect' ] .'/">'. $row[ 'redirect' ] .'</a></td>';
			print '<td><a target="_blank" href="https://www.nic.ru/whois/?query='. $url2 .'">Whois</a></td>';
			print '<td>'. date( 'd.m.Y, H:i', $row[ 'dt' ] ) .'</td>';
			print '<td>';
				if( $row[ 'idc' ] ) print '<a target="_blank" href="http://bunker-yug.ru/customer.php?p=edit&id='. $row[ 'idc' ] .'">карточка ID '. $row[ 'idc' ] .'</a>';
			print '</td>';
			print '<td>'. substr( $row[ 'last' ], 0, 10 ) .'</td>';
			print '</tr>';
		}
		
		print '</table>';
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
	$str= str_replace( "https://www.", '', $str );
	$str= str_replace( "https://", '', $str );
	$str= str_replace( "http://www.", '', $str );
	$str= str_replace( "http://", '', $str );
	$str= str_replace( "www.", "", $str );
	$str= str_replace( "//", "/", $str );
	$str= strtolower( $str );
	$str= explode( "/", $str );
	$str= explode( ":", $str[0] );
	$str= str_replace( "/", '', $str[0] );
	$str= str_replace( " ", '', $str );
	$str= str_replace( ",", '', $str );
	$str= str_replace( ";", '', $str );
	$str= trim( $str );
	
	return $str;
}
