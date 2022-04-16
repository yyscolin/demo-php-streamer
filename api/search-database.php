<?php

$results_count = 5;

$type = $_GET['type'];
$search_query = $_GET['query'];

if (!isset($type) || !isset($search_query) || !in_array($type, ['movie', 'star'])) {
  header("HTTP/1.0 400");
  exit();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");

$payload = new stdClass();
$payload->type = $type;
$payload->results = search_database_by_query($type, $search_query, $results_count);

header('Content-type: application/json');
echo json_encode($payload);

?>