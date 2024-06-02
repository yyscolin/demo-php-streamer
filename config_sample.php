<?php

$PROJ_CONF = array(
  "PROJECT_TITLE"=>"Demo PHP Streamer",

  /** Should the website be protected using a password */
  "ACCESS_PASSWORD"=>"",

  /** Should the seek button be on the right  in video player, default left */
  "SEEK_BTN_RIGHT"=>false,

  "MYSQL_HOSTNAME"=>"127.0.0.1",
  "MYSQL_PORT"=>3306,
  "MYSQL_USERNAME"=>"",
  "MYSQL_PASSWORD"=>"",
  "MYSQL_DATABASE"=>"",

  /** Using full paths recommended instead of relative */
  "MEDIA_DIRS"=>[
    /** Where the mp4 files of your movies should be located */
    "mp4"=>[
      $_SERVER["DOCUMENT_ROOT"]."media/movies",
    ],

    /** Where the media cover art for your movies should be located */
    "cover"=>[
      $_SERVER["DOCUMENT_ROOT"]."media/covers",
    ],

    /** Where the profile pictures for your stars should be located */
    "star"=>[
      $_SERVER["DOCUMENT_ROOT"]."media/stars",
    ],
  ],
);
