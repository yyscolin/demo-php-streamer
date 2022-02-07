<?php

function print_star_potrait($star, $star_name) {
  print_line("<div id='s-pot' class='flex'>");
  print_line("<img class='frame' src='/images/frame.png'>", 2);
  print_line("<div class='flex inner' style='background-color:grey;z-index:-2'></div>", 2);
  print_line("<div class='flex inner'>", 2);
  print_line("<img src='$star->img'>", 3);
  print_line("<div class='info'>", 3);
  print_line("<p class='text-ellipsis'>$star_name</p>", 4);
  print_line("<p class='text-ellipsis'>$star->dob</p>", 4);
  print_line("</div>", 3);
  print_line("</div>", 2);
  print_line("</div>");
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/box-vid.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/common.php");

$id = $_GET["id"];
if (!isset($id)) redirectToHomePage();

if ($id == 0) {
  $star_name = "Others";
} else {
  $star = get_entity_from_database('star', $id);
  if (!$star) redirectToHomePage();
  $star_name = $star->name;
}

print_page_header([
  "<link rel='stylesheet' href='/styles/poster.css'>",
  "<link rel='stylesheet' href='/styles/star-potrait.css'>",
  "<title>$star_name - ".$_SERVER["PROJECT_TITLE"]."</title>"
]);

print_line("<div id='main-block' style='margin-top:0;overflow:hidden'>");

if ($id == 0) {
  $db_query = "select id, name_$language as name from vids where id not in (select vid from xref_entities_vids) and status=1 order by release_date desc";
  $res = $con->query($db_query);
} else {
  print_star_potrait($star, $star_name);

  $db_query = "select id, name_$language as name from vids where id in (select vid from xref_entities_vids where entity=?) and status=1 order by release_date desc";
  $stmt = $con->prepare($db_query);
  $stmt->bind_param('s', $star->id);
  $stmt->execute();
  $res = $stmt->get_result();
}

while ($r = mysqli_fetch_object($res)) print_vid_box($r, 2);
print_line("</div>");

print_page_footer();

?>