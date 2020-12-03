<?php

include('public/box-vid.php');
include('public/mysql_connections.php');
include("public/common.php");
echo "  <link rel='stylesheet' href='/styles/poster.css'>
  <title>Vids - Demo PHP Streamer</title>";
include('public/html-mid.html');

$type = 'vids';
$items_per_page = 10;
$page_no = isset($_COOKIE["Vids-Page"]) ? $_COOKIE["Vids-Page"] : 1;
$limit_start = ($page_no - 1) * $items_per_page;

/** Determine query to use */ 
$query = "select * from vids where status=3 order by modify_timestamp desc";

/** Print boxes */
$res = mysqli_query($con, "$query limit $limit_start, $items_per_page");
while ($r = mysqli_fetch_object($res)) {
  print_vid_box($r->id);
}


include('public/nav-pages.php');
include('public/html-tail.html');

?>