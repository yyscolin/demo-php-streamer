<?php
session_start();

include("public/verifyLogin.php");
include('public/html-head.html');
echo "  <link rel='stylesheet' href='/styles/poster.css'>
  <link rel='stylesheet' href='/styles/star-box.css'>
  <title>Demo PHP Streamer</title>";
include('public/html-mid.html');

/** establish mysql connection */
require('public/mysql_connections.php');

/** Ger random stars */
$randCount = 5;
echo "<h2>Random Stars</h2>"
    ."<div style='width:95vw;overflow-y:auto'><div style='text-align:center'>";
$dbQuery = "select id, coalesce(concat(name_f, ' ', name_l), name_f) as name_e, name_j, dob, coalesce(count, 0) as count, display from stars join (select star, count(star) as count from casts group by star) as t on stars.id = t.star  where display = 1 union select 0, 'Others', null, null, (select count(id) from (select id from vids where id not in (select vid from casts) group by id) as t), null order by rand() limit 5";
$dbResponse = mysqli_query($con, $dbQuery);
while ($r = mysqli_fetch_object($dbResponse)) {
    $img_src = "/media/stars/$r->id.jpg";
    if (!file_exists(".".$img_src)) {
      $img_src = "/media/stars/0.jpg";
    }

    echo "
    <div class='star-box'>
        <img onclick='window.location.href=\"/star/$r->id\"' src='$img_src'>
        <p>$r->name_j</p>
        <p>$r->name_e</p>
        <p>$r->dob</p>
        <a href='/star/$r->id'>$r->count Videos</a>
    </div>";
}
echo "</div></div>";

/** Get random vids */
$randCount = 5;
echo "<h2>Random Videos</h2>";
$dbQuery = "select id from vids where status = 3 order by rand() limit $randCount";
$dbResponse = mysqli_query($con, $dbQuery);
while ($r = mysqli_fetch_object($dbResponse)) {
    include('public/box-vid.php');
}
    
include('public/html-tail.html');
?>
