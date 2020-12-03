<?php

$type = $_GET['type'];
$query = $_GET['query'];

if (!isset($query) || ($type != 'vid' && $type != 'star')) {
    header("HTTP/1.0 400");
    exit();
}

include('../public/search-database.php');

$payload = new stdClass();
$payload->type = $type;
$payload->results = search_database_by_query($type, $query, 5 ,false);

header('Content-type: application/json');
echo json_encode($payload);

?>