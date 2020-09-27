<?php

function getFileType($item) {
  $splits = explode('.', $item);
  $i = count($splits) - 1;
  return strtolower($splits[$i]);
}

function getOnclick($item) {
  if (is_dir($item))
    return 'ajax_getFiles';

  $fileType = getFileType($item);
  switch ($fileType) {
    case 'bmp':
    case 'jpeg':
    case 'jpg':
    case 'png':
    case 'gif':
      return "openImage";
    case 'mp4':
      return "openVideo";
    default:
      return '';
  }
}

$dir = $_GET['dir'];
if (!isset($dir)) {
  header("HTTP/1.1 400 Bad Request");
  return;
}

$base = '../others';
$files = [];
$folders = [];

$lastChar = substr($dir, -1);
if ($lastChar!='/') $dir .= '/';

$items = glob("$base$dir*");
$dir_level = substr_count($dir, '/')+1;
foreach ($items as $item) {
  $name = explode('/', $item)[$dir_level];
  if (is_dir($item)) $name.='/';
  $onclick = getOnclick($item);
  $param = is_dir($item)
    ? $dir.$name
    : $item;
  $value = [
    'name' => $name,
    'onclick' => $onclick,
    'param' => $param
  ];
  if (is_dir($item))
    array_push($folders, $value);
  else
    array_push($files, $value);
}
$payload = ['dir'=>$dir,'files'=>array_merge($folders, $files)];

header('Content-Type: application/json');
header("HTTP/1.1 200 OK");
echo json_encode($payload);

?>