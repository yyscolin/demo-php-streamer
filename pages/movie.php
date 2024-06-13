<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/box-star.php");

function get_mp4s($movie_id) {
  global $mysql_connection;

  $doc_root = $_SERVER["DOCUMENT_ROOT"];
  $movie_parts = [];

  /** Try to get the number of parts of this movie from the database */
  $db_query = "SELECT part_id, file_name FROM movies_media WHERE movie_id=?";
  $db_statement = $mysql_connection->prepare($db_query);
  $db_statement->bind_param("s", $movie_id);
  $db_statement->execute();
  $db_response = $db_statement->get_result();
  if ($db_response->num_rows > 0) {
    while ($db_row = mysqli_fetch_object($db_response)) {
      $file_name = $db_row->file_name;
      $part_id = intval($db_row->part_id);
      array_push($movie_parts, array(
        "file_path"=>"/media/video_files/$file_name",
        "part_id"=>$part_id,
      ));
    }
    return $movie_parts;
  }

  /** Find files with special naming convention */
  $file_exts = ["m3u8", "webm", "mp4"];
  $movie_title_first_word = get_first_word_of_movie_title($movie_id);
  foreach ($file_exts as $ext) {
    $file_pattern1 = "^$movie_title_first_word~[0-9]+\.$ext$";
    $file_pattern2 = "^$movie_id~[0-9]+\.$ext$";
    $matching_files = glob("$doc_root/media/video_files/*");
    foreach ($matching_files as $file_name) {
      $file_splits = explode("/", $file_name);
      $file_name = array_pop($file_splits);
      $is_match_pattern1 = preg_match("/$file_pattern1/", $file_name);
      $is_match_pattern2 = preg_match("/$file_pattern2/", $file_name);
      if ($is_match_pattern1 || $is_match_pattern2) {
        $file_stem = explode(".", $file_name)[0];
        $part_id = explode("~", $file_stem)[1];
        array_push($movie_parts, array(
          "file_path"=>"/media/video_files/$file_name",
          "part_id"=>intval($part_id),
        ));
      }
    }

    if (count($movie_parts) > 0) break;
  }
  return $movie_parts;
}

function get_seek_options() {
  global $PROJ_CONF;
  $options = array("back"=>10, "forward"=>10);
  if ($PROJ_CONF["SEEK_BTN_RIGHT"]) {
    $options["backIndex"] = 11;
    $options["forwardIndex"] = 11;
  }
  return json_encode($options);
}

function get_playlist_json($movie_id, $mp4s) {
  $playlist = [];
  foreach ($mp4s as $mp4) {
    $file_path = $mp4["file_path"];
    $file_splits = explode(".", $file_path);
    $file_ext = array_pop($file_splits);

    $is_m3u8 = $file_ext == "m3u8";
    $file_type = $is_m3u8 ? "application/x-mpegURL" : "video/$file_ext";

    $poster = "/media/movie_covers/$movie_id.jpg";
    if (!file_exists($_SERVER["DOCUMENT_ROOT"].$poster)) {
      $poster = "/images/default-cover.jpg";
    }

    array_push($playlist, array(
      "sources"=>[array(
        "src"=>$mp4["file_path"],
        "type"=>$file_type,
      )],
      "poster"=>$poster,
    ));
  }
  return json_encode($playlist);
}

function print_star_boxes($movie_id, $language, $mysql_connection) {
  $db_query = "
  SELECT id, name_$language AS name, count
  FROM stars JOIN (
    SELECT star_id, count(*) AS count FROM movies_stars
    WHERE status=1
    AND movie_id IN (
      SELECT id FROM movies WHERE status=1
    )
    GROUP BY star_id
  ) AS t ON stars.id=t.star_id
  WHERE status=1
  AND id IN (
    SELECT star_id FROM movies_stars
    WHERE status=1
    AND movie_id=?
  )";
  $db_statement = $mysql_connection->prepare($db_query);
  $db_statement->bind_param("s", $movie_id);
  $db_statement->execute();
  $db_response = $db_statement->get_result();
  while ($db_row = mysqli_fetch_object($db_response))
    print_star_box($db_row, 4);
}

