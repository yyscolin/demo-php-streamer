<?php

function print_star_box($star) {
  $onclick = "window.location.href=\"/star/$star->id\"";
  $onerror = "$(this).attr(\"src\", \"/media/stars/0.jpg\")";
  $src = "/media/stars/$star->id.jpg";
  $star->name_e = $star->name_f;
  if ($star->name_l) $star->name_e .= " $star->name_l";

  echo "<div class='star-box'>"
    ."<img onclick='$onclick' onerror='$onerror' src='$src'>";
  
  echo "<p class='jp-name'>$star->name_j</p>"
    ."<p class='en-name'>$star->name_e</p>"
    ."<p class='dob'>$star->dob</p>"
    ."<a class='vid-count' href='/star/$star->id'>$star->count Videos</a>";
  
  echo "</div>";
}