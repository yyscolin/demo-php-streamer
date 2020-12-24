<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");

$id = $_GET["id"];
if (!isset($id)) redirectToHomePage();
require_once($_SERVER['DOCUMENT_ROOT']."/public/search-database.php");

if ($id == 0) {
  $r = new stdClass();
  $r->id = 0;
  $star_name = "Untitled Stars";
  $r->dob = "";
} else {
  $r = search_database_by_id('star', $id);
  if (!$r) redirectToHomePage();
  $star_name = get_locale_star_name($r);
}

print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<title>$star_name - Demo PHP Streamer</title>"
]);

$img_src = "/media/stars/$r->id.jpg";
if (!file_exists($_SERVER['DOCUMENT_ROOT'].$img_src)) {
  $img_src = $default_star_src;
}

print_line("<div class='flex' style='width: 100%; height: 32vw; margin: 8vw 0;overflow: hidden;'>");
print_line("<img style='z-index: 0; width: 100vw; height: 60vw;' src='/images/frame.png'>", 2);
print_line("<div class='flex' style='background-color: grey; margin: 0; width: 50vw; height: 30vw; position: absolute; z-index: -1;'>", 2);
print_line("<img src='$img_src' style='width: 15%;'>", 3);
print_line("<h1 style='color: white; margin: 0 1vw; vertical-align: top; display: inline-block;'>$star_name<br>$r->dob</h1>", 3);
print_line("</div>", 2);
print_line("</div>");

/** Prepare statement */
if ($id == 0) {
  echo "<p>";
  $query = "select id, title from vids where id not in (select vid from casts)";
  $res = $con->query($query);
  echo "</p>";
} else {
  $query = "select id, title from vids where id in (select vid from casts where star = ?) and status=3 order by release_date desc";
  $stmt = $con->prepare($query);
  $stmt->bind_param('s', $r->id);
  $stmt->execute();
  $res = $stmt->get_result();
}

/** Print page content */
print_line("<div id='main-block'>");
while ($r = mysqli_fetch_object($res)) {
  $title = $_SERVER["show_vid_code"] == "true" ? "$r->id $r->title" : $r->title;
  print_vid_box($r->id, $title, 2);
}
print_line("</div>");

print_page_footer();

?>