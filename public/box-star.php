<?php

function print_star($star) {
  $img_src = "/media/stars/$star->id.jpg";

  echo "\n><div class='star-box'>"
    ."<img onclick='window.location.href=\"/star/$star->id\"' onerror='$(this).attr(\"src\", \"/media/stars/0.jpg\")' src='$img_src'>";
  
  echo "<p class='jp-name'>$star->name_j</p>"
    ."<p class='en-name'>$star->name_e</p>"
    ."<p class='dob'>$star->dob</p>"
    ."<a class='vid-count' href='/star/$star->id'>$star->count Videos</a>";
  
  echo "</div";
}