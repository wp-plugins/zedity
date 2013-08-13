<?php
header('Content-type: text/css');
session_start();
if (!isset($_SESSION['webfonts'])) die;
foreach ($_SESSION['webfonts'] as $font) {
	$fontname = explode(',',$font);
	$fontname = urlencode($fontname[0]);
	echo "@import url(\"//fonts.googleapis.com/css?family=$fontname\");\n";
}

