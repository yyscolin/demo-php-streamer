<?php

include('public/box-star.php');
include('public/box-vid.php');
include('public/search-database.php');

$type = $_GET['type'];
$query = $_GET['query'];
$search_results = isset($query) && ($type != 'vid' || $type != 'star')
  ? search_database_by_query($type, $query, 10)
  : [];

function print_search_results() {
  global $type;
  global $search_results;

  if (count($search_results) > 0) {
    echo "<table id='results-table' class='$type'>";

    foreach ($search_results as $r) {
      if ($type == 'vid') {
        $title = "<p>$r->id $r->title</p>";
        $subtitle = $r->release_date ? "<p class='subtitle'>$r->release_date</p>" : "";
        $media_path = "/media/covers/$r->id.jpg";
      } else {
        $title = "<p>$r->name</p>";
        $subtitle = $r->dob ? "<p class='subtitle'>$r->dob</p>" : "";
        $media_path = "/media/stars/$r->id.jpg";
      }
    
      echo "<tr class='noselect' onclick='window.location.href=\"/$type/$r->id\"'>";
      echo "<td><img src='$media_path'></td>";
      echo "<td style='text-align:left'>$title$subtitle</td>";
      echo "</tr>";
    }

    echo "</table>";
  }
}

include('public/common.php');
?>

  <title><?php echo get_text("search", ucfirst); ?> - Demo PHP Streamer</title>
  <style>
    #search-form {
      margin-top: 32px;
    }
    #results-table {
      margin: 32px auto;
    }
    #results-table tr {
      cursor: pointer;
    }
    #results-table img {
      height: 125px;
    }
    #results-table td:nth-child(2) {
      min-width: 20vw;
      padding-left: 1vw;
      padding-right: 1vw;
    }
    #results-table p {
      margin: 0;
      width: 100%;
    }
    #results-table.vid p:not(.subtitle) {
      font-size: 24px;
    }
    #results-table.star p:not(.subtitle) {
      font-size: 42px;
    }
    
    @media only screen and (max-width: 960px) {
      #results-table.vid p:not(.subtitle) {
        font-size: 22px;
      }
    }
    
    @media only screen and (max-width: 840px) {
      #results-table.vid p:not(.subtitle) {
        font-size: 20px;
      }
    }
    
    @media only screen and (max-width: 720px) {
      #results-table.vid {
        margin: 32px 4vw;
      }
    }
    
    @media only screen and (max-width: 680px) {
      #results-table.vid p:not(.subtitle) {
        font-size: 18px;
      }
    }
    
    @media only screen and (max-width: 600px) {
      #results-table.vid {
        background-color: transparent;
        border: 0;
        margin: 0 4vw;
      }
      #results-table.vid td {
        background-color: darkslateblue;
        display: block;
      }
      #results-table.vid tr:not(first-child) td:first-child {
        margin-top: 32px;
      }
      #results-table.vid img {
        height: auto;
        width: 100%;
      }
    }

    @media only screen and (max-width: 500px) {
      #results-table {
        margin: 0 auto;
        width: 90vw;
      }
      #results-table.star {
        background-color: transparent;
        border: 0;
      }
      #results-table.star td {
        background-color: darkslateblue;
        display: block;
      }
      #results-table.star p {
        text-align: center;
      }
      #results-table.vid p:not(.subtitle) {
        font-size: 22px;
      }
      #results-table.star tr:not(first-child) td:first-child {
        margin-top: 32px;
      }
      #results-table.star img {
        height: auto;
        width: 100%;
      }
      #results-table.vid p:not(.subtitle) {
        font-size: 14px;
      }
    }
  </style><?php if (!$is_mobile && $is_iPad) {
    echo "
  <style>
    #results-table tr:hover {
      background-color: black;
      color: white;
    }
  </style>";
  }
  

include('public/common-mid.php');

?>
  
  <div id='main-block'>
    <form id='search-form'>
      <select name='type'>
        <option value='star'<?php if ($type == 'star') echo "selected='selected'"; ?>><?php echo get_text("stars", ucfirst); ?></option>
        <option value='vid'<?php if ($type == 'vid') echo "selected='selected'"; ?>><?php echo get_text("movies", ucfirst); ?></option>
      </select>
      <input name='query' value='<?php echo $query; ?>'>
      <button type='submit'><?php echo get_text("search", ucfirst); ?></button>
    </form>
    <?php print_search_results(); ?>

  </div><?php

include('public/html-tail.html');

?>
