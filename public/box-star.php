<?php

function print_star_box($star) {
  $onclick = "window.location.href=\"/star/$star->id\"";
  $onerror = "$(this).attr(\"src\", \"/media/stars/0.jpg\")";
  $src = "/media/stars/$star->id.jpg";
  $star_name = get_locale_star_name($star);

  echo "<div class='star-box'>"
    ."<img onclick='$onclick' onerror='$onerror' src='$src'>";
  
  echo "<p class='name'>$star_name</p>"
    ."<p class='dob'>$star->dob</p>"
    ."<a class='vid-count' href='/star/$star->id'><span>$star->count</span> "
    .get_text('videos', ucfirst)."</a>";
  
  echo "</div>";
}