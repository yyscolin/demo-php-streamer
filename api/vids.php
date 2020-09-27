<?php

if (!$_POST['id']) {
    header("HTTP/1.0 400");
    exit();
}

include('../public/mysql_connections.php');

$id = $_POST['id'];
$title = $_POST['title'] ? $_POST['title'] : null;
$release_date = $_POST['release_date'] ? $_POST['release_date'] : null;
$duration = $_POST['duration'] ? $_POST['duration'] : null;

$query = "insert into vids (id, title, release_date, duration) values (?, ?, ?, ?)";
$stmt = $con->prepare($query);
$stmt->bind_param('ssss', $id, $title, $release_date, $duration);
$stmt->execute();
$stmt->close();

?>