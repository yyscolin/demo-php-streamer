<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/config.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/public/mysql_connections.php");

$project_root = $_SERVER["DOCUMENT_ROOT"];
$media_path = $PROJ_CONF["MEDIA_PATH"];

function find_blob_file($file_name) {
    global $PROJ_CONF;
    foreach ($PROJ_CONF["BLOB_DIRS"] as $blob_dir) {
        $blob_file = "$blob_dir/$file_name";
        if (file_exists($blob_file)) return $blob_file;
    }
}

function prefix_zeroes($string, $length) {
    return str_repeat(0, $length - strlen($string)).$string;
}

function send_headers($file_size) {
    $content_length = $file_size;
    $byte_start = 0;
    $byte_end = $file_size - 1;

    header('Content-type: video/mp4');
    header("Accept-Ranges: 0-$content_length");
    if (isset($_SERVER['HTTP_RANGE'])) {
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $byte_start-$byte_end/$file_size");
            exit;
        }
        if ($range == '-') {
            $c_start = $file_size - substr($range, 1);
            $c_end = $byte_end;
        } else {
            $range  = explode('-', $range);
            $c_start = $range[0];
            $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $file_size;
        }
        $c_end = $c_end > $byte_end ? $byte_end : $c_end;
        if ($c_start > $c_end || $c_start > $file_size - 1 || $c_end >= $file_size) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $byte_start-$byte_end/$file_size");
            exit;
        }
        $byte_start = $c_start;
        $byte_end = $c_end;
        $content_length = $byte_end - $byte_start + 1;
        header('HTTP/1.1 206 Partial Content');
    }
    header("Content-Range: bytes $byte_start-$byte_end/$file_size");
    header("Content-Length: $content_length");

    return array($content_length, $byte_start, $byte_end);
}

function send_media_file($file_path, $crypt_key=null) {
    set_time_limit(0);

    $buffer_size = 4 * 1024;
    $file_size = filesize($file_path);
    list($content_length, $byte_start, $byte_end) = send_headers($file_size);
    $read_stream = fopen($file_path, "rb");
    if ($byte_start > 0) fseek($read_stream, $byte_start);

    while(!feof($read_stream) && ($p = ftell($read_stream)) <= $byte_end) {
        if ($p + $buffer_size > $byte_end) $buffer_size = $byte_end - $p + 1;
        $chunk = fread($read_stream, $buffer_size);
        if ($crypt_key) {
            $decrypt_maps = str_split(file_get_contents($crypt_key), 256);
            $map_index = $byte_start % count($decrypt_maps);
            for ($i = 0; $i < $buffer_size; $i++) {
                echo $decrypt_maps[$map_index][ord($chunk[$i])];
                $map_index = $map_index + 1;
                if ($map_index == count($decrypt_maps)) $map_index = 0;
            }
        } else echo $chunk;
        flush();
    }
    fclose($read_stream);
}

$is_image_file = in_array($_GET["type"], ["cover", "star"]);
if ($is_image_file) {
    header("Content-Type:image/jpeg");
    $media_file = "$media_path/".$_GET["type"]."s/".$_GET["file"];
    if ($PROJ_CONF["BLOB_KEY"] && file_exists("$media_file.eif")) {
        $raw_binary = file_get_contents("$media_file.eif");
        $iv_key = substr($raw_binary, -16);
        $bin_data = substr($raw_binary, 0, -16);
        $blob_key = base64_decode($PROJ_CONF["BLOB_KEY"]);
        $bin_data =  openssl_decrypt($bin_data, "AES-256-CBC", $blob_key, OPENSSL_RAW_DATA, $iv_key);
        echo $bin_data;
        exit();
    }
    if (!file_exists("$media_file.jpg")) $media_file = "$project_root/images/default-".$_GET["type"];
    readfile("$media_file.jpg");
    exit();
}

if ($_GET["type"] != "movie") {
    http_response_code(400);
    exit();
}

list($movie_id, $part_id) = explode("~", $_GET["file"]);

$file_v4 = prefix_zeroes($movie_id, 6)."~".prefix_zeroes($part_id, 3);
$file_v4 = find_blob_file($file_v4);
if ($PROJ_CONF["CRYPT_V4_KEY"] && $file_v4) {
    send_media_file($file_v4, $PROJ_CONF["CRYPT_V4_KEY"]);
    exit();
}

$db_query = "
    SELECT SUBSTRING_INDEX(name_en, ' ', 1) as name FROM movies WHERE id=?";
$db_statement = $mysql_connection->prepare($db_query);
$db_statement->bind_param("s", $movie_id);
$db_statement->execute();
$db_response = $db_statement->get_result();
$db_row = mysqli_fetch_object($db_response);

$movie_title_first_word = $db_row->name;
$file_path = "$media_path/movies/$movie_title_first_word"."_$part_id.mp4";
if (file_exists($file_path)) {
    send_media_file($file_path);
    exit();
}

$db_query = "SELECT file_id FROM movies_media WHERE movie_id=? AND part_id=?";
$db_statement = $mysql_connection->prepare($db_query);
$db_statement->bind_param("ss", $movie_id, $part_id);
$db_statement->execute();
$db_response = $db_statement->get_result();
if ($db_response->num_rows < 1) {
    http_response_code(404);
    exit();
}
$db_row = mysqli_fetch_object($db_response);

$file_id = $db_row->file_id;
$file_path = "$media_path/movies/$file_id.mp4";
if (file_exists($file_path)) {
    send_media_file($file_path);
    exit();
}

$blob_key = base64_decode($PROJ_CONF["BLOB_KEY"]);
$db_query = "SELECT * FROM media_files WHERE id=$file_id";
$db_response = $mysql_connection->query($db_query);
$db_row = mysqli_fetch_object($db_response);
$iv_key = $db_row->iv_key;

set_time_limit(0);
$version_id = $db_row->ver_id;
if ($version_id == 3) {
    $blob_file = find_blob_file(prefix_zeroes($file_id, 4));
    if (!$PROJ_CONF["BLOB_KEY"] || !$blob_file) {
        http_response_code(404);
        exit();
    }

    $blob_size = 512 * 1024 - 1;

    $file_size = $db_row->file_size;
    list($content_remaining, $byte_start, $byte_end) = send_headers($file_size);
    $blob_index = floor($byte_start / $blob_size);
    $bytes_displacement = $byte_start - $blob_index * $blob_size;

    $blob_file = fopen($blob_file, "rb");
    fseek($blob_file, $blob_index * ($blob_size + 1));

    do {
        $bin_data = fread($blob_file, $blob_size + 1);
        $bin_data =  openssl_decrypt($bin_data, "AES-256-CBC", $blob_key, OPENSSL_RAW_DATA, $iv_key);
        if ($bytes_displacement > 0) {
            $bin_data = substr($bin_data, $bytes_displacement);
            $bytes_displacement = 0;
        }

        $buffer_size = min(strlen($bin_data), $content_remaining);
        echo substr($bin_data, 0, $buffer_size);
        flush();

        $content_remaining -= $buffer_size;
        $blob_index++;
    } while ($content_remaining > 0);

    exit();
}
