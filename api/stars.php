<?php

if (!$_POST['name_f']) {
    header("HTTP/1.0 400");
    exit();
}

include('../public/mysql_connections.php');

$name_f = $_POST['name_f'];
$name_l = $_POST['name_l'] ? $_POST['name_l'] : null;
$name_j = $_POST['name_j'] ? $_POST['name_j'] : null;
$dob = $_POST['dob'] ? $_POST['dob'] : null;

$query = "insert into stars (name_l, name_f, name_j, dob) values (?, ?, ?, ?)";
$stmt = $con->prepare($query);
$stmt->bind_param('ssss', $name_l, $name_f, $name_j, $dob);
$stmt->execute();
echo $stmt->insert_id;
$stmt->close();

?>