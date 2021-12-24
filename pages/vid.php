<?php

function get_mp4s($vid_id) {
  global $con;
  $media_path = $_SERVER['MEDIA_PATH'];
  $mp4s = [];

  foreach (glob("$media_path/vids/*/$vid_id"."_*.mp4") as $mp4) {
    $splits = explode("/", $mp4);
    $subfolder = $splits[count($splits) - 2];
    $file_name = explode(".", $splits[count($splits) - 1])[0];
    list(, $part_no) = explode("_", $file_name);
    array_push($mp4s, array(
      "file_path"=>"/media/vid/$subfolder/$vid_id/$part_no",
      "part_no"=>intval($part_no)
    ));
  }

  if (count($mp4s) == 0) {
    $db_query = "select part from vid_media where vid=? union select part from vid_media_v2 where vid=?";
    $stmt = $con->prepare($db_query);
    $stmt->bind_param('ss', $vid_id, $vid_id);
    $stmt->execute();
    $db_response = $stmt->get_result();
    while ($row = mysqli_fetch_object($db_response)) {
      $part_no = $row->part;
      array_push($mp4s, array(
        "file_path"=>"/media/vid/x/$vid_id/$part_no",
        "part_no"=>intval($part_no)
      ));
    }
  }

  usort($mp4s, function($a, $b) {
    return $a['part_no'] > $b['part_no'];
  });

  return $mp4s;
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/box-star.php");

$id = $_GET["id"];
if (!isset($id)) redirectToHomePage();
$vid = get_vid_from_database($id);
if (!$vid) redirectToHomePage();

print_page_header([
  "<script>const id = '$vid->id'</script>",
  "<link rel='stylesheet' href='/styles/p-vid.css'>",
  "<link rel='stylesheet' href='/styles/star-box.css'>",
  "<title>$vid->id - Demo PHP Streamer</title>"
]);

print_line("<div id='main-block'>");
print_line("<h4 style='width:100%'>$vid->name</h4>", 2);
print_line("<div id='display' value='$vid->id'>", 2);

$mp4s = get_mp4s($vid->id);
if (count($mp4s) > 0) {
  $vid_path = $mp4s[0]['file_path'];
  $onclick = "loadVideo(\"$vid_path\")";
  print_line("<img id='play-btn' src='/images/play.png' onclick='$onclick'>", 3);
  print_line("<img id='poster' src='$vid->img' onclick='$onclick'>", 3);
} else {
  print_line("<img id='poster' src='$vid->img'>", 3);
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
      <td><b>".get_text("release date", 'ucwords')."</b></td>
      <td>$vid->release_date</td>
    </tr>
    <tr>
      <td><b>".get_text("duration", 'ucfirst')."</b></td>
      <td>$vid->duration ".get_text("minutes")."</td>
    </tr>
  </table>
  <div id='stars-box'>
    <div>";


/** Get list of stars */
$db_query = "select id, name_$language as name, count
from entities join (
    select entity, count(*) as count from xref_entities_vids
    where vid in (
      select id from vids where status=1
    ) group by entity
) as t on entities.id = t.entity
where id in (
    select entity from xref_entities_vids
    where vid = '$vid->id' and `is`='star'
) and status=1";
$res = mysqli_query($con, $db_query);
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