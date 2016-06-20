<?php
// Buran_008 - Etalon
// scorN - v.2.1
// 19.05.2016
// Буран
//====================================================================================================
	//error_reporting( E_ALL & ~E_NOTICE & ~E_WARNING );
	error_reporting( E_ALL & ~E_NOTICE );
	ini_set( 'display_errors', 'On' );
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                  
	                 
//====================================================================================================
	define( '_DS', DIRECTORY_SEPARATOR );
	$host= str_replace( "www.", "", $_SERVER[ 'HTTP_HOST' ] );
	$root= __FILE__;
	$scriptname= $_SERVER[ 'SCRIPT_NAME' ];
	if( strpos( $root, "\\" ) !== false ) $scriptname= str_replace( "/", "\\", $scriptname );
	$root= str_replace( $scriptname, '', $root );
	if( substr( $root, strlen( $root )-1, 1 ) == _DS ) $root= substr( $root, 0, -1 );
	if( isset( $_GET[ 'dir' ] ) ) $dir= trim( urldecode( $_GET[ 'dir' ] ) ); else $dir= '';
		if( substr( $dir, 0, 1 ) != _DS ) $dir= _DS . $dir;
		if( substr( $dir, strlen( $dir )-1, 1 ) == _DS ) $dir= substr( $dir, 0, -1 );
	$root_dir= $root . $dir;
	
	$ww= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host=". $host ."&w=". $_GET[ 'w' ] );
	if( ! $ww || $_GET[ 'w' ] == '' || $_GET[ 'w' ] != $ww ){ print '[[FAIL]]'; exit(); }
//====================================================================================================
	$act= $_GET[ 'act' ];
	$etalonfile= $_GET[ 'etalon' ];
	
	$rassh= ( isset( $_GET[ 'rassh' ] ) ? $_GET[ 'rassh' ] : '/php/js/htaccess/html/htm/suspected/' );
	
	$b= "\n";
	$br= "<br />";
//====================================================================================================

if( $act == 'last_from_b003' )
{
	if( $open= opendir( $root_dir .'/_buran/b003/' ) )
	{
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir .'/_buran/b003/'. $file ) )
			{
				$lastetalonfile= $root_dir .'/_buran/b003/'. $file;
			}
		}
	}
	if( $lastetalonfile )
	{
		$ff= fopen( $lastetalonfile, 'r' );
		
		if( ! $ff ){ print '[[ERROR_ETALON_FILE]]' .$b; exit(); }
		
		$serialize= '';
		while( ! feof( $ff ) ) $serialize .= fread( $ff, 1024*100 );
		fclose( $ff );
		$etalon= unserialize( $serialize );
		print '[[START]]' .$b;
		buran2( "" );
		print '[[FINISH]]' .$b;
	}
}

if( $act == 'etalon' )
{
	$ff= fopen( $root .( $etalonfile ? ( substr( $etalonfile, 1, 1 ) != '/' ? '/' : '' ) . $etalonfile : '/_buran/b008/etalon.txt' ), 'r' );
	
	if( ! $ff ){ print '[[ERROR_ETALON_FILE]]' .$br; exit(); }
	
	$serialize= '';
	while( ! feof( $ff ) ) $serialize .= fread( $ff, 1024*100 );
	fclose( $ff );
	$etalon= unserialize( $serialize );
	$print= '[[START]]' .$br;
	buran( "" );
	
	$print_1 .= '<h2>Нет файла из эталона</h2>';
	foreach( $etalon AS $file => $info )
	{
		if( ! file_exists( ( isset( $_GET[ 'root' ] ) ? '' : $root_dir._DS ) . $file ) )
		{
			$print_1 .= '<div style="padding-bottom:2px;">';
			$print_1 .= '<span style="text-decoration:none;color:#0042ff;font-family:arial;font-size:16px;">'. ( isset( $_GET[ 'root' ] ) ? '' : $root_dir._DS ) . $file .'</span>' .$br;
			$print_1 .= '</div>';
		}
	}
	
	print $print .$br;
	print '<h2>Файл не совпадает с эталоном</h2>';
	print $print_1 .$br;
	print '<h2>Файла нет в эталоне</h2>';
	print $print_2 .$br;
	print '<h2>Файл совпадает с эталоном</h2>';
	print $print_3 .$br;
	if( isset( $_GET[ 'print' ] ) ){ print '<pre>'; print_r( $etalon ); print '</pre>'; }
}

if( $act == 'create' )
{
	$etalon= array();
	etalon( "", $etalon );
	$serialize= serialize( $etalon );
	@mkdir( $root .'/_buran/b008/', 0777 );
	$ff= fopen( $root .'/_buran/b008/etalon.txt', 'w' );
	$ff_r= fwrite( $ff, $serialize );
	fclose( $ff );
	print '[[OK]]';
}

//====================================================================================================

