<?php

include('public/common.php');
include('public/getInfoById.php');
echo "  <script>const id = '$r->id'</script>
  <link rel='stylesheet' href='/styles/p-vid.css'>
  <link rel='stylesheet' href='/styles/star-box.css'>
  <title>$r->id - Demo PHP Streamer</title>";
include('public/html-mid.html');

echo "
  <div id='main-block'>
    <h4 style='width:100%'>$r->id $r->title</h4>
    <div id='display' value='$r->id'>
      <img id='play-btn' src='/images/play.png' onclick='loadVideo()'>
      <img id='poster' src='/media/covers/$r->id.jpg' onclick='loadVideo()'>
    </div>";

$count = count(glob("media/vids/$r->id*.mp4"));
if ($count > 1) {
  echo "\n\t<div style='width:100%'>";
  for ($i = 1; $i <= $count; $i++)
    echo "\n\t\t<button part='$i' "
    .($i == 1 ? "id='selected'" : "onclick='loadVideo(this)'")
    .">PART $i</button>";
  echo "\n\t</div>";
}
echo "
  <table id='info-table'>
    <tr>
      <td><b>発売日</b></td>
      <td>$r->release_date</td>
    </tr>
    <tr>
      <td><b>収録時間</b></td>
      <td>$r->duration 分鐘</td>
    </tr>
  </table>
  <div id='stars-box'>
    <div>";


/** Get list of stars */
include('public/box-star.php');
$query = "select id, name_f, name_l, name_j, dob, display, count
from stars join (
    select star, count(*) as count from casts
    where vid in (
      select id from vids where status=3
    ) group by star
) as t on stars.id = t.star
where id in (
    select star from casts where vid = '$r->id'
)";
$res = mysqli_query($con, $query);
while ($r = mysqli_fetch_object($res)) {
    print_star_box($r);
}

echo "
    </div>
  </div>
  </div>
  <script src='/scripts/video-player.js'></script>";

include('public/html-tail.html');

?>