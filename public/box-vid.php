<?php

function print_vid_box($id) {
  $img_src = "/media/covers/$id.jpg";
  if (!file_exists($_SERVER['DOCUMENT_ROOT'].$img_src)) {
    global $default_cover_src;
    $img_src = $default_cover_src;
  }

  echo "
    <a class='poster vid-box' href='/vid/$id'>
      <p>$id</p>
      <p class='border'>$id</p>
      <img class='poster' src='$img_src'>
    </a>";
}