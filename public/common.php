<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/public/mysql_connections.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/languages.php");

function redirectToHomePage() {
  header("Location: /");
  exit();
}

function get_first_word_of_movie_title($movie_id) {
  global $mysql_connection;
  $db_query = "SELECT name_en FROM movies WHERE id=?";
  $db_statement = $mysql_connection->prepare($db_query);
  $db_statement->bind_param("s", $movie_id);
  $db_statement->execute();
  $db_response = $db_statement->get_result();
  $db_row = mysqli_fetch_object($db_response);
  $movie_title = $db_row->name_en;
  $movie_title_first_word = explode(" ", $movie_title)[0];
  return $movie_title_first_word;
}

function get_stars_from_database() {
  global $mysql_connection;
  global $language;

  $stars = [];
  $db_query = "
  SELECT id, IFNULL(name_$language, '&ltNo Name&gt') AS name,
  IFNULL(t2.count, 0) AS count, latest_release_date FROM (
    SELECT stars.*, MAX(release_date) AS latest_release_date FROM stars
    LEFT JOIN (
      SELECT * from movies_stars WHERE status=1
    ) t1 ON stars.id=t1.star_id
    LEFT JOIN (
      SELECT * FROM movies WHERE status=1
    ) t2 ON t2.id=t1.movie_id
    GROUP BY stars.id
  ) t1 LEFT JOIN (
    SELECT star_id, count(*) AS count
    FROM movies_stars
    WHERE status=1
    AND movie_id IN (
      SELECT id FROM movies WHERE status=1
    ) GROUP BY star_id
  ) t2 ON t1.id=t2.star_id
  WHERE status=1
  ORDER BY latest_release_date DESC";
  $db_response = mysqli_query($mysql_connection, $db_query);
  while ($db_row = mysqli_fetch_object($db_response)) {
    $db_row->attributes = [];
    $db_row->img = "/media/profile_pics/$db_row->id.jpg";
    if (!file_exists($_SERVER["DOCUMENT_ROOT"].$db_row->img)) {
      $db_row->img = "/images/default-star.jpg";
    }
    $stars[] = $db_row;
  }

  $db_query = "
  SELECT star_id, value, name_$language AS name, format_$language AS format
  FROM stars_attributes JOIN attributes ON attribute_id=attributes.id";
  $db_response = mysqli_query($mysql_connection, $db_query);
  while ($db_row = mysqli_fetch_object($db_response)) {
    $attribute_value = $db_row->format;
    $sub_values = explode(" ", $db_row->value);
    for ($i = 1; $i <= count($sub_values); $i++)
      $attribute_value = str_replace("%$i%", $sub_values[$i-1], $attribute_value);
    foreach ($stars as $star) if ($star->id == $db_row->star_id) {
      array_push($star->attributes, (object) array(
        "key"=>$db_row->name,
        "value"=>$attribute_value
      ));
      break;
    }
  }

  return $stars;
}

function get_star_from_database($star_id) {
  $stars = get_stars_from_database();
  foreach ($stars as $star)
    if ($star->id == $star_id) return $star;
  return null;
}

function get_movies_from_database() {
  global $mysql_connection;
  global $language;

  $movies = [];
  $db_query = "
  SELECT
    id,
    IFNULL(name_$language, '&ltNo Title&gt') AS name,
    release_date,
    duration,
    IFNULL(img_src, '/images/default-cover.jpg') AS img
  FROM movies
  WHERE status=1
  ORDER BY update_timestamp DESC";
  $db_statement = $mysql_connection->prepare($db_query);
  $db_statement->execute();
  $db_response = $db_statement->get_result();
  while ($db_row = $db_response->fetch_object()) {
    $movies[] = $db_row;
  }
  return $movies;
}

function get_movie_from_database($movie_id) {
  $movies = get_movies_from_database();
  foreach ($movies as $movie)
    if ($movie->id == $movie_id) return $movie;
  return null;
}

function print_line($line, $indentation_level=1) {
  echo "\n";
  for ($i = 0 ; $i < $indentation_level; $i++) echo "  ";
  echo $line;
}

function print_page_header($head_items=[]) {?><!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <meta http-equiv='X-UA-Compatible' content='ie=edge'>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="/scripts/main.js" defer></script>
  <link rel="stylesheet" href="/styles/main.css">
  <link rel="stylesheet" href="/styles/banner.css"><?php

  foreach ($head_items as $i) {
    if ($i) print_line($i);
  }
?>

</head>
<body>
  <nav id='banner'>
    <button id="menu-button" onClick="$(`body`).toggleClass(`menu-active`)">☰</button>
    <a id="banner-icon" href="/" style="margin: auto">
      <img src="/banner.png" style="height: 70px" title="Go to homepage"/>
    </a>
    <div id="menu-bar">
      <a href="/pages/stars.php"><?=get_text("stars", "strtoupper")?></a>
      <a href="/pages/movies.php"><?=get_text("movies", "strtoupper")?></a>
      <a class="short-banner-item" href="/search.php">SEARCH</a>
      <form class="long-banner-item" action="/search.php" style="margin: 0 32px">
        <select name="type">
          <option value="star"><?=get_text("stars", "strtoupper")?></option>
          <option value="movie"><?=get_text("movies", "strtoupper")?></option>
        </select>
        <input type="search" name="query" placeholder="<?=get_text("search", "ucfirst")?>"/>
        <button type="submit"><?=get_text("go", "strtoupper")?></button>
      </form>
      <select
        id="lang-select"
        onchange="setLanguange(this.value)"
        style="color: black; margin: 20px; width: 90px;"
      >
        <option value="en">English</option>
        <option value="jp">日本語</option>
      </select>
    </div>
  </nav><?php
}

function print_page_footer() {
  print_line("</body>", 0);
  print_line("</html>", 0);
}

session_start();

/** Verify Login */
if ($_SERVER["ACCESS_PASSWORD"] && !$_SESSION['auth']) {
  require_once($_SERVER["DOCUMENT_ROOT"]."/public/login.html");
  exit();
}

$language = isset($_COOKIE['language']) ? $_COOKIE['language'] : "en";
