<?php

$type = $_GET['type'];
$query = $_GET['query'];

if (!isset($query) || ($type != 'vid' && $type != 'star')) {
  header("HTTP/1.0 400");
  exit();
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/search-database.php");

$payload = new stdClass();
$payload->type = $type;
$payload->results = search_database_by_query($type, $query, 5);

header('Content-type: application/json');
echo json_encode($payload);

?>