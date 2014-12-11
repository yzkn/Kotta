<?php
// Copyright (c) 2014 YA-androidapp(https://github.com/YA-androidapp) All rights reserved.
session_start();
set_time_limit(0);
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
require_once(dirname(__FILE__).'/req/mp3tag_getid3.php');
$pwdfile = 'pwd/'.$id.'.cgi';
if ( file_exists($pwdfile) ) {
 $tpassword = file_get_contents($pwdfile);
 $tpassword = str_replace(array("\r\n","\n","\r"," "), '', $tpassword);
 if ( ($pw !== '') && ($pw === $tpassword) ) {

  if ( (isset($confs['dbfilename']) == false) || ($confs['dbfilename'] == '') ) { $confs['dbfilename'] = 'musics.sqlite3'; }

  try {
   $db = new PDO('sqlite:./conf/'.$confs['dbfilename']);
   $sql = "CREATE TABLE IF NOT EXISTS musics(i integer primary key autoincrement, datasrc text, title text, favcheck text, basename text, artistdirtmp text, artist text, album text, number integer, genre text, time_m text, time_s text)";
   $db->query($sql);
   $db = null;
  } catch (Exception $e) {
   die("Exception: " . $e->getMessage());
  }
  getdirtree($base_dir);
 }
}

function getdirtree($path){
 global $arguments, $base_dir, $base_uri, $confs;

 $rpath = realpath($path);
 if ($handle = opendir($rpath)) {
  try {
   $db = new PDO('sqlite:./conf/'.$confs['dbfilename']);

   $sql = "INSERT INTO musics( datasrc , title , favcheck , basename , artistdirtmp , artist , album , number , genre , time_m , time_s)"
                   ." values (:datasrc, :title, :favcheck, :basename, :artistdirtmp, :artist, :album, :number, :genre, :time_m, :time_s)";

   $sth = $db->prepare($sql);
   $db->beginTransaction();
   while (false !== ($file = readdir($handle))) {
    if ('.' == $file || '..' == $file) { continue; }
    if (is_dir($rpath.'/'.$file)) {
     getdirtree($rpath.'/'.$file);
    } elseif ( (is_file($rpath.'/'.$file)) && (stripos(realpath($rpath.'/'.$file), '.mp3') !== FALSE) ) {
     echo realpath($rpath.'/'.$file)."<br>";
     $r2path = str_replace($base_dir.'/', '', $rpath);
     if (  ($arguments['filter_dir']=='')  || (($arguments['filter_dir'] !='') &&(fnmatch($arguments['filter_dir'],$r2path)==1))          ) {
      if ( ($arguments['filter_file']=='') || (($arguments['filter_file']!='') &&(fnmatch($arguments['filter_file'],basename($file))==1)) ) {
       if (    ($confs['filter_dir']=='')  || (    ($confs['filter_dir'] !='') &&    (fnmatch($confs['filter_dir'],$r2path)==1))          ) {
        if (   ($confs['filter_file']=='') || (    ($confs['filter_file']!='') &&    (fnmatch($confs['filter_file'],basename($file))==1)) ) {
         $getmp3info_parts = array();
         $getmp3info_parts = getmp3info(realpath($rpath.'/'.$file));





         $rslt = $sth->execute( 
          array(
           ":datasrc" => str_replace($base_dir.((mb_substr($base_dir,-1)=='/')?'':'/'), $base_uri, realpath($rpath.'/'.$file)),
           ":title" => htmlspecialchars($getmp3info_parts[0], ENT_QUOTES),
           ":favcheck" => urlencode(str_replace($base_dir.((mb_substr($base_dir,-1)=='/')?'':'/'), '', realpath($rpath.'/'.$file))),
           ":basename" => basename($rpath.'/'.$file),
           ":artistdirtmp" => str_replace(array($base_dir.((mb_substr($base_dir,-1)=='/')?'':'/'), '/'.basename($rpath.'/'.$file)), array('', ''), realpath($rpath.'/'.$file)),
           ":artist" => htmlspecialchars($getmp3info_parts[1], ENT_QUOTES),
           ":album" => htmlspecialchars($getmp3info_parts[2], ENT_QUOTES),
           ":number" => htmlspecialchars($getmp3info_parts[3], ENT_QUOTES),
           ":genre" => htmlspecialchars($getmp3info_parts[4], ENT_QUOTES),
           ":time_m" => htmlspecialchars( (($getmp3info_parts[5]<10)?("0".$getmp3info_parts[5]):($getmp3info_parts[5])) , ENT_QUOTES),
           ":time_s" => htmlspecialchars( (($getmp3info_parts[6]<10)?("0".$getmp3info_parts[6]):($getmp3info_parts[6])) , ENT_QUOTES),
          )
         );
         if ($rslt){
          echo 'データの追加に成功しました: '.htmlspecialchars($getmp3info_parts[0], ENT_QUOTES).'<br>';
         }else{
          $db->rollBack();
          $db = null;
          die('Exception: データの追加に失敗しました');
         }





        }
       }
      }
     }
    }
   }
   closedir($handle);
   $db->commit();
   $db = null;
  } catch (PDOException $e) {
   $db->rollBack();
   $db = null;
   die("Exception: " . $e->getMessage());
  }
 }
}
