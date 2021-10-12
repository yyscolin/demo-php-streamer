<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/nav-pages.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<title>".get_text("movies", 'ucfirst')." - Demo PHP Streamer</title>"
]);

$items_per_page = 10;
$current_page = isset($_COOKIE["Vids-Page"]) ? $_COOKIE["Vids-Page"] : 1;
$limit_start = ($current_page - 1) * $items_per_page;

print_line("<div id='main-block'>");
$vids = get_vids_from_database();
for ($i = $limit_start; $i < $limit_start+$items_per_page; $i++) {
  $vid = $vids[$i];
  print_vid_box($vid, 2);
}
print_line("</div>");

print_page_navbar('vid', count($vids), $items_per_page, $current_page);
print_page_footer();

?>
