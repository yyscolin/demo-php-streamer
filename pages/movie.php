<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/box-star.php");

function get_mp4s($movie_id) {
  global $PROJ_CONF;
  global $mysql_connection;
  $movie_parts = [];

  /** Try to get the number of parts of this movie from the database */
  $db_query = "SELECT part_id FROM movies_media WHERE movie_id=?";
  $db_statement = $mysql_connection->prepare($db_query);
  $db_statement->bind_param("s", $movie_id);
  $db_statement->execute();
  $db_response = $db_statement->get_result();
  if ($db_response->num_rows > 0) {
    while ($db_row = mysqli_fetch_object($db_response)) {
      array_push($movie_parts, intval($db_row->part_id));
    }
  }

  /** If data is not in database, find files with special naming convention */
  else {
    $movie_title_first_word = get_first_word_of_movie_title($movie_id);
    $file_pattern = $movie_title_first_word."_[0-9].mp4";
    foreach ($PROJ_CONF["MEDIA_DIRS"]["mp4"] as $directory) {
      foreach(["", "/*"] as $subdir) {
        $matching_files = glob($directory.$subdir."/$file_pattern");
        foreach ($matching_files as $file_name) {
          $str_splits = explode(".", $file_name);
          $str_splits = explode("_", $str_splits[count($str_splits) - 2]);
          $part_id = $str_splits[count($str_splits) - 1];
          array_push($movie_parts, intval($part_id));
        }
      }
    }
  }

  $movie_parts = array_unique($movie_parts, SORT_NUMERIC);
  for ($i = 0; $i < count($movie_parts); $i++) $movie_parts[$i] = array(
    "file_path"=>"/media/movie/$movie_id~$movie_parts[$i]",
    "part_id"=>$movie_parts[$i]
  );

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
    array_push($playlist, array(
      "sources"=>[array(
        "src"=>"/media/movie/$movie_id~".$mp4["part_id"],
        "type"=>"video/mp4",
      )],
      "poster"=>"/media/cover/$movie_id",
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
  "<link rel=\"stylesheet\" href=\"/styles/videojs.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/videojs-seek-buttons.css\">",
  "<link rel=\"stylesheet\" href=\"/styles/videojs-mobile-ui.css\">",
  "<title>$movie_data->name - ".$PROJ_CONF["PROJECT_TITLE"]."</title>"
]);?>

  <div id="main-block">
    <h4 style="width:100%"><?=$movie_data->name?></h4><?php

$mp4s = get_mp4s($movie_data->id);
if (count($mp4s) > 0) {?>

    <video class="video-js vjs-big-play-centered">
      <p class="vjs-no-js">
      To view this video please enable JavaScript, and consider upgrading to a web browser that
      <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
      </p>
    </video><?php

} else {?>

    <div id="display-wrapper">
      <img src="<?=$movie_data->img?>" style="height:100%">
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
  </div>
  <script src="/scripts/videojs.min.js"></script>
  <script src="/scripts/videojs-mobile-ui.min.js"></script>
  <script src="/scripts/videojs-playlist.min.js"></script>
  <script src="/scripts/videojs-seek-buttons.min.js"></script>
  <script>
    const videoPlayer = videojs(document.querySelector(`.video-js`), {
      controls: true,
      fluid: true,
      preload: `none`,
      playbackRates: [.5, 1, 1.5, 2],
    })

    videoPlayer.mobileUi()
    videoPlayer.seekButtons(<?=get_seek_options()?>)
    videoPlayer.playlist(<?=get_playlist_json($movie_id, $mp4s)?>)
  </script><?php

print_page_footer();

?>