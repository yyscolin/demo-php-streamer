<?php

function get_mp4s($video_id) {
  global $con;
  $media_path = $_SERVER["MEDIA_PATH"];
  $mp4s = [];

  $db_query = "select part_id from vid_media where video_id=?";
  $stmt = $con->prepare($db_query);
  $stmt->bind_param("s", $video_id);
  $stmt->execute();
  $db_response = $stmt->get_result();
  while ($row = mysqli_fetch_object($db_response)) {
    $part_id = $row->part_id;
    array_push($mp4s, array(
      "file_path"=>"/media/vid/$video_id~$part_id",
      "part_id"=>intval($part_id)
    ));
  }

  usort($mp4s, function($a, $b) {
    return $a["part_id"] > $b["part_id"];
  });

  return $mp4s;
}

function get_seek_options() {
  $options = array("back"=>10, "forward"=>10);
  if ($_SERVER["SEEK_BTN_RIGHT"]) {
    $options["backIndex"] = 11;
    $options["forwardIndex"] = 11;
  }
  return json_encode($options);
}

function get_playlist_json($video_id, $mp4s) {
  $playlist = [];
  foreach ($mp4s as $mp4) {
    array_push($playlist, array(
      "sources"=>[array(
        "src"=>"/media/vid/$video_id~".$mp4["part_id"],
        "type"=>"video/mp4",
      )],
      "poster"=>"/media/cover/$video_id",
    ));
  }
  return json_encode($playlist);
}

function print_star_boxes($video_id, $language, $con) {
  $db_query = "
  select id, name_$language as name, count
  from entities join (
    select entity, count(*) as count from xref_entities_vids
    where vid in (
      select id from vids where status=1
    ) group by entity
  ) as t on entities.id = t.entity
  where id in (
    select entity from xref_entities_vids
    where vid = '$video_id' and `is`='star'
  ) and status=1";
  $db_response = mysqli_query($con, $db_query);
  while ($db_row = mysqli_fetch_object($db_response))
    print_star_box($db_row);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/box-star.php");

$video_id = $_GET["id"];
if (!isset($video_id)) redirectToHomePage();
$video_data = get_vid_from_database($video_id);
if (!$video_data) redirectToHomePage();

print_page_header([
  "<link rel=\"stylesheet\" href=\"/styles/page-vid.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/star-box.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/videojs.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/videojs-seek-buttons.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/videojs-mobile-ui.css\">",
  "<title>$video_id - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);?>

  <div id="main-block">
    <h4 style="width:100%"><?=$video_data->name?></h4><?php

$mp4s = get_mp4s($video_data->id);
if (count($mp4s) > 0) {?>

    <video class="video-js vjs-big-play-centered">
      <p class="vjs-no-js">
      To view this video please enable JavaScript, and consider upgrading to a web browser that
      <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
      </p>
    </video><?php

} else {?>

    <div id="display-wrapper">
      <img src="<?=$video_data->img?>" style="height:100%">
      <p id="display-error-message">Error: No video file(s) found</p>
    </div>
    <?php

}

if (count($mp4s) > 1) {?>

    <div style="width:100%"><?php

  for ($i = 0; $i < count($mp4s); $i++) {?>

      <button onclick="loadVideoPart(<?=$i?>)">PART <?=$mp4s[$i]["part_id"]?></button><?php

  }?>

      <script>
        $(`button`).first().prop(`disabled`, true)

        function loadVideoPart(videoPart) {
          videoPlayer.playlist.currentItem(videoPart)
          videoPlayer.play()

          $(`button:not([onclick="loadVideoPart(${videoPart})"])`).prop(`disabled`, false)
          $(`button[onclick="loadVideoPart(${videoPart})"]`).prop(`disabled`, true)
        }
      </script>
    </div><?php

}?>

    <table id="info-table">
      <tr>
        <td><b><?=get_text("release date", "ucwords")?></b></td>
        <td><?=$video_data->release_date?></td>
      </tr>
      <tr>
        <td><b><?=get_text("duration", "ucfirst")?></b></td>
        <td><?=$video_data->duration." ".get_text("minutes")?></td>
      </tr>
    </table>
    <div id="stars-box">
      <div><?=print_star_boxes($video_id, $language, $con)?>

      </div>
    </div>
  </div>
  <script src="/scripts/videojs.min.js"></script>
  <script src="/scripts/videojs-mobile-ui.min.js"></script>
  <script src="/scripts/videojs-playlist.min.js"></script>
  <script src="/scripts/videojs-seek-buttons.min.js"></script>
  <script>
    const videoId = window.location.pathname.split(`/`)[2]
    const videoPlayer = videojs(document.querySelector(`.video-js`), {
      controls: true,
      fluid: true,
      preload: true,
      playbackRates: [.5, 1, 1.5, 2],
    })

    videoPlayer.mobileUi()
    videoPlayer.seekButtons(<?=get_seek_options()?>)
    videoPlayer.playlist(<?=get_playlist_json($video_id, $mp4s)?>)
  </script><?php

print_page_footer();

?>