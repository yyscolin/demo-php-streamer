<?php

if (!$_POST['star'] || !$_POST['vid']) {
    header("HTTP/1.0 400");
    exit();
}

include('../public/mysql_connections.php');

$star = $_POST['star'];
$vid = $_POST['vid'];

$query = "insert into casts (star, vid) values (?, ?)";
$stmt = $con->prepare($query);
$stmt->bind_param('ss', $star, $vid);
$stmt->execute();
$stmt->close();

?>