<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-star.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-movie.php");
print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<link rel='stylesheet' href='/styles/star-box.css'>",
  "<title>".$_SERVER["PROJECT_TITLE"]."</title>"
]);

print_line("<div id='main-block'>");

/** Get random stars */
$random_count = 50;
print_line("<h2>Random Stars</h2>", 2);
print_line("<div style='font-size:0;width:100%;overflow-y:auto'>", 2);
print_line("<div style='text-align:center'>", 3);
$db_query = "
  SELECT id, name_$language AS name, COALESCE(count, 0) AS count
  FROM stars LEFT JOIN (
    SELECT star_id, count(*) AS count FROM movies_stars
    WHERE movie_id IN (
      SELECT id FROM movies WHERE status=1
    ) GROUP BY star_id
  ) AS t ON stars.id=t.star_id
  WHERE status=1
  ORDER BY rand() LIMIT $random_count";
$db_response = mysqli_query($mysql_connection, $db_query);
while ($db_row = mysqli_fetch_object($db_response)) print_star_box($db_row, 4);

print_line("</div>", 3);
print_line("</div>", 2);

/** Get random movies */
$random_count = 5;
print_line("<h2>Random Videos</h2>", 2);
$movies = get_movies_from_database();
$random_indexes = array_rand($movies, $random_count);
foreach ($random_indexes as $i) print_movie_box($movies[$i], 2);
print_line("</div>");

print_page_footer();

?>
