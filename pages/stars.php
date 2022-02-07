<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-star.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/nav-pages.php");

print_page_header([  
  "<link rel='stylesheet' href='/styles/star-box.css'>",
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<style>#main-block{font-size:0}</style>",
  "<title>".get_text("stars", 'ucfirst')." - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);

$items_per_page = 50;
$current_page = isset($_COOKIE["Stars-Page"]) ? $_COOKIE["Stars-Page"] : 1;
$limit_start = ($current_page - 1) * $items_per_page;

$stars = get_entity_from_database('star');

print_line("<div id='main-block'>");
for ($i = $limit_start; $i < $limit_start + $items_per_page; $i++) {
  print_star_box($stars[$i]);
}
print_line("</div>");

print_page_navbar('star', count($stars), $items_per_page, $current_page);
print_page_footer();

?>
