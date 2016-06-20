<?php
// Buran_008 - Etalon
// scorN - v.1.3
// 07.01.2016
// Буран
//====================================================================================================
	error_reporting(0);
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                                    
	                                                                                  
//====================================================================================================
	define( '_DS', DIRECTORY_SEPARATOR );
	$host= str_replace( "www.", "", $_SERVER[ 'HTTP_HOST' ] );
	$root= __FILE__;
	$scriptname= $_SERVER[ 'SCRIPT_NAME' ];
	if( strstr( $root, "\\" ) ) $scriptname= str_replace( "/", "\\", $scriptname );
	$root= str_replace( $scriptname, '', $root );
	if( substr( $root, strlen( $root )-1, 1 ) == _DS ) $root= substr( $root, 0, -1 );
	if( isset( $_GET[ 'dir' ] ) ) $dir= trim( urldecode( $_GET[ 'dir' ] ) ); else $dir= '';
		if( substr( $dir, 0, 1 ) != _DS ) $dir= _DS . $dir;
		if( substr( $dir, strlen( $dir )-1, 1 ) == _DS ) $dir= substr( $dir, 0, -1 );
	$root_dir= $root . $dir;
	
	//$ww= @file_get_contents( "http://fndelta.gavrishkin.ru/__password__002.php?host=". $host ."&w=". $_GET[ 'w' ] );
	//if( ! $ww || $_GET[ 'w' ] == '' || $_GET[ 'w' ] != $ww ){ print '[[FAIL]]'; exit(); }