function buran2( $folder, $rootflag=true, $simple=true )
{
	global $root_dir;
	global $etalon;
	global $rassh;
	global $b;
	global $br;
	
	if( ! $folder ) $folder= _DS;
	
	if( $open= opendir( $root_dir . $folder ) )
	{
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir . $folder . $file ) )
			{
				$file_rassh= substr( strrchr( $file, "." ), 1 );
				if( $rassh != '' && stripos( $rassh, '/'. $file_rassh .'/' ) === false ) continue;
				
				$stat= stat( $root_dir . $folder . $file );
				$md5= md5_file( $root_dir . $folder . $file );
				if( ! $etalon[ $root_dir . $folder . $file ][ 'md5' ] )
				{
					print date( 'd-m-Y, H:i', filectime( $root_dir . $folder . $file ) ) .' | '. $folder . $file .' | - новый файл' .$b;
				}elseif( $etalon[ $root_dir . $folder . $file ][ 'md5' ] != $md5 || $etalon[ $root_dir . $folder . $file ][ 'sz' ] != $stat[ 'size' ] ){
					print date( 'd-m-Y, H:i', filectime( $root_dir . $folder . $file ) ) .' | '. $folder . $file .' | - изменен' .$b;
				}
				
			}elseif( is_link( $root_dir . $folder . $file ) ){
				//
			}elseif( $file != "." && $file != ".." ){
				buran2( $folder . $file . _DS );
			}
		}
	}
}

function buran( $dir )
{
	global $root_dir;
	global $root;
	global $etalon;
	global $print;
	global $print_1;
	global $print_2;
	global $print_3;
	global $br;
	
	if( $open= opendir( $root_dir ._DS. $dir ) )
	{
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir ._DS. $dir . $file ) )
			{
				$rassh= substr( strrchr( $file, "." ), 1 );
				if( $rassh != 'php' ) continue 1;
				$stat= stat( $root_dir ._DS. $dir . $file );
				$md5= md5_file( $root_dir ._DS. $dir . $file );
				if( ! $etalon[ ( isset( $_GET[ 'root' ] ) ? $root.'/' : '' ) . $dir . $file ][ 'md5' ] )
				{
					$print_2 .= '<div style="padding-bottom:2px;">';
					$print_2 .= '<span style="color:#999;font-family:arial;font-size:12px;">'. date( 'd-m-Y, H:i', filectime( $root_dir ._DS. $dir . $file ) ) .' - </span>';
					$print_2 .= '<a style="text-decoration:none;color:#555;font-family:arial;font-size:12px;" target="_blank" href="__001.php?act=printfile&w='. $_GET[ 'w' ] .'&dir=/&file='. $dir . $file .'">'. $dir . $file .'</a>' .$br;
					$print_2 .= '</div>';
					
				}elseif( $etalon[ ( isset( $_GET[ 'root' ] ) ? $root.'/' : '' ) . $dir . $file ][ 'md5' ] != $md5 || $etalon[ ( isset( $_GET[ 'root' ] ) ? $root.'/' : '' ) . $dir . $file ][ 'sz' ] != $stat[ 'size' ] ){
					$print_1 .= '<div style="padding-bottom:2px;">';
					$print_1 .= '<span style="color:#555;font-family:arial;font-size:16px;">'. date( 'd-m-Y, H:i', filectime( $root_dir ._DS. $dir . $file ) ) .' - </span>';
					$print_1 .= '<a style="text-decoration:none;color:#db0000;font-family:arial;font-size:16px;" target="_blank" href="__001.php?act=printfile&w='. $_GET[ 'w' ] .'&dir=/&file='. $dir . $file .'">'. $dir . $file .'</a>' .$br;
					$print_1 .= '</div>';
					
				}elseif( isset( $_GET[ 'green' ] ) ){
					$print_3 .= '<div style="padding-bottom:2px;">';
					$print_3 .= '<span style="color:#555;font-family:arial;font-size:16px;">'. date( 'd-m-Y, H:i', filectime( $root_dir ._DS. $dir . $file ) ) .' - </span>';
					$print_3 .= '<a style="text-decoration:none;color:#39cb00;font-family:arial;font-size:16px;" target="_blank" href="__001.php?act=printfile&w='. $_GET[ 'w' ] .'&dir=/&file='. $dir . $file .'">'. $dir . $file .'</a>' .$br;
					$print_3 .= '</div>';
				}
			}elseif( is_link( $root_dir ._DS. $dir . $file ) ){
				//
			}elseif( $file != "." && $file != ".." ){
				buran( $dir . $file . _DS );
			}
		}
	}
}

function etalon( $dir, &$etalon )
{
	global $root_dir;
	if( $open= opendir( $root_dir ._DS. $dir ) )
	{
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir ._DS. $dir . $file ) )
			{
				$stat= stat( $root_dir . $dir . $file );
				$etalon[ $dir . $file ]= array(
					'md5' => md5_file( $root_dir ._DS. $dir . $file ),
					'sz' => $stat[ 'size' ],
				);
			}elseif( is_link( $root_dir ._DS. $dir . $file ) ){
				//
			}elseif( $file != "." && $file != ".." ){
				etalon( $dir . $file . _DS, $etalon );
			}
		}
	}
}