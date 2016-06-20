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
	
	$act= $_GET[ 'act' ];
	
	$ww= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host=". $host ."&w=". $_GET[ 'w' ] );
	//if( ! $ww || $_GET[ 'w' ] == '' || $_GET[ 'w' ] != $ww ) exit();
//====================================================================================================

	$logfiles= '_buran/b003/';
	
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
						$content[ $file ] .= fread( $fl, 1024*100 );
					}
					$uncontent[ $file ]= unserialize( $content[ $file ] );
					if( ! $var ){ $first= $uncontent[ $file ]; $var= $uncontent[ $file ]; continue 1; }
					
					foreach( $uncontent[ $file ] AS $key => $row )
					{
						if( stristr( $key, 'pageCache.php' ) ) continue 1;
						if( $noprint[ $key ] ) continue 1;
						if( preg_match( "/\/b003_(.*)\.txt$/", $key ) === 1 ){ $noprint[ $key ]= true; $print_2 .= '<div style="font-size:11px;font-family:Arial;">'. $key .'</div>'; continue 1; }
						
						if( $key != '_dt' && $key != '_dth' )
						{
							if(
								$row[ 'md5' ] != $var[ $key ][ 'md5' ] ||
								$row[ 'sz' ] != $var[ $key ][ 'sz' ] ||
								//$row[ 'at' ] != $var[ $key ][ 'at' ] ||
								$row[ 'mt' ] != $var[ $key ][ 'mt' ] ||
								$row[ 'ct' ] != $var[ $key ][ 'ct' ]
							)
							{
								$table[ $key ][ $uncontent[ $file ][ '_dt' ] ]= $row;
								$dts[ $uncontent[ $file ][ '_dt' ] ]= true;
							}
						}
					}
					
					$var= $uncontent[ $file ];
				}
			}
		}
		
		$print .= '<table border="1" width="100%" cellpadding="5" cellspacing="0" style="font-size:11px;font-family:Arial;border-collapse:collapse;">';
			$print .= '<tr>';
				$print .= '<td>Файл</td>';
				//$print .= '<td>'. date( 'd.m.Y, H:i', $first[ '_dt' ] ) .'</td>';
				foreach( $dts AS $_dt => $flag )
				{
					//$print .= '<td>'. date( 'd.m.Y, H:i', $_dt ) .'</td>';
				}
			$print .= '</tr>';
		foreach( $table AS $key => $row )
		{
			$filename= explode( "/", $key );
			$filename= $filename[ count( $filename ) - 1 ];
			$print .= '<tr style="'.( $first[ $key ][ 'md5' ] || count( $row ) > 1 ? 'background:#f0f0f0;' : '' ).'">';
				$print .= '<td style="color:#888;"><div style="color:#00e;padding-bottom:5px;">'. $filename .'</div>'. $key .'</td>';
				if( $first[ $key ][ 'md5' ] )
				{
					$print .= '<td align="center">';
					$print .= '<div style="color:#00e;padding-bottom:5px;"><nobr>'. date( 'd.m.Y', $first[ '_dt' ] ) .'</nobr>' ."</div>";
					$print .= $first[ $key ][ 'md5' ] ."<br />";
					$print .= round( $first[ $key ][ 'sz' ] / 1024 ) ." Кб<br />";
					$print .= '<div style="color:#aaa;"><nobr>'. date( 'd.m.Y, H:i', $first[ $key ][ 'at' ] ) .'</nobr>' ."</div>";
					$print .= date( 'd.m.Y, H:i', $first[ $key ][ 'mt' ] ) ."<br />";
					$print .= date( 'd.m.Y, H:i', $first[ $key ][ 'ct' ] ) ."";
					$print .= '</td>';
				}
				foreach( $dts AS $_dt => $flag )
				{
					if( $row[ $_dt ][ 'md5' ] )
					{
						$print .= '<td align="center">';
						$md5= substr( $row[ $_dt ][ 'md5' ], 0, 10 );
						$print .= '<div style="color:#00e;padding-bottom:5px;"><nobr>'. date( 'd.m.Y', $_dt ) .'</nobr>' ."</div>";
						$print .= $md5 ."<br />";
						$print .= '<nobr>'. round( $row[ $_dt ][ 'sz' ] / 1024 ) ." Кб" .'</nobr>' ."<br />";
						$print .= '<div style="color:#aaa;"><nobr>'. date( 'd.m.Y, H:i', $row[ $_dt ][ 'at' ] ) .'</nobr>' ."</div>";
						$print .= '<nobr>'. date( 'd.m.Y, H:i', $row[ $_dt ][ 'mt' ] ) .'</nobr>' ."<br />";
						$print .= '<nobr>'. date( 'd.m.Y, H:i', $row[ $_dt ][ 'ct' ] ) .'</nobr>' ."";
						$print .= '</td>';
					}
				}
			$print .= '</tr>';
		}
		$print .= '</table>';
		
	}
	
	print $print;
	print '<h4>Непоказанные файлы</h4>';
	print $print_2;