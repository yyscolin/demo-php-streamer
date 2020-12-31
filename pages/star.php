<?php

function print_star_potrait($star) {
  print_line("<div id='s-pot' class='flex'>");
  print_line("<img class='frame' src='/images/frame.png'>", 2);
  print_line("<div class='flex inner' style='background-color:grey;z-index:-2'></div>", 2);
  print_line("<div class='flex inner'>", 2);
  print_line("<img src='$star->img'>", 3);
  print_line("<div class='info'>", 3);
  print_line("<p class='text-ellipsis'>".get_locale_star_name($star)."</p>", 4);
  print_line("<p class='text-ellipsis'>$star->dob</p>", 4);
  print_line("</div>", 3);
  print_line("</div>", 2);
  print_line("</div>");
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");

$id = $_GET["id"];
if (!isset($id)) redirectToHomePage();
require_once($_SERVER['DOCUMENT_ROOT']."/public/search-database.php");

print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<link rel='stylesheet' href='/styles/star-potrait.css'>",
  "<title>$star_name - Demo PHP Streamer</title>"
]);

print_line("<div id='main-block' style='margin-top:0;overflow:hidden'>");

if ($id == 0) {
  $query = "select id, title from vids where id not in (select vid from casts) and status=3 order by release_date desc";
  $res = $con->query($query);
} else {
  $r = search_database_by_id('star', $id);
  if (!$r) redirectToHomePage();
  print_star_potrait($r);

  $query = "select id, title from vids where id in (select vid from casts where star = ?) and status=3 order by release_date desc";
  $stmt = $con->prepare($query);
  $stmt->bind_param('s', $r->id);
  $stmt->execute();
  $res = $stmt->get_result();
}

/** Print page content */
while ($r = mysqli_fetch_object($res)) {
  print_vid_box($r, 2);
}
print_line("</div>");

print_page_footer();

?>