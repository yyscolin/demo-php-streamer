<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/public/box-star.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/nav-pages.php");

print_page_header([  
  "<link rel=\"stylesheet\" href=\"/styles/star-box.css\">",
  "<title>".get_text("stars", "ucfirst")." - ".get_text("loading", "ucfirst")." - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);

$items_per_page = 50;
$loading_message = get_text("loading", "ucfirst")."... ".get_text("please wait", "ucfirst")."...";

print_line("<div id=\"main-block\">");
print_line("<div id=\"loading-message\">$loading_message</div>", 2);
print_line("<div style=\"font-size:0;width:100%\">", 2);
$stars = get_stars_from_database();
for ($i = 0; $i < $items_per_page; $i++)
  print_star_box();
print_line("</div>", 2);
print_line("</div>");

print_page_navbar("star", count($stars), $items_per_page);
print_page_footer();

?>
