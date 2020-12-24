<?php

function print_vid_box($id, $title, $default_indentation=1) {
  $img_src = "/media/covers/$id.jpg";
  if (!file_exists($_SERVER['DOCUMENT_ROOT'].$img_src)) {
    global $default_cover_src;
    $img_src = $default_cover_src;
  }

  print_line("<div class='poster vid-box'>", $default_indentation);
  print_line("<h3>$title</h3>", $default_indentation + 1);
  print_line("<img src='$img_src' onclick='window.location.href=`/vid/$id`'>", $default_indentation + 1);
  print_line("</div>", $default_indentation);
}