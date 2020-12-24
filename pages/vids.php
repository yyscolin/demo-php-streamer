<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/nav-pages.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
echo "  <link rel='stylesheet' href='/styles/poster.css'>
  <title>".get_text("movies", ucfirst)." - Demo PHP Streamer</title>";
require_once($_SERVER['DOCUMENT_ROOT']."/public/common-mid.php");

$type = 'vids';
$items_per_page = 10;
$current_page = isset($_COOKIE["Vids-Page"]) ? $_COOKIE["Vids-Page"] : 1;
$limit_start = ($current_page - 1) * $items_per_page;

/** Determine query to use */ 
$query = "select * from vids where status=3 order by modify_timestamp desc";

/** Print boxes */
$res = mysqli_query($con, "$query limit $limit_start, $items_per_page");
while ($r = mysqli_fetch_object($res)) {
  print_vid_box($r->id);
}

print_page_navbar($type, $query, $items_per_page, $current_page);

require_once($_SERVER['DOCUMENT_ROOT']."/public/html-tail.html");

?>