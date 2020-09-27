<?php

function redirectToHomePage() {
    header("Location: /");
    exit();
}

/** Determine if table == "vids" or "stars" */
$table = explode('/', $_SERVER['REQUEST_URI'])[1];
$table = explode('.', $table)[0];
$table = $table.'s';

if (!isset($_GET['id'])) redirectToHomePage();

include('public/mysql_connections.php');
$stmt = $con->prepare("select * from $table where id = ?");
$stmt->bind_param('s', $_GET['id']);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) redirectToHomePage();

$r = $res->fetch_object();