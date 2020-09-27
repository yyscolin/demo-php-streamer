<?php

session_start();

include('public/verifyLogin.php');
include('public/html-head.html');
echo "\n  <link rel='stylesheet' href='/styles/poster.css'>";
include('public/html-mid.html');


if ($_GET['id'] == 0) {
    $r = new stdClass();
    $r->id = $_GET['id'];
    $r->name_j = "";
    $r->name_f = "Untitled Stars";
    $r->name_l = "";
    $r->dob = "";
} else {
    include('public/getInfoById.php');
}

$img_src = "/media/stars/$r->id.jpg";
if (!file_exists(".".$img_src)) {
  $img_src = "/media/stars/0.jpg";
}

echo "<div class='flex' style='width: 100vw; height: 32vw; margin: 8vw 0;overflow: hidden;'>
    <img style='z-index: 0; width: 100vw; height: 60vw;' src='/images/frame.png'>
    <div class='flex' style='background-color: grey; margin: 0; width: 50vw; height: 30vw; position: absolute; z-index: -1;'>
        <img src='$img_src' style='width: 25%;'>
        <h1 style='color: white; margin: 0 1vw; vertical-align: top; display: inline-block;'>$r->name_j<br>$r->name_f".($r->name_l?" $r->name_l":"")."<br>$r->dob</h1>
    </div>
</div>";

/** Prepare statement */
if ($_GET['id'] == 0) {
    echo "<p>";
    $query = "select id from vids where id not in (select vid from casts)";
    $res = $con->query($query);
    echo "</p>";
} else {
    $query = "select id from vids where id in (select vid from casts where star = ?) order by release_date desc";
    $stmt = $con->prepare($query);
    $stmt->bind_param('s', $r->id);
    $stmt->execute();
    $res = $stmt->get_result();
}

/** Print boxes */
while ($r = mysqli_fetch_object($res)) include('public/box-vid.php');

include('public/html-tail.html');

?>