//====================================================================================================
	$act= $_GET[ 'act' ];
	$etalonfile= $_GET[ 'etalon' ];
	
	$b= "\n";
	$br= "<br />";
	
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
if( $act == 'etalon' )
{
	?>
<style type="text/css">
	wrapper {
		display: table;
		width: 100%;
	}
		files {
			display: table-cell;
			vertical-align: top;
			width: 290px;
		}
			files .folder_name {
				background: #f0f0f0;
				text-align: center;
				font-weight: bold;
				padding: 2px 5px;
				font-size: 11px;
			}
			files .folder_files {
			}
				files .folder_files >a {
					display: block;
					margin: 0px 1px 1px 0px;
					text-decoration: none;
					font-size: 12px;
					color: #000;
					line-height: 15px;
				}
		subm {
			display: table-cell;
			vertical-align: top;
		}
</style>
	<?php
	
	$ff= fopen( $root .( $etalonfile ? ( substr( $etalonfile, 1, 1 ) != '/' ? '/' : '' ) . $etalonfile : '/_buran/b008/etalon.txt' ), 'r' );
	
	if( ! $ff ){ print '[[ERROR_ETALON_FILE]]' .$br; exit(); }
	
	$serialize= '';
	while( ! feof( $ff ) ) $serialize .= fread( $ff, 1024*100 );
	fclose( $ff );
	$etalon= unserialize( $serialize );
	$print= '[[START]]' .$br;
	buran( "", $fileslist );
	
//=============================================================
			$WW= '<wrapper>
				<files>
					<!--div class="folder_name">Корень</div-->
					<div class="folder_files"><span>&nbsp;</span>[[/]]<div class="clr"></div></div>
				</files>
				<subm>{{/}}</sub>
				<div class="clr"></div>
			</wrapper>';
			if( $fileslist )
			{
				foreach( $fileslist AS $key => $row )
				{
					if( strpos( $fileslist[ '/' ][ 'folders' ][ 'str' ], '|'.$key ) === false ) continue 1;
					
					$key2= substr( $key, 0, -1 );
					$parent= substr( $key2, 0, strrpos( $key2, "/" )+1 );
					$folder_name= substr( $key2, strrpos( $key2, "/" )+1 );
					
					$tmp= '<wrapper>
						<files>
							<div class="folder_name" title="'. $key .'">'. $folder_name .'</div>
							<div class="folder_files"><a target="_blank" href="?act=addtoetalon&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $key .'&h='. $_GET[ 'h' ] .'&u='. $_GET[ 'u' ] .'&p='. $_GET[ 'p' ] .'&n='. $_GET[ 'n' ] .'&m='. $_GET[ 'm' ] .'&c='. $_GET[ 'c' ] .'&q='. $_GET[ 'q' ] .'" title="'. $key .'">&nbsp;</a>[['. $key .']]<div class="clr"></div></div>
						</files>
						<subm>{{'. $key .'}}</sub>
						<div class="clr"></div>
					</wrapper>';
					$WW= str_replace( '{{'. $parent .'}}', $tmp.'{{'. $parent .'}}', $WW );
					
					$tmp= '';
					if( $row[ 'files' ] )
					{
						foreach( $row[ 'files' ] AS $key2 => $row2 )
						{
							$tmp .= '<a class="'. $row2[ 'type' ] .'" target="_blank" href="?act=addtoetalon&w='. $_GET[ 'w' ] .'&dir='. $dir .'&file='. $key . $key2 .'&h='. $_GET[ 'h' ] .'&u='. $_GET[ 'u' ] .'&p='. $_GET[ 'p' ] .'&n='. $_GET[ 'n' ] .'&m='. $_GET[ 'm' ] .'&c='. $_GET[ 'c' ] .'&q='. $_GET[ 'q' ] .'" title="'. $key2 .'">'. $key2 .'</a>';
						}
					}
					$WW= str_replace( '[['. $key .']]', $tmp, $WW );
				}
				$WW= preg_replace( "/\{\{(.*)\}\}/", '', $WW );
			}
			print $WW;
//=============================================================
	
	$print_1 .= '<h2>Нет файла из эталона</h2>';
	foreach( $etalon AS $file => $info )
	{
		if( ! file_exists( ( isset( $_GET[ 'root' ] ) ? '' : $root_dir.'/' ) . $file ) )
		{
			$print_1 .= '<div style="padding-bottom:2px;">';
			$print_1 .= '<span style="text-decoration:none;color:#0042ff;font-family:arial;font-size:16px;">'. ( isset( $_GET[ 'root' ] ) ? '' : $root_dir .'/' ) . $file .'</span>' .$br;
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
if( $act == 'addtoetalon' )
{
	@mysql_connect( $db_host, $db_user, $db_passw, $db_name );
	$mysql_select_db_result= @mysql_select_db( $db_name );
	@mysql_query( "{$db_conn_method} {$db_conn_charset}" );
	if( ! $mysql_select_db_result ) exit();
	
	$file= $_GET[ 'file' ];
	
	if( $_POST[ 'path' ] )
	{
		$save_cms= addslashes( trim( $_POST[ 'cms' ] ) );
		$save_type= $_POST[ 'type' ];
		$save_sub= intval( $_POST[ 'sub' ] );
		$save_path= trim( $_POST[ 'path' ] );
		
		if( substr( $save_path, 0, 1 ) != '/' ) $save_path= '/'. $save_path;
		
		$file_type= 'folder';
		if( substr( $save_path, -1, 1 ) != '/' )
		{
			$file_type= 'file';
			$save_sub= 1;
		}
		
		$save_domain= $host;
		
		$save_path= addslashes( $save_path );
		
		$cms_domain= ( $save_cms ? "cms='{$save_cms}'" : "domain='{$save_domain}'" );
		
		$sz= $md5= '';
		if( $file_type == 'file' )
		{
			$sz= filesize( $root_dir . $save_path );
			$md5= md5_file( $root_dir . $save_path );
		}
		
		$rr= mysql_query( "SELECT * FROM delta_v_etalon WHERE {$cms_domain} AND `path`='{$save_path}' LIMIT 1" );
		if( $rr && mysql_num_rows( $rr ) == 1 )
		{
			mysql_query( "UPDATE delta_v_etalon SET type='{$save_type}', md5='{$md5}', size='{$sz}' WHERE {$cms_domain} AND path='{$save_path}' LIMIT 1" );
		}elseif( $rr ){
			mysql_query( "INSERT INTO delta_v_etalon SET {$cms_domain}, type='{$save_type}', path='{$save_path}', md5='{$md5}', size='{$sz}'" );
		}
		
		if( $save_sub >= 2 )
		{
			$fileslist= array();
			perebor( $save_path, $save_sub, &$fileslist );
			if( $fileslist )
			{
				foreach( $fileslist AS $fileinfo )
				{
					$rr= mysql_query( "SELECT * FROM delta_v_etalon WHERE {$cms_domain} AND `path`='{$fileinfo[0]}' LIMIT 1" );
					if( $rr && mysql_num_rows( $rr ) == 1 )
					{
						mysql_query( "UPDATE delta_v_etalon SET type='{$save_type}', md5='{$fileinfo[1]}', size='{$fileinfo[2]}' WHERE {$cms_domain} AND path='{$fileinfo[0]}' LIMIT 1" );
					}elseif( $rr ){
						mysql_query( "INSERT INTO delta_v_etalon SET {$cms_domain}, type='{$save_type}', path='{$fileinfo[0]}', md5='{$fileinfo[1]}', size='{$fileinfo[2]}'" );
					}
				}
			}
		}
	}
	
	$cms= cms();
	
	print '<form action="" method="post">
		CMS: <select size="2" name="cms" style="padding:5px;">
			<option selected="selected" value="'. $cms[ 1 ] .'">'. $cms[ 1 ] .'</option>
			<option value="0">~ По домену ~</option>
		</select>
		
		<br />
		<br />
		Тип: <select size="10" name="type" style="padding:5px;">
			<option value="usual">Обычный</option>
			<option selected="selected" value="system">Системный</option>
			<option value="resources">Ресурс</option>
			<option value="dynamic">Динамический</option>
		</select>
		
		<br />
		<br />
		<select size="10" name="sub" style="padding:5px;">
			<option selected="selected" value="1">Только выбранный элемент</option>
			
			<optgroup label="Вложенные 1-го уровня">
				<option value="2">Папки и файлы</option>
				<option value="3">Папки</option>
				<option value="4">Файлы</option>
			</optgroup>
			
			<optgroup label="Все вложенные">
				<option value="5">Папки и файлы</option>
				<option value="6">Папки</option>
				<option value="7">Файлы</option>
			</optgroup>
		</select>
		
		<br />
		<br />
		Путь: <input style="width:90%;padding:5px;" type="text" name="path" value="'. $file .'" />
		<br />
		Если в конце слеш - это папка.
		<br />
		Все остальное - файлы.
		
		<br />
		<br />
		<br />
		<input style="padding:10px;" type="submit" value="Сохранить" />
	</form>';
}

	
//====================================================================================================

//====================================================================================================
function perebor( $folder, $sub, &$fileslist )
{
	global $root_dir;
	global $root;
	
	if( ! $folder ) $folder= _DS;
	
	if( $open= opendir( $root_dir . $folder ) )
	{
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir . $folder . $file ) )
			{
				if( $sub == 2 || $sub == 4 || $sub == 5 || $sub == 7 )
				{
					$fileslist[]= array(
						$folder . $file,
						md5_file( $root_dir . $folder . $file ),
						filesize( $root_dir . $folder . $file ),
					);
				}
			}elseif( is_link( $root_dir . $folder . $file ) ){
				//
			}elseif( $file != "." && $file != ".." ){
				if( $sub == 2 || $sub == 3 || $sub == 5 || $sub == 6 ) $fileslist[]= array( $folder . $file . _DS );
				if( $sub >= 5 ) perebor( $folder . $file . _DS, $sub, $fileslist );
			}
		}
	}
}

