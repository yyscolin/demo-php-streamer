<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");

if (
  !isset($_GET["type"]) ||
  !isset($_GET["page-no"]) ||
  !isset($_GET["items-count"]) ||
  !in_array($_GET["type"], ["movie", "star"]) ||
  !is_numeric($_GET["page-no"]) ||
  !is_numeric($_GET["items-count"]) ||
  $_GET["page-no"] < 1 ||
  $_GET["items-count"] < 1
) {
  header("HTTP/1.0 400");
  exit();
}

$type = $_GET["type"];
$items_per_page = $_GET["items-count"];
$limit_start = ($_GET["page-no"] - 1) * $items_per_page;

$db_results = $type == "movie" ? get_movies_from_database() : get_stars_from_database();
$api_response = array_slice($db_results, $limit_start, $items_per_page);
header("Content-type: application/json");
echo json_encode($api_response);

?>