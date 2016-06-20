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
	
	$b3= "buran__005";
	
	
	$root= $_SERVER[ 'DOCUMENT_ROOT' ];
	$root= '/home/u333528/bunker-yug.ru/www';
	
	@include_once( $root ."/db.php" );
	@include_once( $root ."/idn/idna_convert.class.php" );
	
	
	$idn= new idna_convert();
	
//=======================================================================

?>
	<h1>Архивы сайтов</h1>
	
<?php

	$rr= mysql_query( "SELECT * FROM {$b3}_2 ORDER BY dt DESC" );
	if( $rr && mysql_num_rows( $rr ) > 0 )
	{
		while( $row= mysql_fetch_assoc( $rr ) )
		{
			if( empty( $print[ $row[ 'redirect' ] ] ) )
			{
				$print[ $row[ 'redirect' ] ]= '<br /><br /><div><b><a target="_blank" href="http://'. $row[ 'redirect' ] .'/">www.'. $row[ 'redirect' ] .'</a></b> - <a target="_blank" href="http://'. $row[ 'redirect' ] .'/_buran/b005/log.txt">Лог-файл</a></div>';
			}
			$lines= explode( "\n", $row[ 'result' ] );
			$print[ $row[ 'redirect' ] ] .= '<div>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<a target="_blank" href="'. $lines[ 4 ] .'">'. $lines[ 4 ] .'</a></div>';
		}
		foreach( $print AS $row )
		{
			print $row;
		}
	}


//=======================================================================



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
