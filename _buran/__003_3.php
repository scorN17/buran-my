<?php
// Buran_003_2
// scorN - v.1.0
// 20.05.2015
// Буран
//====================================================================================================
	error_reporting( 0 );
//====================================================================================================
	$root= $_SERVER[ 'DOCUMENT_ROOT' ];
	if( isset( $_GET[ 'dir' ] ) ) $dir= mysql_escape_string( trim( urldecode( $_GET[ 'dir' ] ) ) ); else $dir= '/';
		if( substr( $dir, 0, 1 ) != "/" ) $dir= "/" . $dir;
		if( substr( $dir, strlen( $dir )-1, 1 ) != "/" ) $dir .= "/";
	$host= str_replace( "www.", "", $_SERVER[ 'HTTP_HOST' ] );
	$root_dir= $root . $dir;
	
	$maxtime= 20;
	$rashireniya= "/php/js/htaccess/html/htm/inc/txt/";
	$rashireniya= "/php/";
	
	$act= $_GET[ 'act' ];
	
	$ww= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host=". $host ."&w=". $_GET[ 'w' ] );
	//if( ! $ww || $_GET[ 'w' ] == '' || $_GET[ 'w' ] != $ww ) exit();
//====================================================================================================

	$logfiles= '_buran/b003/';
	
		clearstatcache();
	if( $open= opendir( $root_dir . $logfiles ) )
	{
		$var= false;
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir . $logfiles . $file ) )
			{
				$rassh= substr( strrchr( $file, "." ), 1 );
				if( $rassh == 'txt' )
				{
					$fl= fopen( $root_dir . $logfiles . $file, "rb" );
					while( ! feof( $fl ) )
					{
						$content[ $file ] .= fread( $fl, 1024*500 );
					}
					$uncontent[ $file ]= unserialize( $content[ $file ] );
					if( ! $var ){ $first= $uncontent[ $file ]; $var= $uncontent[ $file ]; continue 1; }
					
					foreach( $uncontent[ $file ] AS $key => $row )
					{
						$rassh= substr( strrchr( $key, "." ), 1 );
						if( strpos( $key, 'pageCache.php' ) !== false ) continue 1;
						if( strpos( $rashireniya, '/'.$rassh.'/' ) === false ) continue 1;
						if( $noprint[ $key ] ) continue 1;
						if( preg_match( "/\/b003_(.*)\.txt$/", $key ) === 1 ){ $noprint[ $key ]= true; $print_2 .= '<div style="font-size:11px;font-family:Arial;">'. $key .'</div>'; continue 1; }
						
						if( $key != '_dt' && $key != '_dth' )
						{
							$table[ $key ][ $uncontent[ $file ][ '_dt' ] ]= $row;
							$dts[ $uncontent[ $file ][ '_dt' ] ]= true;
						}
					}
					
					$var= $uncontent[ $file ];
				}
			}
		}
		
		foreach( $table AS $key => $row )
		{
			$ii++;
			//if( $ii < 300 ) continue 1;
			//if( $ii > 302 ) break 1;
			if( strpos( $key, 'assets/cache' ) !== false ) continue 1;
			
			$filename= explode( "/", $key );
			$filename= $filename[ count( $filename ) - 1 ];
			$print .= '<div style="float:left;width:200px;font-size:11px;font-family:Arial;">'. $filename .'</div>';
			
			$prev_filestate= $filestate= -1;
			foreach( $dts AS $_dt => $flag )
			{
				$prev_filestate= $filestate;
				$filestate= $row[ $_dt ];
				if( ! $status[ $key ] || $filestate == -1 ) $status[ $key ]= 'background:#f8f8f8;';
				
				if( ! $filestate ) $status[ $key ]= 'background:#d00;';
				if( ! $prev_filestate && $filestate ) $status[ $key ]= 'background:#0c0;';
				
				if(
					$filestate[ 'md5' ] != $prev_filestate[ 'md5' ] ||
					$filestate[ 'sz' ] != $prev_filestate[ 'sz' ] ||
					//$filestate[ 'at' ] != $prev_filestate[ 'at' ] ||
					$filestate[ 'mt' ] != $prev_filestate[ 'mt' ] ||
					$filestate[ 'ct' ] != $prev_filestate[ 'ct' ]
				)
				{
					$status[ $key ]= 'background:#00a2f0;';
				}
				
				$print .= '<div style="float:left;width:15px;height:15px;margin-right:1px; '. $status[ $key ] .'">&nbsp;</div>';
			}
			$print .= '<div style="clear:both;font-size:0px;line-height:0px;height:2px;">&nbsp;</div>';
		}
	}
	
	print $print;
	print '<h4>Непоказанные файлы</h4>';
	print $print_2;