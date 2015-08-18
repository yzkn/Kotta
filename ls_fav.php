<?php
// Copyright (c) 2014-2015 YA-androidapp(https://github.com/YA-androidapp) All rights reserved.
session_start();
error_reporting(0);
require_once(dirname(__FILE__).'/conf/index.php');

if ( (isset($_SERVER['PHP_AUTH_USER'])) && ($_SERVER['PHP_AUTH_USER'] != '') ) {
 $id = $_SERVER['PHP_AUTH_USER']; $_SESSION['id'] = $id;
} elseif ( (isset($_SESSION['id'])) && ($_SESSION['id'] != '') ) {
 $id = $_SESSION['id'];
} elseif ( (isset($_COOKIE['id'])) && ($_COOKIE['id'] != '') ) {
 $id = $_COOKIE['id']; $_SESSION['id'] = $id;
} elseif ( (isset($_REQUEST['id'])) && ($_REQUEST['id'] != '') ) {
 $id = $_REQUEST['id']; $_SESSION['id'] = $id;
} else {
 $id = '';
}
if ( (isset($_SERVER['PHP_AUTH_PW'])) && ($_SERVER['PHP_AUTH_PW'] != '') ) {
 $pw = $_SERVER['PHP_AUTH_PW']; $_SESSION['pw'] = $pw;
} elseif ( (isset($_SESSION['pw'])) && ($_SESSION['pw'] != '') ) {
 $pw = $_SESSION['pw'];
} elseif ( (isset($_COOKIE['pw'])) && ($_COOKIE['pw'] != '') ) {
 $pw = $_COOKIE['pw']; $_SESSION['pw'] = $pw;
} elseif ( (isset($_REQUEST['pw'])) && ($_REQUEST['pw'] != '') ) {
 $pw = $_REQUEST['pw']; $_SESSION['pw'] = $pw;
} else {
 $pw = '';
}

require_once(realpath(__DIR__).'/conf/index.php');
require_once(dirname(__FILE__).'/req/get_new_files.php');
require_once(dirname(__FILE__).'/req/mp3tag_getid3.php');
$pwdfile = 'pwd/'.$id.'.cgi';
if ( file_exists($pwdfile) ) {
 $tpassword = file_get_contents($pwdfile);
 $tpassword = str_replace(array("\r\n","\n","\r"," "), '', $tpassword);
 if ( ($pw !== '') && ($pw === $tpassword) ) {
  if ( $_REQUEST['favnum'] != '' )       { $favnum = $_REQUEST['favnum'];
  } elseif ( $_SESSION['favnum'] != '' ) { $favnum = $_SESSION['favnum'];
  } else                                 { $favnum = ''; }

if ( $favnum === '_recently_added' ) {
  $line = getNewFiles($base_dir);
} else {
  $line = file('fav/'.$id.'_'.$favnum.'.cgi', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
}
  @ini_set('zlib.output_compression', 'Off');
  @ini_set('output_buffering', 'Off');
  @ini_set('output_handler', '');
  @apache_setenv('no-gzip', 1);
  function output_chunk($chunk) {
   echo sprintf("%x\r\n", strlen($chunk));
   echo $chunk."\r\n";
  }
  header("Content-type: application/octet-stream");
  header("Transfer-encoding: chunked");
  flush();
  $i = 0;
  foreach ($line as $value) {
   if (!is_array($value)) {
    $getmp3info_parts = array();
    $getmp3info_parts = getmp3info(realpath($value));
    $json = json_encode( 
     array(
      "track" => $i,
      "datasrc" => str_replace($base_dir.((mb_substr($base_dir,-1)=='/')?'':'/'), $base_uri, realpath($value)),
      "title" => htmlspecialchars($getmp3info_parts[0], ENT_QUOTES),
      "favcheck" => urlencode(str_replace($base_dir.((mb_substr($base_dir,-1)=='/')?'':'/'), '', realpath($value))),
      "basename" => basename($value),
      "id" => $id,
      "favnum" => $favnum,
      "artistdirtmp" => str_replace(array($base_dir.((mb_substr($base_dir,-1)=='/')?'':'/'), '/'.basename($value)), array('', ''), realpath($value)),
      "artist" => htmlspecialchars($getmp3info_parts[1], ENT_QUOTES),
      "album" => htmlspecialchars($getmp3info_parts[2], ENT_QUOTES),
      "number" => htmlspecialchars($getmp3info_parts[3], ENT_QUOTES),
      "genre" => htmlspecialchars($getmp3info_parts[4], ENT_QUOTES),
      "time_m" => htmlspecialchars( (($getmp3info_parts[5]<10)?("0".$getmp3info_parts[5]):($getmp3info_parts[5])) , ENT_QUOTES),
      "time_s" => htmlspecialchars( (($getmp3info_parts[6]<10)?("0".$getmp3info_parts[6]):($getmp3info_parts[6])) , ENT_QUOTES),
     ) 
    ); 
    output_chunk($json.str_repeat(' ', 8000)."\n");
    flush();
    $i++;
   }
  }
  echo "0\r\n\r\n";
 }
}
