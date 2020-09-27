<?php

session_start();

include("public/verifyLogin.php");
include('public/mysql_connections.php');
include('public/html-head.html');
echo "
    <link rel='stylesheet' href='/styles/poster.css'>";
include('public/html-mid.html');

/** Determine page number */
$page = (int)$_GET['page'] != 0 ? $_GET['page'] : 1;
$itemNo = 10;
$limitStart = ($page-1)*$itemNo;

/** Determine query to use */ 
$query = "select * from vids where status=3 order by modify_timestamp desc";

/** Print boxes */
$res = mysqli_query($con, "$query limit $limitStart, $itemNo");
while ($r = mysqli_fetch_object($res)) include('public/box-vid.php');

$subHref = '/vids';

include('public/nav-pages.php');
include('public/html-tail.html');

?>