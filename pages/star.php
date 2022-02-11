<?php

function print_star_potrait($star, $star_name) {
  print_line("<div id='s-pot' class='flex'>");
  print_line("<img class='frame' src='/images/frame.png'>", 2);
  print_line("<div class='flex inner' style='background-color:grey;z-index:-2'></div>", 2);
  print_line("<div class='flex inner'>", 2);
  print_line("<img src='$star->img'>", 3);
  print_line("<div class='info'>", 3);
  print_line("<p class='text-ellipsis'>$star_name</p>", 4);
  foreach ($star->attributes as $star_attribute)
    print_line("<p class='text-ellipsis'>".$star_attribute->key.": ".$star_attribute->value."</p>", 4);
  print_line("</div>", 3);
  print_line("</div>", 2);
  print_line("</div>");
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-movie.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");

$id = $_GET["id"];
if (!isset($id)) redirectToHomePage();

if ($id == 0) {
  $star_name = "Others";
} else {
  $star = get_star_from_database($id);
  if (!$star) redirectToHomePage();
  $star_name = $star->name;
}

print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<link rel='stylesheet' href='/styles/star-potrait.css'>",
  "<title>$star_name - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);

print_line("<div id='main-block' style='margin-top:0;overflow:hidden'>");

if ($id == 0) {
  $db_query = "
  SELECT id, name_$language AS name
  FROM movies WHERE id NOT IN (
    SELECT movie_id FROM movies_stars
  ) AND status=1 ORDER BY release_date DESC";
  $db_response = $mysql_connection->query($db_query);
} else {
  print_star_potrait($star, $star_name);

  $db_query = "
  SELECT id, name_$language AS name
  FROM movies WHERE id IN (
    SELECT movie_id FROM movies_stars WHERE star_id=?
  ) AND status=1 ORDER BY release_date DESC";
  $db_statement = $mysql_connection->prepare($db_query);
  $db_statement->bind_param("s", $star->id);
  $db_statement->execute();
  $db_response = $db_statement->get_result();
}

while ($db_row = mysqli_fetch_object($db_response)) print_movie_box($db_row, 2);
print_line("</div>");

print_page_footer();

?>