<?php

session_start();

if (!isset($_GET['query']) || ($_GET['type'] != 'vid' && $_GET['type'] != 'star')) {
    header("HTTP/1.0 400");
    exit();
}

include('../public/mysql_connections.php');
$search = $_GET['query'].'%';
if ($_GET['type'] == 'vid') {
    $query = "select id, (case when (char_length(title) < 12) then title else concat(left(title, 9), '...') end) as name from vids where id like ? order by timestamp desc limit 5";
    $stmt = $con->prepare($query);
    $stmt->bind_param('s', $search);
} else {
    $query = "select id, name_j as name from stars where concat(name_f, ' ', name_l) like ? or concat(name_l, ' ', name_f) like ? order by name_f limit 5";
    $stmt = $con->prepare($query);
    $stmt->bind_param('ss', $search, $search);
}
$stmt->execute();
$res = $stmt->get_result();
$response = new stdClass();
$response->type = $_GET['type'];
$response->queue = (int)$_GET['queue'];
$response->detail = $_SESSION['auth'];
$response->results = [];
while ($r = $res->fetch_object())
    array_push($response->results, $r);

header('Content-type: application/json');
echo json_encode($response);

?>