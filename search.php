<?php

include('public/box-star.php');
include('public/box-vid.php');
include('public/search-database.php');

$type = $_GET['type'];
$query = $_GET['query'];
$search_results = isset($query) && ($type != 'vid' || $type != 'star')
  ? search_database_by_query($type, $query)
  : [];

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
  </style><?php if (!$is_mobile) {
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
        <option value='vid'<?php if ($type == 'vid') echo "selected='selected'"; ?>><?php echo get_text("videos", ucfirst); ?></option>
      </select>
      <input name='query' value='<?php echo $query; ?>'>
      <button type='submit'><?php echo get_text("search", ucfirst); ?></button>
    </form>
    <?php if (count($search_results) > 0) {
      echo "<table id='results-table'>";
      foreach ($search_results as $r) {
        if ($type == 'vid') {
          $title = $r->id." ".$r->name;
          $media_path = "/media/covers/$r->id.jpg";
          $font_size = 24;
        } else {
          $title = $r->name_j;
          $media_path = "/media/stars/$r->id.jpg";
          $font_size = 43;
        }
        echo "<tr class='noselect' onclick='window.location.href=\"/$type/$r->id\"'>";
        echo "<td><img src='$media_path'></td>";
        echo "<td style='font-size:$font_size"."px'>$title</td>";
        echo "</tr>";
      }
      echo "</table>";
    }?>

  </div><?php

include('public/html-tail.html');

?>
