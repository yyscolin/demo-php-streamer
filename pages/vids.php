<?php

include("../public/box-vid.php");
include("../public/mysql_connections.php");
include("../public/nav-pages.php");
include("../public/common.php");
echo "  <link rel='stylesheet' href='/styles/poster.css'>
  <title>".get_text("movies", ucfirst)." - Demo PHP Streamer</title>";
include("../public/common-mid.php");

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

include("../public/html-tail.html");

?>