<?php

function print_vid_box($vid, $default_indentation=1) {
  if ($vid) {
    $img = get_img_src('vid', $vid->id);
    $href = "/vid/$vid->id";
    $title = $_SERVER["show_vid_code"] == "true" ? "$vid->id $vid->title" : $vid->title;
    $style = "";
  } else {
    $img = "";
    $href = "";
    $title = "";
    $style = "display:none";
  }

  print_line("<div class='poster' style='$style'>", $default_indentation);
  print_line("<p class='title'>$title</p>", $default_indentation + 1);
  print_line("<a href='$href'>", $default_indentation + 1);
  print_line("<img src='$img'>", $default_indentation + 2);
  print_line("</a>", $default_indentation + 1);
  print_line("</div>", $default_indentation);
}