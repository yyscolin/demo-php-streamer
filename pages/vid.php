<?php

function get_mp4s($vid_id) {
  global $con;
  $media_path = $_SERVER["MEDIA_PATH"];
  $mp4s = [];

  $db_query = "select part_id from vid_media where video_id=?";
  $stmt = $con->prepare($db_query);
  $stmt->bind_param("s", $vid_id);
  $stmt->execute();
  $db_response = $stmt->get_result();
  while ($row = mysqli_fetch_object($db_response)) {
    $part_id = $row->part_id;
    array_push($mp4s, array(
      "file_path"=>"/media/vid/$vid_id~$part_id",
      "part_id"=>intval($part_id)
    ));
  }

  usort($mp4s, function($a, $b) {
    return $a["part_id"] > $b["part_id"];
  });

  return $mp4s;
}

require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/box-star.php");

$id = $_GET["id"];
if (!isset($id)) redirectToHomePage();
$vid = get_vid_from_database($id);
if (!$vid) redirectToHomePage();

print_page_header([
  "<link rel=\"stylesheet\" href=\"/styles/page-vid.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/star-box.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/videojs.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/videojs-seek-buttons.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/videojs-mobile-ui.css\">",
  "<title>$vid->id - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);?>

  <div id="main-block">
    <h4 style="width:100%"><?=$vid->name?></h4><?php

$mp4s = get_mp4s($vid->id);
if (count($mp4s) > 0) {?>

    <video class="video-js vjs-big-play-centered">
      <p class="vjs-no-js">
      To view this video please enable JavaScript, and consider upgrading to a web browser that
      <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
      </p>
    </video><?php

} else {?>

    <div id="display-wrapper">
      <img src="<?=$vid->img?>" style="height:100%">
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
        <td><?=$vid->release_date?></td>
      </tr>
      <tr>
        <td><b><?=get_text("duration", "ucfirst")?></b></td>
        <td><?=$vid->duration." ".get_text("minutes")?></td>
      </tr>
    </table>
    <div id="stars-box">
      <div><?php

/** Get list of stars */
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
  where vid = '$vid->id' and `is`='star'
) and status=1";
$res = mysqli_query($con, $db_query);
while ($r = mysqli_fetch_object($res)) {
  print_star_box($r);
}

?>

      </div>
    </div>
  </div>
  <script src="/scripts/videojs.min.js"></script>
  <script src="/scripts/videojs-mobile-ui.min.js"></script>
  <script src="/scripts/videojs-playlist.min.js"></script>
  <script src="/scripts/videojs-seek-buttons.min.js"></script>
  <!-- <script src="/scripts/video-player.js"></script> -->
  <script>
    const videoId = window.location.pathname.split(`/`)[2]
    const videoPlayer = videojs(document.querySelector(`.video-js`), {
      controls: true,
      fluid: true,
      preload: true,
      playbackRates: [.5, 1, 1.5, 2],
    })

    videoPlayer.mobileUi()
    videoPlayer.seekButtons({
      back: 10,
      forward: 10,
    })
    videoPlayer.playlist([<?php

for ($i = 0; $i < count($mp4s); $i++) {?>

      {
        sources: [{
          src: `/media/vid/${videoId}~<?=$mp4s[$i]["part_id"]?>`,
          type: `video/mp4`
        }],
        poster: `/media/cover/${videoId}`,
      },<?php

}?>

    ])
  </script><?php

print_page_footer();

?>