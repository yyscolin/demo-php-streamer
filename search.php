<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/public/common.php");

$type = isset($_GET["type"]) ? $_GET["type"] : null;
$search_query = isset($_GET["query"]) ? $_GET["query"] : null;

if ($type && !in_array($type, ["movie", "star"])) {
  header("HTTP/1.0 400");
  exit();
}

function print_search_results($type, $search_query) {
  if ($type && $search_query) {
    $search_results = search_database_by_query($type, $search_query);
    if (count($search_results) > 0) {?>

    <table id="results-table" class="$type"><?php

      foreach ($search_results as $search_result) {
        if ($type == "movie") {
          $name = "<p>$search_result->name</p>";
          $subtitle = $search_result->release_date ? "<p class=\"subtitle\">$search_result->release_date</p>" : "";
          $media_path = $search_result->img;
        } else {
          $name = "<p>$search_result->name</p>";
          $subtitle = join(", ", array_map(function($star_attribute) {
            return "$star_attribute->key: $star_attribute->value";
          }, $search_result->attributes));
          $media_path = $search_result->img;
        }?>

      <tr class="noselect" onclick="window.location.href=`/pages/<?=$type?>.php?id=<?=$search_result->id?>`">
      <td><img src="<?=$media_path?>" onerror="this.src=`/images/default-cover.jpg`"></td>
      <td style="text-align:left"><?=$name.$subtitle?></td>
      </tr><?php

      }?>

    </table><?php

    }
  }
}

function search_database_by_query($type, $search_query, $items_count = null) {
  global $mysql_connection;

  $search_results = $type == 'movie' ? get_movies_from_database() : get_stars_from_database();
  $search_results = array_filter($search_results, function($x) {
    global $search_query;
    return strpos(strtolower($x->name), strtolower($search_query)) !== false;
  });

  usort($search_results, function($a, $b) {
    return $a->name <=> $b->name;
  });

  if (!$items_count) return $search_results;

  return array_slice($search_results, 0, $items_count);
}

print_page_header([
  "<link rel=\"stylesheet\" href=\"/styles/page-search.css\">",
  "<title>".get_text("search", "ucfirst")." - ".$PROJ_CONF["PROJECT_TITLE"]."</title>"
]);

?>
  
  <div id="main-block">
    <form id="search-form">
      <select name="type">
        <option value="star"<?php if ($type == "star") echo " selected=\"selected\""; ?>><?=get_text("stars", "ucfirst")?></option>
        <option value="movie"<?php if ($type == "movie") echo " selected=\"selected\""; ?>><?=get_text("movies", "ucfirst")?></option>
      </select>
      <input name="query" value="<?=$search_query?>" placeholder="<?=get_text("search", "ucfirst")?>">
      <button type="submit"><?=get_text("go", "ucfirst")?></button>
    </form><?=print_search_results($type, $search_query)?>

  </div><?php

print_page_footer();

?>
