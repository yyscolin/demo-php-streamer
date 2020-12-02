<?php

if (!isset($_GET['query']) || ($_GET['type'] != 'vid' && $_GET['type'] != 'star')) {
    header("HTTP/1.0 400");
    exit();
}

include('../public/search-database.php');

$payload = new stdClass();
$payload->type = $_GET['type'];
$payload->results = search_database($_GET['type'], $_GET['query']);

header('Content-type: application/json');
echo json_encode($payload);

?>