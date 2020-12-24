<?php

function print_star_box($star) {
  $onclick = "window.location.href=\"/star/$star->id\"";
  
  $img_src = "/media/stars/$star->id.jpg";
  if (!file_exists($_SERVER['DOCUMENT_ROOT'].$img_src)) {
    global $default_star_src;
    $img_src = $default_star_src;
  }

  $star_name = get_locale_star_name($star);

  echo "<div class='star-box'>"
    ."<img onclick='$onclick' src='$img_src'>";
  
  echo "<p class='name'>$star_name</p>"
    ."<p class='dob'>$star->dob</p>"
    ."<a class='vid-count' href='/star/$star->id'><span>$star->count</span> "
    .get_text("movies", ucfirst)."</a>";
  
  echo "</div>";
}