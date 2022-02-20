<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/public/box-movie.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/nav-pages.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");
print_page_header([
  "<link rel=\"stylesheet\" href=\"/styles/poster.css\">",
  "<title>".get_text("movies", "ucfirst")." - ".get_text("loading", "ucfirst")." - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);

$items_per_page = 10;
$loading_message = get_text("loading", "ucfirst")."... ".get_text("please wait", "ucfirst")."...";

print_line("<div id=\"main-block\">");
print_line("<div id=\"loading-message\">$loading_message</div>", 2);
$movies = get_movies_from_database();
for ($i = 0; $i < $items_per_page; $i++)
  print_movie_box(null, 2);
print_line("</div>");

print_page_navbar("movie", count($movies), $items_per_page);
print_page_footer();

?>
