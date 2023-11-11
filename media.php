<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/config.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/mysql_connections.php");

$project_root = $_SERVER["DOCUMENT_ROOT"];

function get_movie_file_path($movie_id, $part_id) {
  global $mysql_connection;
  $db_query = "SELECT file_name FROM movies_media
      WHERE movie_id=? AND part_id=?";
  $db_statement = $mysql_connection->prepare($db_query);
  $db_statement->bind_param("ss", $movie_id, $part_id);
  $db_statement->execute();
  $db_response = $db_statement->get_result();
  if ($db_response->num_rows < 1) {
    return null;
  }

  $db_row = mysqli_fetch_object($db_response);
  $file_name = $db_row->file_name;
  $file_path = find_file("mp4", $file_name);
  return $file_path;
}

function find_file($file_type, $file_name) {
  global $PROJ_CONF;
  foreach ($PROJ_CONF["MEDIA_DIRS"][$file_type] as $directory) {
    if (file_exists("$directory/$file_name")) return "$directory/$file_name";

    $matching_files = glob("$directory/*/$file_name");
    if (count($matching_files)) return $matching_files[0];
  }
}

function send_headers($file_size) {
  $content_length = $file_size;
  $byte_start = 0;
  $byte_end = $file_size - 1;

  header('Content-type: video/mp4');
  header("Accept-Ranges: 0-$content_length");
  if (isset($_SERVER['HTTP_RANGE'])) {
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    if (strpos($range, ',') !== false) {
      header('HTTP/1.1 416 Requested Range Not Satisfiable');
      header("Content-Range: bytes $byte_start-$byte_end/$file_size");
      exit;
    }
    if ($range == '-') {
      $c_start = $file_size - substr($range, 1);
      $c_end = $byte_end;
    } else {
      $range = explode('-', $range);
      $c_start = $range[0];
      $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $file_size;
    }
    $c_end = $c_end > $byte_end ? $byte_end : $c_end;
    if ($c_start > $c_end || $c_start > $file_size - 1 || $c_end >= $file_size) {
      header('HTTP/1.1 416 Requested Range Not Satisfiable');
      header("Content-Range: bytes $byte_start-$byte_end/$file_size");
      exit;
    }
    $byte_start = $c_start;
    $byte_end = $c_end;
    $content_length = $byte_end - $byte_start + 1;
    header('HTTP/1.1 206 Partial Content');
  }
  header("Content-Range: bytes $byte_start-$byte_end/$file_size");
  header("Content-Length: $content_length");

  return array($content_length, $byte_start, $byte_end);
}

function send_media_file($file_path) {
  set_time_limit(0);

  $buffer_size = 4 * 1024;
  $file_size = filesize($file_path);
  list($content_length, $byte_start, $byte_end) = send_headers($file_size);
  $read_stream = fopen($file_path, "rb");
  if ($byte_start > 0) fseek($read_stream, $byte_start);

  while(!feof($read_stream) && ($p = ftell($read_stream)) <= $byte_end) {
    if ($p + $buffer_size > $byte_end) $buffer_size = $byte_end - $p + 1;
    echo fread($read_stream, $buffer_size);
    flush();
  }
  fclose($read_stream);
}

$file_type= $_GET["type"];

$is_image_file = in_array($file_type, ["cover", "star"]);
if ($is_image_file) {
  header("Content-Type:image/jpeg");

  $file_name = $_GET["file"].".jpg";
  $file_path = find_file($file_type, $file_name);

  if (!file_exists($file_path))
    $file_path = "$project_root/images/default-".$file_type.".jpg";

  readfile($file_path);
  exit();
}

$is_movie_file = $file_type == "movie";
if (!$is_movie_file) {
  http_response_code(400);
  exit();
}

list($movie_id, $part_id) = explode("~", $_GET["file"]);

/**
 * Special mp4 file naming convention (not recommended)
 * <first word of movie title>_<part number>.mp4
 * Example:
 * Movie title - What a beautiful day
 * Part number - 1
 * File name - what_1.mp4
 */
$movie_title_first_word = get_first_word_of_movie_title($movie_id);
$file_path = find_file("mp4", "$movie_title_first_word"."_$part_id.mp4");
if ($file_path) {
  send_media_file($file_path);
  exit();
}

/** Alternatively, the names of the mp4 files can be found in database */
$file_path = get_movie_file_path($movie_id, $part_id);
if ($file_path) {
  send_media_file($file_path);
  exit();
}

http_response_code(404);
