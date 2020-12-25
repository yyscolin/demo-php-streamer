<?php

function print_vid_box($vid, $default_indentation=1) {
  if ($vid) {
    $img = get_img_src('vid', $id);
    $onclick = "window.location.href=`/vid/$vid->id`";
    $title = $_SERVER["show_vid_code"] == "true" ? "$vid->id $vid->title" : $vid->title;
    $style = "";
  } else {
    $img = "";
    $onclick = "";
    $title = "";
    $style = "display:none";
  }

  print_line("<div class='poster' style='$style'>", $default_indentation);
  print_line("<h3>$title</h3>", $default_indentation + 1);
  print_line("<img src='$img' onclick='$onclick'>", $default_indentation + 1);
  print_line("</div>", $default_indentation);
}