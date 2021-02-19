<?php

function redirectToHomePage() {
  header("Location: /");
  exit();
}

function get_others_star() {
  global $con;

  $db_query = "select count(id) as count from vids where id not in (select vid from casts) and status=1";
  $db_response = mysqli_query($con, $db_query);
  $star = mysqli_fetch_object($db_response);
  if ($star->count == 0) return null;

  $star->id = 0;
  $star->name_f = "Others";
  $star->dob = null;
  return $star;
}

function print_line($line, $indentation_level=1) {
  echo "\n";
  for ($i = 0 ; $i < $indentation_level; $i++) echo "  ";
  echo $line;
}

function get_img_src($type, $id) {
  if ($type == "vid" || $type == "vids") {
    $img = "/media/covers/$id.jpg";
    $type = "vid";
  } else {
    $img = "/media/stars/$id.jpg";
    $type = "star";
  }

  if (!file_exists($_SERVER['DOCUMENT_ROOT'].$img)) {
    global $default_imgs;
    $img = $default_imgs[$type];
  }
  return $img;
}

function print_page_header($head_items=[]) {
  global $is_Android;
  global $is_mobile;
  global $is_iPad;
  global $default_imgs;
  
  ?><!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <meta http-equiv='X-UA-Compatible' content='ie=edge'>
  <script src='/scripts/jquery.min.3.4.1.js'></script>
  <script src='/scripts/main.js'></script>
  <script src='/scripts/search-box.js'></script>
  <script>
    const isAndroid = <?php echo $is_Android ? "true" : "false"; ?>;
    const defaultStarSrc = '<?php echo $img_src = $default_imgs['star']; ?>';
    const defaultCoverSrc = '<?php echo $img_src = $default_imgs['vid']; ?>';
  </script>
  <link rel='stylesheet' href='/styles/main.css'>
  <link rel='stylesheet' href='/styles/banner.css'><?php

  if (!$is_mobile && !$is_iPad) {
    print_line("<link rel='stylesheet' href='/styles/main-web.css'>");
  }

  foreach ($head_items as $i) {
    if ($i) print_line($i);
  }
?>

</head>
<body>
  <div id='banner'>
    <button id='menu-button' onclick='$("body").toggleClass("menu-active")'>☰</button
    ><img id='banner-icon' onclick="window.location.href='/'" src='/banner.png' title='Go to homepage'
    ><div id='menu-bar' class='inline'>
      <a href='/stars'><?php echo get_text('stars', strtoupper); ?></a
      ><a href='/vids'><?php echo get_text('movies', strtoupper); ?></a
      ><form id='search-box' class='inline' action='/search.php'>
        <select id='search-type' name='type' onchange='searchDatabase()'>
          <option value='star'><?php echo get_text('stars', ucfirst); ?></option>
          <option value='vid'><?php echo get_text('movies', ucfirst); ?></option>
        </select
        ><input id='search-field' type='search' name='query' oninput='searchDatabase()' placeholder='<?php echo get_text("keyword"); ?>...'>
        <div id='search-results' style='display: none;'></div>
        <button type='submit'><?php echo get_text("go", strtoupper); ?></button>
      </form><!--
      --><div class='inline' style='cursor:pointer;margin-left:16px'>
        <img src='/images/languages-white.png' width='24px' onclick='$("#lang-dropdown").toggle()'>
        <ul id='lang-dropdown' class='dropdown-list'>
          <li onclick='setLanguange("en")'>English</li>
          <li onclick='setLanguange("jp")'>日本語</li>
        </ul>
        <script>
          function setLanguange(language) {
            document.cookie = `language=${language}; SameSite=Lax;`
            location.reload()
          }
        </script>
      </div>
    </div>
  </div><?php
}

function print_page_footer() {
  print_line("</body>", 0);
  print_line("</html>", 0);
}

session_start();

/** Verify Login */
if (!$_SESSION['auth']) {
  require_once($_SERVER['DOCUMENT_ROOT']."/public/login.html");
  exit();
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");
require_once($_SERVER['DOCUMENT_ROOT']."/public/languages.php");

$default_imgs = array(
  "star"=>file_exists($_SERVER['DOCUMENT_ROOT']."/media/stars/default.jpg")
    ? "/media/stars/default.jpg"
    : "/images/default-star.jpg",
  "vid"=>file_exists($_SERVER['DOCUMENT_ROOT']."/media/covers/default.jpg")
    ? "/media/covers/default.jpg"
    : "/images/default-cover.jpg"
);

$useragent=$_SERVER['HTTP_USER_AGENT'];
$is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
$is_iOS = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
$is_iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
$is_iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
$is_Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
$is_webOS = stripos($_SERVER['HTTP_USER_AGENT'], "webOS");
