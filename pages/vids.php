<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/nav-pages.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<title>".get_text("movies", ucfirst)." - Demo PHP Streamer</title>"
]);

$type = 'vids';
$items_per_page = 10;
$current_page = isset($_COOKIE["Vids-Page"]) ? $_COOKIE["Vids-Page"] : 1;
$limit_start = ($current_page - 1) * $items_per_page;

/** Determine query to use */ 
$query = "select * from vids where status=3 order by modify_timestamp desc";

/** Print page contents */
print_line("<div id='main-block'>");
$res = mysqli_query($con, "$query limit $limit_start, $items_per_page");
while ($r = mysqli_fetch_object($res)) {
  $title = $_SERVER["show_vid_code"] == "true" ? "$r->id $r->title" : $r->title;
  print_vid_box($r->id, $title, 2);
}
print_line("</div>");

print_page_navbar($type, $query, $items_per_page, $current_page);
print_page_footer();

?>