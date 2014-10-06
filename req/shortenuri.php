<?php
// Copyright (c) 2014 YA-androidapp(https://github.com/YA-androidapp) All rights reserved.
echo shorten_uri($_REQUEST['uri']);

function shorten_uri($longuri) {
 if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $longuri)) {
  $shorturi = file_get_contents('http://tinyurl.com/api-create.php?url='.urlencode($longuri), false, $fgcconf);
  $shorturi = mb_convert_encoding($shorturi, 'utf8', 'auto');
  if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $shorturi)) {
   return $shorturi;
  } else {
   return false;
  }
 }
}