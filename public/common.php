<?php

function redirectToHomePage() {
  header("Location: /");
  exit();
}

function get_stars_from_database() {
  global $mysql_connection;
  global $language;

  // $metadata = [];
  // $db_query = "SELECT * FROM stars_attributes";
  // $db_response = mysqli_query($mysql_connection, $db_query);
  // while ($db_row = mysqli_fetch_object($db_response)) $metadata[] = $db_row;

  $stars = [];
  $db_query = "
  SELECT id, IFNULL(name_$language, '&ltNo Name&gt') AS name,
  IFNULL(t2.count, 0) AS count, latest_release_date FROM (
    SELECT stars.*, MAX(release_date) AS latest_release_date FROM stars
    LEFT JOIN movies_stars ON stars.id=movies_stars.star_id
    LEFT JOIN movies ON movies.id=movies_stars.movie_id
    GROUP BY stars.id
  ) t1 LEFT JOIN (
    SELECT star_id, count(*) AS count
    FROM movies_stars WHERE movie_id IN (
      SELECT id FROM movies WHERE status=1
    ) GROUP BY star_id
  ) t2 ON t1.id=t2.star_id
  WHERE status=1
  ORDER BY latest_release_date DESC";
  $db_response = mysqli_query($mysql_connection, $db_query);
  while ($db_row = mysqli_fetch_object($db_response)) {
    $db_row->img = "/media/star/$db_row->id";

    // foreach($metadata as $x) if ($x->id == $db_row->id) {
    //   $attribute = $x->attribute;
    //   $db_row->$attribute = $x->value;
    // }

    $stars[] = $db_row;
  }
  return $stars;
}

function get_star_from_database($star_id) {
  $stars = get_stars_from_database();
  foreach ($stars as $star)
    if ($star->id == $star_id) return $star;
  return null;
}

function get_movies_from_database() {
  global $mysql_connection;
  global $language;

  $movies = [];
  $db_query = "
  SELECT id, IFNULL(name_$language, '&ltNo Title&gt') AS name, release_date, duration
  FROM movies ORDER BY db_timestamp DESC";
  $db_statement = $mysql_connection->prepare($db_query);
  $db_statement->execute();
  $db_response = $db_statement->get_result();
  while ($db_row = $db_response->fetch_object()) {
    $db_row->img = "/media/cover/$db_row->id";
    $movies[] = $db_row;
  }
  return $movies;
}

function get_movie_from_database($movie_id) {
  $movies = get_movies_from_database();
  foreach ($movies as $movie)
    if ($movie->id == $movie_id) return $movie;
  return null;
}

function search_database_by_query($type, $search_query, $items_count = null) {
  global $mysql_connection;

  $search_results = $type == 'movie' ? get_movies_from_database() : get_stars_from_database();
  $search_results = array_filter($search_results, function($x) {
    global $search_query;
    return strpos(strtolower($x->name), strtolower($search_query)) !== false;
  });

  usort($search_results, function($a, $b) {
    return $a->name <=> $b->name;
  });

  if (!$items_count) return $search_results;

  return array_slice($search_results, 0, $items_count);
}

function print_line($line, $indentation_level=1) {
  echo "\n";
  for ($i = 0 ; $i < $indentation_level; $i++) echo "  ";
  echo $line;
}

function print_page_header($head_items=[]) {
  global $is_Android;
  global $is_mobile;
  global $is_iPad;
  
  ?><!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <meta http-equiv='X-UA-Compatible' content='ie=edge'>
  <script src='/scripts/jquery.min.3.4.1.js'></script>
  <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
  <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <script src='/scripts/main.js'></script>
  <script>
    const isAndroid = <?php echo $is_Android ? "true" : "false"; ?>;
    const languages = [{
      code: `en`,
      name: `English`
    }, {
      code: `jp`,
      name: `日本語`
    }]
    const displayText = {
      stars: `<?php echo get_text('stars', "strtoupper"); ?>`,
      movies: `<?php echo get_text('movies', "strtoupper"); ?>`,
      keyword: `<?php echo get_text('keyword', "strtoupper"); ?>`,
      go: `<?php echo get_text('go', "strtoupper"); ?>`,
    }
  </script>
  <script src="/react/TopBanner.js" type="text/babel"></script>
  <link rel="stylesheet" href="/styles/main.css">
  <link rel="stylesheet" href="/styles/banner.css"><?php

  if (!$is_mobile && !$is_iPad) {
    print_line("<link rel='stylesheet' href='/styles/main-web.css'>");
  }

  foreach ($head_items as $i) {
    if ($i) print_line($i);
  }
?>

</head>
<body>
  <nav id='banner'></nav><?php
}

function print_page_footer() {
  print_line("</body>", 0);
  print_line("</html>", 0);
}

session_start();

/** Verify Login */
if ($_SERVER['ACCESS_PASSWORD'] && !$_SESSION['auth']) {
  require_once($_SERVER['DOCUMENT_ROOT']."/public/login.html");
  exit();
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/languages.php");
$language = isset($_COOKIE['language']) ? $_COOKIE['language'] : "en";

$useragent=$_SERVER['HTTP_USER_AGENT'];
$is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
$is_iOS = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
$is_iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
$is_iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
$is_Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
$is_webOS = stripos($_SERVER['HTTP_USER_AGENT'], "webOS");
