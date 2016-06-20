<?php
// Buran_003
// scorN - v.1.2
// 17.05.2016
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
	
	$maxtime= 20;
	$rashireniya= "/php/js/htaccess/html/htm/inc/txt/";
	
	$act= $_GET[ 'act' ];
	
	$ww= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host=". $host ."&w=". $_GET[ 'w' ] );
	if( ! $ww || $_GET[ 'w' ] == '' || $_GET[ 'w' ] != $ww ) exit();
//====================================================================================================

if( $act == 'savefilesstate' )
{
	clearstatcache();
	
// Открываем последний слепок
	$etalon= false;
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
		if( $ff )
		{
			$serialize= '';
			while( ! feof( $ff ) ) $serialize .= fread( $ff, 1024*100 );
			fclose( $ff );
			$etalon= unserialize( $serialize );
		}
	}
// Открываем последний слепок
	
	print "[[OK]]\n";
	print $host ."\n";
	
	$files= array(
		'_dth' => date( 'Y-m-d-H-i' ),
		'_dt' => time()
	);
	
	$c_starttime= microtime( 1 );
	print "[[START]]\n";
	action( "", $files, $mainlog );
	print "[[SERIALIZE]]\n";
	
	$serialize= serialize( $files );
	
	$logfilename= '/_buran/b003/b003_'. $host .'_'. date( 'Y-m-d-H-i-s' ) .'.txt';
	$logfile= $root_dir . $logfilename;
	print "[[LOGFILE]]\n";
	print $root_dir ."\n";
	print "http://". $host . $logfilename ."\n";
	
	@mkdir( $root_dir .'/_buran/b003/', 0777 );
	$ff= fopen( $logfile, 'w' );
	$ff_r= fwrite( $ff, $serialize );
	fclose( $ff );
	if( ! $ff_r ) print "[[ERROR_SAVE]]\n";
	
	if( $etalon )
	{
		$logfilename_main= '/_buran/b003/b003_'. $host .'__main.txt';
		$ff= fopen( $root_dir . $logfilename_main, 'a' );
		fwrite( $ff, "\n" .'= '. date( 'd-m-Y, H:i' ) .' =' ."\n" );
		fwrite( $ff, $mainlog[ 1 ] . $mainlog[ 2 ] );
		fclose( $ff );
	}
	
	print "[[FINISH]]\n";
}

function action( $folder, &$files, &$mainlog )
{
	global $root_dir;
	global $c_starttime;
	global $maxtime;
	global $rashireniya;
	global $etalon;
	
	if( ! $folder ) $folder= _DS;
	
	if( $open= opendir( $root_dir . $folder ) )
	{
		while( $file= readdir( $open ) )
		{
			$mt_end= microtime( 1 );
			if( $mt_end - $c_starttime > $maxtime && $maxtime != '' )
			{
				print "[[MAXTIME]]\n";
				break 1;
			}
			
			if( filetype( $root_dir . $folder . $file ) == 'link' ) continue;
			
			if( ! is_dir( $root_dir . $folder . $file ) )
			{
				$rassh= substr( strrchr( $file, "." ), 1 );
				
				if( stristr( $rashireniya, '/'. $rassh .'/' ) )
				{
					$stat= stat( $root_dir . $folder . $file );
					$md5= md5_file( $root_dir . $folder . $file );
					$files[ $root_dir . $folder . $file ]= array(
						'md5' => $md5,
						'sz' => $stat[ 'size' ],
						'at' => $stat[ 'atime' ],
						'mt' => $stat[ 'mtime' ],
						'ct' => $stat[ 'ctime' ],
					);
					
					if( $etalon )
					{
						if( ! $etalon[ $root_dir . $folder . $file ][ 'md5' ] )
						{
							$mainlog[ 1 ] .= date( 'd-m-Y, H:i', $stat[ 'ctime' ] ) .' | 01 | '. $folder . $file .' | ' ."\n";
							
						}elseif( $etalon[ $root_dir . $folder . $file ][ 'md5' ] != $md5 || $etalon[ $root_dir . $folder . $file ][ 'sz' ] != $stat[ 'size' ] ){
							$mainlog[ 2 ] .= date( 'd-m-Y, H:i', $etalon[ $root_dir . $folder . $file ][ 'ct' ] ) .' | '. date( 'd-m-Y, H:i', $stat[ 'ctime' ] ) .' | 02 | '. $folder . $file .' | '. $etalon[ $root_dir . $folder . $file ][ 'md5' ] .' | '. $md5 .' | '. $etalon[ $root_dir . $folder . $file ][ 'sz' ] .' | '. $stat[ 'size' ] ."\n";
						}
					}else{
						$mainlog[ 1 ] .= '| 99 |' ."\n";
					}
				}
				
			}elseif( $file != "." && $file != ".." ){
				action( $folder . $file . _DS, $files, $mainlog );
			}
		}
		
	}else{
		print "[[ERROR__OPENDIR]]\n";
	}
}