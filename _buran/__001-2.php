<?php
// Buran_001
// scorN - v.10
// 07.01.2016
// Буран
//====================================================================================================
	error_reporting(-1);            
//====================================================================================================
	define( '_DS', DIRECTORY_SEPARATOR );
	$root= __FILE__;
	$scriptname= $_SERVER[ 'SCRIPT_NAME' ];
	if( strpos( $root, "\\" ) !== false ) $scriptname= str_replace( "/", "\\", $scriptname );
	$root= str_replace( $scriptname, '', $root );
	$host= str_replace( "www.", "", $_SERVER[ 'HTTP_HOST' ] );
	
	//$ww= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host=". $host ."&w=". $_GET[ 'w' ] );
	//if( ! $ww || $_GET[ 'w' ] == '' || $_GET[ 'w' ] != $ww ){ print '[[FAIL]]'; exit(); }
//====================================================================================================

	$c_starttime= microtime( 1 );

	if( isset( $_GET[ 'dir' ] ) ) $dir= trim( urldecode( $_GET[ 'dir' ] ) ); else $dir= _DS;
		if( substr( $dir, 0, 1 ) != _DS ) $dir= _DS . $dir;
		if( substr( $dir, strlen( $dir )-1, 1 ) != _DS ) $dir .= _DS;
	$root_dir= $root . $dir;
	$rn= "\n";
	$br= "<br>";
	$act= $_GET[ 'act' ];
	
/* db */
	if( isset( $_GET[ 'h' ] ) ) $db_host= addslashes( trim( $_GET[ 'h' ] ) );
	if( isset( $_GET[ 'u' ] ) ) $db_user= addslashes( trim( $_GET[ 'u' ] ) );
	if( isset( $_GET[ 'p' ] ) ) $db_passw= addslashes( trim( $_GET[ 'p' ] ) );
	if( isset( $_GET[ 'n' ] ) ) $db_name= addslashes( trim( $_GET[ 'n' ] ) ); else $db_name= $db_user;
	if( isset( $_GET[ 'm' ] ) ) $db_conn_method= addslashes( trim( urldecode( $_GET[ 'm' ] ) ) ); else $db_conn_method= 'SET NAMES';
	if( isset( $_GET[ 'c' ] ) ) $db_conn_charset= addslashes( trim( $_GET[ 'c' ] ) ); else $db_conn_charset= 'utf8';
	if( isset( $_GET[ 'q' ] ) ) $db_query= urldecode( $_GET[ 'q' ] );
/* db */
//====================================================================================================

if( $act == 'antivirus' )
{
	@mysql_connect( $db_host, $db_user, $db_passw, $db_name );
	$mysql_select_db_result= @mysql_select_db( $db_name );
	@mysql_query( "{$db_conn_method} {$db_conn_charset}" );
	if( ! $mysql_select_db_result ) exit();
	$sign= @mysql_query( "SELECT * FROM delta_v_search WHERE ".( $db_query != '' ? $db_query : 'flag_off=0' )." ORDER BY id" );
	@mysql_close();
	
	buran( '', $files );
	
	print_r( $files );
}

//====================================================================================================

function buran( $folder, &$files )
{
	global $root;
	global $dir;
	global $root_dir;
	global $sign;
	
	if( ! $folder ) $folder= _DS;
	
	if( $open= opendir( $root_dir . $folder ) )
	{
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir . $folder . $file ) )
			{
						$tmp3= substr( strrchr( $file, "." ), 1 );
						if( $tmp3 != 'php' ) continue 1;
						
						
				$files[ $folder ][ 'files' ][ $file ]= array(
					'name' => $file
				);
				
				
				/*
						$tmp_filesize= filesize( $root_dir . $folder . $file );
						$o= fopen( $root_dir . $folder . $file, "rb" );
						$r= fread( $o, $tmp_filesize );
						fclose( $o );
						for( $kk=0; $kk < @mysql_num_rows( $sign ); $kk++ )
						{
							$tmp2= 'not';
							$kw1= $kw2= $kw3= $kw4= 0;
							
							$kw1= stripos( $r, @mysql_result( $sign, $kk, 'kw_1' ) );
							if( @mysql_result( $sign, $kk, 'kw_1' ) == '-' || $kw1 !== false )
							{
								$kw2= stripos( $r, @mysql_result( $sign, $kk, 'kw_2' ), $kw1 );
								if( @mysql_result( $sign, $kk, 'kw_2' ) == '-' || $kw2 !== false )
								{
									$kw3= stripos( $r, @mysql_result( $sign, $kk, 'kw_3' ), $kw2 );
									if( @mysql_result( $sign, $kk, 'kw_3' ) == '-' || $kw3 !== false )
									{
										$kw4= stripos( $r, @mysql_result( $sign, $kk, 'kw_4' ), $kw3 );
										if( @mysql_result( $sign, $kk, 'kw_4' ) == '-' || $kw4 !== false )
										{
											if( true )
											{
												if( true )
												{
													if( true )
													{
														$tmp2= 'yea';
													}
												}
											}
										}
									}
								}
							}
							
							if( $tmp2 == 'yea' )
							{
								$files[ $folder ][ 'files' ][ $file ][ 'virus' ]= true;
							}
						}
						*/
						
						
						
						
			}elseif( is_link( $root_dir . $folder . $file ) ){
				//
			}elseif( $file != "." && $file != ".." ){
				$files[ $folder . $file .'/' ]= array(
					'name' => $file
				);
				buran( $folder . $file . _DS, $files );
			}
		}
	}
}