function buran( $folder, &$fileslist )
{
	global $root_dir;
	global $root;
	global $etalon;
	global $print;
	global $print_1;
	global $print_2;
	global $print_3;
	global $br;
	
	if( ! $folder ) $folder= _DS;
	
	if( $open= opendir( $root_dir . $folder ) )
	{
		$fileslist[ $folder ]= array();
		
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir . $folder . $file ) )
			{
				$rassh= substr( strrchr( $file, "." ), 1 );
				if( $rassh != 'php' ) continue 1;
				$stat= stat( $root_dir . $folder . $file );
				$md5= md5_file( $root_dir . $folder . $file );
				if( ! $etalon[ ( isset( $_GET[ 'root' ] ) ? $root : '' ) . $folder . $file ][ 'md5' ] )
				{
					$print_2 .= '<div style="padding-bottom:2px;">';
					$print_2 .= '<span style="color:#999;font-family:arial;font-size:12px;">'. date( 'd-m-Y, H:i', filectime( $root_dir . $folder . $file ) ) .' - </span>';
					$print_2 .= '<a style="text-decoration:none;color:#555;font-family:arial;font-size:12px;" target="_blank" href="__001.php?act=printfile&w='. $_GET[ 'w' ] .'&dir=/&file='. $folder . $file .'">'. $folder . $file .'</a>' .$br;
					$print_2 .= '</div>';
					
				}elseif( $etalon[ ( isset( $_GET[ 'root' ] ) ? $root : '' ) . $folder . $file ][ 'md5' ] != $md5 || $etalon[ ( isset( $_GET[ 'root' ] ) ? $root : '' ) . $folder . $file ][ 'sz' ] != $stat[ 'size' ] ){
					$print_1 .= '<div style="padding-bottom:2px;">';
					$print_1 .= '<span style="color:#555;font-family:arial;font-size:16px;">'. date( 'd-m-Y, H:i', filectime( $root_dir . $folder . $file ) ) .' - </span>';
					$print_1 .= '<a style="text-decoration:none;color:#db0000;font-family:arial;font-size:16px;" target="_blank" href="__001.php?act=printfile&w='. $_GET[ 'w' ] .'&dir=/&file='. $folder . $file .'">'. $folder . $file .'</a>' .$br;
					$print_1 .= '</div>';
					
				}elseif( isset( $_GET[ 'green' ] ) ){
					$print_3 .= '<div style="padding-bottom:2px;">';
					$print_3 .= '<span style="color:#555;font-family:arial;font-size:16px;">'. date( 'd-m-Y, H:i', filectime( $root_dir . $folder . $file ) ) .' - </span>';
					$print_3 .= '<a style="text-decoration:none;color:#39cb00;font-family:arial;font-size:16px;" target="_blank" href="__001.php?act=printfile&w='. $_GET[ 'w' ] .'&dir=/&file='. $folder . $file .'">'. $folder . $file .'</a>' .$br;
					$print_3 .= '</div>';
				}
				
				if( ! $fileslist[ '/' ][ 'folders' ][ $folder ] )
				{
					$fileslist[ '/' ][ 'folders' ][ $folder ]= true;
					$fileslist[ '/' ][ 'folders' ][ 'str' ] .= '|'. $folder .'|';
				}
				$fileslist[ $folder ][ 'files' ][ $file ][ 'id' ]= 1;
			}elseif( is_link( $root_dir . $folder . $file ) ){
				//
			}elseif( $file != "." && $file != ".." ){
				buran( $folder . $file . _DS, $fileslist );
			}
		}
	}
}

function etalon( $folder, &$etalon )
{
	global $root_dir;
	if( $open= opendir( $root_dir . $folder ) )
	{
		while( $file= readdir( $open ) )
		{
			if( ! is_dir( $root_dir . $folder . $file ) )
			{
				$stat= stat( $root_dir . $folder . $file );
				$etalon[ $folder . $file ]= array(
					'md5' => md5_file( $root_dir . $folder . $file ),
					'sz' => $stat[ 'size' ],
				);
			}elseif( is_link( $root_dir . $folder . $file ) ){
				//
			}elseif( $file != "." && $file != ".." ){
				etalon( $folder . $file . _DS, $etalon );
			}
		}
	}
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