$movie_id = $_GET["id"];
if (!isset($movie_id)) redirectToHomePage();
$movie_data = get_movie_from_database($movie_id);
if (!$movie_data) redirectToHomePage();

print_page_header([
  "<link rel=\"stylesheet\" href=\"/styles/page-movie.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/star-box.css\">",
  "<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/video.js@7.21.6/dist/video-js.min.css\">",
  // "<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/videojs-mobile-ui@1.1.1/dist/videojs-mobile-ui.min.css\">",
  // "<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/videojs-seek-buttons@3.0.1/dist/videojs-seek-buttons.min.css\">",
  "<title>$movie_data->name - ".$PROJ_CONF["PROJECT_TITLE"]."</title>"
]);?>

  <div id="main-block">
    <h4 style="width:100%"><?=$movie_data->name?></h4><?php

$mp4s = get_mp4s($movie_data->id);
if (count($mp4s) > 0) {?>

    <video
      id="video-player"
      class="video-js vjs-big-play-centered"
      nativeControlsForTouch
    >
      <p class="vjs-no-js">
      To view this video please enable JavaScript, and consider upgrading to a web browser that
      <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
      </p>
    </video>
    <script src="https://cdn.jsdelivr.net/npm/video.js@7.21.6/dist/video.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@videojs/http-streaming@3.10.0/dist/videojs-http-streaming.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/videojs-mobile-ui@1.1.1/dist/videojs-mobile-ui.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/videojs-playlist@5.1.2/dist/videojs-playlist.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/videojs-seek-buttons@3.0.1/dist/videojs-seek-buttons.min.js"></script> -->
    <script>
      const playlist = <?=get_playlist_json($movie_id, $mp4s)?>;
      const videoPlayer = videojs(document.querySelector(`.video-js`), {
        controls: true,
        fluid: true,
        preload: `none`,
        playbackRates: [.5, 1, 1.5, 2],
      })
      // videoPlayer.mobileUi()
      // videoPlayer.seekButtons(<?=get_seek_options()?>)
      videoPlayer.playlist(playlist)
    </script><?php

} else {
  $poster = "/media/movie_covers/$movie_id.jpg";
  if (!file_exists($_SERVER["DOCUMENT_ROOT"].$poster)) {
    $poster = "/images/default-cover.jpg";
  }?>

    <div id="display-wrapper">
      <img src="<?=$poster?>" style="height:100%">
      <p id="display-error-message">Error: No video file(s) found</p>
    </div>
    <?php

}

if (count($mp4s) > 1) {?>

    <div style="width:100%"><?php

  for ($i = 0; $i < count($mp4s); $i++) {?>

      <button onclick="loadMoviePart(<?=$i?>)">PART <?=$mp4s[$i]["part_id"]?></button><?php

  }?>

      <script>
        $(`button`).first().prop(`disabled`, true)

        function loadMoviePart(moviePart) {
          videoPlayer.playlist.currentItem(moviePart)
          videoPlayer.play()

          $(`button:not([onclick="loadMoviePart(${moviePart})"])`).prop(`disabled`, false)
          $(`button[onclick="loadMoviePart(${moviePart})"]`).prop(`disabled`, true)
        }
      </script>
    </div><?php

}?>

    <table id="info-table">
      <tr>
        <td><b><?=get_text("release date", "ucwords")?></b></td>
        <td><?=$movie_data->release_date?></td>
      </tr>
      <tr>
        <td><b><?=get_text("duration", "ucfirst")?></b></td>
        <td><?=$movie_data->duration." ".get_text("minutes")?></td>
      </tr>
    </table>
    <div id="stars-box">
      <div><?=print_star_boxes($movie_id, $language, $mysql_connection)?>

      </div>
    </div>
  </div><?php

print_page_footer();

?>