function buran22( $dir )
{
	global $host;
	global $root_dir;
	global $subdir;
	global $c_starttime;
	global $maxtime;
	global $files;
	global $flag_print;
	global $count_files;
	global $count_checkedfiles;
	global $tm;
	global $tpl_item;
	global $tpl_err_opendir;
	global $r_kws;
	global $print_1;
	global $print_2;
	global $signatureinfo;
	global $signatureinfoflag;
	global $maxlvl;
	global $maxfiles;
	global $tpl_maxfiles;
	global $tpl_maxlvl;
	global $tpl_filesize;
	global $b;
	global $ignore;
	global $level_of_directory;
	global $autodeletefilesize;
	global $autodelete;
	global $autocurefile;
	global $ignoreignore;
	
	$count_file_in_directory= 1;
	$flag_print[ 'max_files_this_directory' ]= false;
	$flag_print[ 'max_level_directrory_this_directory' ]= false;
	
	if( $open= opendir( $root_dir . $dir ) )
	{
		while( $file= readdir( $open ) )
		{
			$mt_end= microtime( 1 );
			if( $mt_end - $c_starttime > $maxtime && $maxtime != '' )
			{
				if( ! $flag_print[ 'maxtime' ] )
				{
					$print_1 .= '<div>-maxtime</div>';
					$print_2 .= '[[TIME]]' .$b;
					$flag_print[ 'maxtime' ]= true;
				}
				break 1;
			}
			if( ! is_dir( $root_dir . $dir . $file ) )
			{
				$count_files++;
				if( $count_file_in_directory > $maxfiles && $maxfiles != '' )
				{
					if( ! $flag_print[ 'max_files_this_directory' ] )
					{
						$tmp= str_replace( "[[directory]]", $dir, $tpl_maxfiles );
						$print_1 .= $tmp;
						$flag_print[ 'max_files_this_directory' ]= true;
					}
					if( ! $flag_print[ 'maxfiles' ] )
					{
						$print_2 .= '[[MAXFILES]]' .$b;
						$flag_print[ 'maxfiles' ]= true;
					}
					continue 1;
				}
				$count_file_in_directory += 1;
				$tmp3= substr( strrchr( $file, "." ), 1 );
				if( ( stripos( $files, '/'. $tmp3 .'/' ) !== false || $files == '' ) && stripos( $ignore, '/'. $root_dir . $dir . $file .'/' ) === false )
				{
					$count_checkedfiles++;
					$tmp_filesize= filesize( $root_dir . $dir . $file );
					if( $tmp_filesize > 0 )
					{
						$o= fopen( $root_dir . $dir . $file, "rb" );
						$r= fread( $o, $tmp_filesize );
						for( $kk=0; $kk < @mysql_num_rows( $r_kws ); $kk++ )
						{
							$kws_rassh= @mysql_result( $r_kws, $kk, 'rassh' );
							if( ! empty( $kws_rassh ) && stripos( $kws_rassh, '/'. $tmp3 .'/' ) === false ) continue 1;
							
							$tmp2= 'not';
							$kw1= $kw2= $kw3= $kw4= 0;
							
							$kw1= stripos( $r, @mysql_result( $r_kws, $kk, 'kw_1' ) );
							if( @mysql_result( $r_kws, $kk, 'kw_1' ) == '-' || $kw1 !== false )
							{
								$kw2= stripos( $r, @mysql_result( $r_kws, $kk, 'kw_2' ), $kw1 );
								if( @mysql_result( $r_kws, $kk, 'kw_2' ) == '-' || $kw2 !== false )
								{
									$kw3= stripos( $r, @mysql_result( $r_kws, $kk, 'kw_3' ), $kw2 );
									if( @mysql_result( $r_kws, $kk, 'kw_3' ) == '-' || $kw3 !== false )
									{
										$kw4= stripos( $r, @mysql_result( $r_kws, $kk, 'kw_4' ), $kw3 );
										if( @mysql_result( $r_kws, $kk, 'kw_4' ) == '-' || $kw4 !== false )
										{
											if( true || @mysql_result( $r_kws, $kk, 'ignore' ) == '-' || ( @mysql_result( $r_kws, $kk, 'ignore' ) != '-' && stripos( $r, @mysql_result( $r_kws, $kk, 'ignore' ) ) === false ) )
											{
												if( @mysql_result( $r_kws, $kk, 'size' ) == 0 || ( @mysql_result( $r_kws, $kk, 'size' ) > 0 && @mysql_result( $r_kws, $kk, 'size' ) == $tmp_filesize ) )
												{
													$rr= @mysql_query( "SELECT * FROM delta_v_ignore_file_signature WHERE file='". $dir . $file ."' AND signature=". @mysql_result( $r_kws, $kk, 'id' ) ." AND kws='{$kw1}-{$kw2}-{$kw3}-{$kw4}' LIMIT 1" );
													if( $ignoreignore || ( $rr && mysql_num_rows( $rr ) == 0 ) )
													{
														$tmp2= 'yea';
														
														if( ! $signatureinfoflag[ @mysql_result( $r_kws, $kk, 'id' ) ] )
														{
															$signatureinfoflag[ @mysql_result( $r_kws, $kk, 'id' ) ]= true;
															$signatureinfo .= '<div><pre>'. @mysql_result( $r_kws, $kk, 'id' ) .'. '. @mysql_result( $r_kws, $kk, 'type' ) .' | '. @mysql_result( $r_kws, $kk, 'kw_1' ) .' | '. @mysql_result( $r_kws, $kk, 'kw_2' ) .' | '. @mysql_result( $r_kws, $kk, 'kw_3' ) .' | '. @mysql_result( $r_kws, $kk, 'kw_4' ) .'</pre></div>';
														}
													}
												}
											}
										}
									}
								}
							}
							
							$tpl_item_type= @mysql_result( $r_kws, $kk, 'id' ) .'. '. @mysql_result( $r_kws, $kk, 'type' );
							if( $tmp2 == 'yea' )
							{
								if( $autodelete && ( ! $autodeletefilesize || $autodeletefilesize == $tmp_filesize ) )
								{
									unlink( $root_dir . $dir . $file );
								}else{
									if( $autocurefile ) curefile( $dir . $file );
								}
								
								$tmp= str_replace( "[[type]]", $tpl_item_type, $tpl_item );
								$tmp= str_replace( "[[directory]]", $dir, $tmp );
								$tmp= str_replace( "[[file]]", $file, $tmp );
								$tmp= str_replace( "[[fullpath]]", $dir . $file, $tmp );
								$tmp= str_replace( "[[filesize]]", $tmp_filesize, $tmp );
								$tmp= str_replace( "[[filedate]]", date( 'd-m-Y, H:i:s', filectime( $root_dir . $dir . $file ) ), $tmp );
								$tmp= str_replace( "[[signatureid]]", @mysql_result( $r_kws, $kk, 'id' ), $tmp );
								$tmp= str_replace( "[[strpos]]", $kw1.'-'.$kw2.'-'.$kw3.'-'.$kw4, $tmp );
								
								$print_1 .= $tmp;
								
								if( ! $flag_print[ 'virus' ] )
								{
									$print_2 .= '[[VIRUS]]' .$b;
									$flag_print[ 'virus' ]= true;
								}
								if( ! $flag_print[ $tpl_item_type ] )
								{
									$print_2 .= '[[VV_'. $tpl_item_type .']]' .$b;
									$flag_print[ $tpl_item_type ]= true;
								}
								break 1;
							}
						}
						fclose( $o );
					}
					
					if( $tm > 0 && filectime( $root_dir . $dir . $file ) > $tm )
					{
						$tpl_item_type= 'FileCTime';
						$tmp= str_replace( "[[type]]", $tpl_item_type, $tpl_item );
						$tmp= str_replace( "[[directory]]", $dir, $tmp );
						$tmp= str_replace( "[[file]]", $file, $tmp );
						$tmp= str_replace( "[[fullpath]]", $dir . $file, $tmp );
						$tmp= str_replace( "[[filesize]]", $tmp_filesize, $tmp );
						$tmp= str_replace( "[[filedate]]", date( 'd-m-Y, H:i:s', filectime( $root_dir . $dir . $file ) ), $tmp );
						
						$print_1 .= $tmp;
						
						if( ! $flag_print[ 'virus' ] )
						{
							$print_2 .= '[[VIRUS]]' .$b;
							$flag_print[ 'virus' ]= true;
						}
						if( ! $flag_print[ $tpl_item_type ] )
						{
							$print_2 .= '[[VV_'. $tpl_item_type .']]' .$b;
							$flag_print[ $tpl_item_type ]= true;
						}
					}
				}
			}elseif( is_link( $root_dir . $dir . $file ) ){
				//
			}elseif( $file != "." && $file != ".." && stripos( $ignore, '/'. $file .'/' ) === false ){
				if( $level_of_directory < $maxlvl || $maxlvl == '' )
				{
					$level_of_directory += 1;
					buran( $dir . $file . _DS );
				}else{
					if( ! $flag_print[ 'max_level_directrory_this_directory' ] )
					{
						$tmp= str_replace( "[[directory]]", $dir, $tpl_maxlvl );
						$print_1 .= $tmp;
						$flag_print[ 'max_level_directrory_this_directory' ]= true;
					}
					if( ! $flag_print[ 'maxlvl' ] )
					{
						$print_2 .= '[[MAXLEVEL]]' .$b;
						$flag_print[ 'maxlvl' ]= true;
					}
				}
			}
		}
		$level_of_directory -= 1;
		closedir( $open );
	}else{
		$print_1 .= str_replace( "[[directory]]", $dir, $tpl_err_opendir );
		$print_2 .= '[[OPEN_ERR]]' .$b;
	}
}