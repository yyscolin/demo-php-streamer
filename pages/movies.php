<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-movie.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/nav-pages.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<title>".get_text("movies", 'ucfirst')." - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);

$items_per_page = 10;
$current_page = isset($_COOKIE["Vids-Page"]) ? $_COOKIE["Vids-Page"] : 1;
$limit_start = ($current_page - 1) * $items_per_page;

print_line("<div id='main-block'>");
$movies = get_movies_from_database();
$limit_end = min($limit_start + $items_per_page, count($movies));
for ($i = $limit_start; $i < $limit_end; $i++) {
  $movie = $movies[$i];
  print_movie_box($movie, 2);
}
print_line("</div>");

print_page_navbar('movie', count($movies), $items_per_page, $current_page);
print_page_footer();

?>
