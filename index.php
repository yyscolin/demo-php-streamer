<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/box-star.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/box-movie.php");
print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<link rel='stylesheet' href='/styles/star-box.css'>",
  "<title>".$PROJ_CONF["PROJECT_TITLE"]."</title>"
]);

print_line("<div id='main-block'>");

$epoch_days = floor(time() / 24 / 60 / 60);

/** Get featured stars */
$min_stars_count = 5;
$db_query = "SELECT FLOOR(COUNT(*)/$min_stars_count) AS count FROM stars WHERE status=1";
$db_response = mysqli_query($mysql_connection, $db_query);
$db_row = mysqli_fetch_object($db_response);
$featured_group_count = $db_row->count;
$todays_group_id = $epoch_days % $featured_group_count;

$db_query = "
  SELECT id, name_$language AS name, COALESCE(count, 0) AS count
  FROM stars LEFT JOIN (
    SELECT star_id, count(*) AS count FROM movies_stars
    WHERE status=1
    AND movie_id IN (
      SELECT id FROM movies WHERE status=1
    )
    GROUP BY star_id
  ) AS t ON stars.id=t.star_id
  WHERE id IN (
    SELECT id FROM (
      SELECT ROW_NUMBER() OVER(ORDER BY id) % $featured_group_count AS group_id, id, name_en
      FROM stars WHERE status=1
    ) t1 WHERE group_id=$todays_group_id
  )";
$db_response = mysqli_query($mysql_connection, $db_query);
if (mysqli_num_rows($db_response)) {
  print_line("<h2>".get_text("stars of the day")."</h2>", 2);
  print_line("<div style=\"font-size:0;width:100%;overflow-y:auto\">", 2);
  print_line("<div style=\"text-align:center\">", 3);
  while ($db_row = mysqli_fetch_object($db_response)) print_star_box($db_row, 4);
  print_line("</div>", 3);
  print_line("</div>", 2);
}

/** Get featured movies */
$min_movies_count = 5;
$db_query = "SELECT FLOOR(COUNT(*)/$min_movies_count) AS count FROM movies WHERE status=1";
$db_response = mysqli_query($mysql_connection, $db_query);
$db_row = mysqli_fetch_object($db_response);
$featured_group_count = $db_row->count;
$todays_group_id = $epoch_days % $featured_group_count;

$db_query = "
SELECT id, IFNULL(name_$language, '&ltNo Title&gt') AS name, release_date, duration
FROM movies WHERE id IN (
  SELECT id FROM (
    SELECT ROW_NUMBER() OVER(ORDER BY id) % $featured_group_count AS group_id, id, name_en
    FROM movies WHERE status=1
  ) t1 WHERE group_id=$todays_group_id
) ORDER BY update_timestamp DESC";
$db_response = mysqli_query($mysql_connection, $db_query);
if (mysqli_num_rows($db_response)) {
  print_line("<h2>".get_text("movies of the day")."</h2>", 2);
  while ($db_row = mysqli_fetch_object($db_response)) print_movie_box($db_row, 2);
}

print_line("</div>");

print_page_footer();

?>
