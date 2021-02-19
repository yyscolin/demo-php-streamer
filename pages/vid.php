<?php

function get_mp4s($vid_id) {
  $mp4s = [];
  $sub_paths = ["", "*/"];
  $number_patterns = ["[123456789]", "0[123456789]", "[123456789][0123456789]"];

  foreach ($sub_paths as $sub_path) {
    foreach ($number_patterns as $number_pattern) {
      foreach (glob("../media/vids/$sub_path$vid_id\_$number_pattern.mp4") as $mp4) {
        $splits = explode("/", $mp4);
        $file_name = $splits[count($splits) - 1];
        $file_name = substr($file_name, 0, count($file_name) - 5);

        $splits = explode("_", $file_name);
        $part_no = intval($splits[count($splits) - 1]);
        array_push($mp4s, array(
          file_path=>substr($mp4, 2),
          part_no=>$part_no
        ));
      }
    }
  }

  usort($mp4s, function($a, $b) {
    return $a['part_no'] > $b['part_no'];
  });

  return $mp4s;
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");

$id = $_GET["id"];
if (!isset($id)) redirectToHomePage();
require_once($_SERVER['DOCUMENT_ROOT']."/public/search-database.php");
$r = search_database_by_id('vid', $id);
if (!$r) redirectToHomePage();

print_page_header([
  "<script>const id = '$r->id'</script>",
  "<link rel='stylesheet' href='/styles/p-vid.css'>",
  "<link rel='stylesheet' href='/styles/star-box.css'>",
  "<title>$r->id - Demo PHP Streamer</title>"
]);

print_line("<div id='main-block'>");
print_line("<h4 style='width:100%'>$r->id $r->title</h4>", 2);
print_line("<div id='display' value='$r->id'>", 2);

$mp4s = get_mp4s($r->id);
if (count($mp4s) > 0) {
  $vid_path = $mp4s[0]['file_path'];
  $onclick = "loadVideo(\"$vid_path\")";
  print_line("<img id='play-btn' src='/images/play.png' onclick='$onclick'>", 3);
  print_line("<img id='poster' src='$r->img' onclick='$onclick'>", 3);
} else {
  print_line("<img id='poster' src='$r->img'>", 3);
}

print_line("</div>", 2);

if (count($mp4s) > 1) {
  print_line("<div style='width:100%'>", 2);
  for ($i = 0; $i < count($mp4s); $i++) {
    $id = $i == 0 ? "id='selected'" : "";
    $vid_path = $mp4s[$i]['file_path'];
    $part_no = $mp4s[$i]['part_no'];
    print_line("<button ".$id."onclick='loadVideo(\"$vid_path\", this)'>PART $part_no</button>", 3);
  }
  print_line("</div>", 2);
}

echo "
  <table id='info-table'>
    <tr>
      <td><b>".get_text("release date", ucwords)."</b></td>
      <td>$r->release_date</td>
    </tr>
    <tr>
      <td><b>".get_text("duration", ucfirst)."</b></td>
      <td>$r->duration ".get_text("minutes")."</td>
    </tr>
  </table>
  <div id='stars-box'>
    <div>";


/** Get list of stars */
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-star.php");
$query = "select id, name_f, name_l, name_j, dob, display, count
from stars join (
    select star, count(*) as count from casts
    where vid in (
      select id from vids where status=1
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

print_page_footer();

?>