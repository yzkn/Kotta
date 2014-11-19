<?php
// Copyright (c) 2014 YA-androidapp(https://github.com/YA-androidapp) All rights reserved.
session_start();
error_reporting(0);

if ( (isset($_SERVER['PHP_AUTH_USER'])) && ($_SERVER['PHP_AUTH_USER'] != '') ) {
 $id = $_SERVER['PHP_AUTH_USER']; $_SESSION['id'] = $id;
} elseif ( (isset($_REQUEST['id'])) && ($_REQUEST['id'] != '') ) {
 $id = $_REQUEST['id']; $_SESSION['id'] = $id;
} elseif ( (isset($_SESSION['id'])) && ($_SESSION['id'] != '') ) {
 $id = $_SESSION['id'];
} else {
 $id = '';
}
if ( (isset($_SERVER['PHP_AUTH_PW'])) && ($_SERVER['PHP_AUTH_PW'] != '') ) {
 $pw = $_SERVER['PHP_AUTH_PW']; $_SESSION['pw'] = $pw;
} elseif ( (isset($_REQUEST['pw'])) && ($_REQUEST['pw'] != '') ) {
 $pw = $_REQUEST['pw']; $_SESSION['pw'] = $pw;
} elseif ( (isset($_SESSION['pw'])) && ($_SESSION['pw'] != '') ) {
 $pw = $_SESSION['pw'];
} else {
 $pw = '';
}
if ( (isset($_REQUEST['pw2'])) && ($_REQUEST['pw2'] != '') ) {
 $pw2 = $_REQUEST['pw2']; $_SESSION['pw2'] = $pw2;
} elseif ( (isset($_SESSION['pw2'])) && ($_SESSION['pw2'] != '') ) {
 $pw2 = $_SESSION['pw2'];
} else {
 $pw2 = '';
}
if ( $_REQUEST['bdir'] != '' ) {
 $bdir = $_REQUEST['bdir'];
} elseif ( $_SESSION['bdir'] != '' ) {
 $bdir = $_SESSION['bdir'];
} else {
 $bdir = '';
}
if ( $_REQUEST['term'] != '' ) {
 $term = $_REQUEST['term'];
} elseif ( $_SESSION['term'] != '' ) {
 $term = $_SESSION['term'];
} else {
 $term = '';
}

require_once(realpath(__DIR__).'/conf/index.php');
if ( $enable_autocomplete_dir == 0 ) { die(''); }
$pwdfile = 'pwd/'.$id.'.cgi';
if ( file_exists($pwdfile) ) {
 $tpassword = file_get_contents($pwdfile);
 $tpassword = str_replace(array("\r\n","\n","\r"," "), '', $tpassword);
 if ( ($pw !== '') && ($pw === $tpassword) ) {

  require_once(realpath(__DIR__).'/req/lib_getdirtree_flat.php');
  $dirs = array();
  $arguments['depth'] = mb_substr_count($bdir.'/'.$term, '/') + 1;
  $keywords = getdirtree_flat(realpath($base_dir.'/'.$bdir), 'dir');
  $keywords2 = array();
  foreach ($keywords as $k => $v) {
   $keywords2[str_replace(realpath($base_dir.'/'.$bdir).'/', '', urldecode($v))] = str_replace(realpath($base_dir.'/'.$bdir).'/', '', urldecode($v));
  }

  array_walk(
   $keywords2,
   function($value, $key) {
    global $base_dir, $result, $term;
    if (mb_strpos(strtolower($key), strtolower($term)) === 0) {
     $result[] = array('id' => $value,
                       'label' => $key,
                       'value' => $key
                      );
    }
   });
  print(json_encode($result));

 }
}
