<?php

$project_root = $_SERVER['DOCUMENT_ROOT'];
$media_path = $_SERVER['MEDIA_PATH'];

if ($_GET["type"] == "cover") {
    header("Content-Type:image/jpeg");
    $file = $_GET["file"];
    $media_file = "$media_path/covers/$file.jpg";
    if (!file_exists($media_file)) $media_file = "$project_root/images/default-cover.jpg";
    readfile($media_file);
    exit();
}

if ($_GET["type"] == "star") {
    header("Content-Type:image/jpeg");
    $file = $_GET["file"];
    $media_file = "$media_path/entities/$file.jpg";
    if (!file_exists($media_file)) $media_file = "$project_root/images/default-star.jpg";
    readfile($media_file);
    exit();
}

if ($_GET["type"] != "vid") {
    http_response_code(404);
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");

function buffer_bytes($bin_data, $bytes_to_send, $buffer_size) {
    if (strlen($bin_data) > $bytes_to_send) $bin_data = substr($bin_data, 0, $bytes_to_send);
    while ($bin_data) {
        set_time_limit(0);
        if (strlen($bin_data) > $buffer_size) {
            echo substr($bin_data, 0, $buffer_size);
            $bin_data = substr($bin_data, $buffer_size);
        } else {
            echo $bin_data;
            $bin_data = null;
        }
        flush();
    }
}

function get_blob_path($blob_no) {
    $blob_no = strval($blob_no);
    $blob_no = str_repeat("0", 6 - strlen($blob_no)).$blob_no;
    return $_SERVER['BLOB_PATH']."/$blob_no";
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

$subfolder = $_GET["subfolder"];
$vid = $_GET["vid"];
$part = $_GET["part"];
$file_fullpath = "$media_path/vids/$subfolder/$vid"."_$part.mp4";

$buffer_size = 4 * 1024;

if (file_exists($file_fullpath)) {
    $file_size = filesize($file_fullpath);
    list($content_length, $byte_start, $byte_end) = send_headers($file_size);
    $fp = fopen($file_fullpath, 'rb');
    if ($byte_start > 0) fseek($fp, $byte_start);
    while(!feof($fp) && ($p = ftell($fp)) <= $byte_end) {
        if ($p + $buffer_size > $byte_end) $buffer_size = $byte_end - $p + 1;
        set_time_limit(0);
        echo fread($fp, $buffer_size);
        flush();
    }
    fclose($fp);
    exit();
}

$blob_key = file_get_contents($_SERVER["BLOB_KEY"]);

$db_query = "select * from media_files_v2 where fid=(select fid from vid_media_v2 where vid=? and part=?)";
$stmt = $con->prepare($db_query);
$stmt->bind_param('ss', $vid, $part);
$stmt->execute();
$db_response = $stmt->get_result();
if ($db_response->num_rows > 0) {
    $blob_size = 512 * 1024 - 1;

    $db_row = mysqli_fetch_object($db_response);
    $fid = $db_row->fid;
    $file_size = $db_row->size;
    $iv = $db_row->iv;
    $blob_count = ceil($file_size / $blob_size);

    list($content_remaining, $bytes_displacement, $byte_end) = send_headers($file_size);
    $blob_folder = $_SERVER['BLOB_PATH']."/".str_repeat(0, 4 - strlen($fid)).$fid;
    $blob_index = floor($bytes_displacement / $blob_size);
    $bytes_displacement %= $blob_size;
    for ($blob_index; $blob_index < $blob_count; $blob_index++) {
        $blob_path = "$blob_folder/".str_repeat(0, 5 - strlen($blob_index)).$blob_index;
        $bin_data = file_get_contents($blob_path);
        $bin_data =  openssl_decrypt($bin_data, "AES-256-CBC", $blob_key, OPENSSL_RAW_DATA, $iv);
        if ($bytes_displacement > 0) {
            $bin_data = substr($bin_data, $bytes_displacement);
            $bytes_displacement = 0;
        }
        echo $bin_data;
        flush();
    }

    exit();
}

$pieces_per_blob = 256;
$piece_size = 512 * 1024 - 1;

$db_query = "select * from media_files where fid=(select fid from vid_media where vid=? and part=?)";
$stmt = $con->prepare($db_query);
$stmt->bind_param('ss', $vid, $part);
$stmt->execute();
$db_response = $stmt->get_result();
$db_row = mysqli_fetch_object($db_response);
$fid = $db_row->fid;
$padding = $db_row->padding;
$iv = $db_row->iv;

$blob_chunks = [];
$file_size = 0;
$db_query = "select head_piece, pieces from media_pieces where fid=$fid order by sequence";
$db_response = $con->query($db_query);
while ($db_row = mysqli_fetch_object($db_response)) {
    $db_row->size = $piece_size * $db_row->pieces;
    $file_size += $db_row->size;
    $blob_chunks[] = $db_row;
}
$file_size -= $padding;
$blob_chunks[count($blob_chunks) - 1]->size -= $padding;

list($content_remaining, $bytes_displacement, $byte_end) = send_headers($file_size);

foreach ($blob_chunks as $blob_chunk) {
    if ($bytes_displacement > $blob_chunk->size) {
        $bytes_displacement -= $blob_chunk->size;
        continue;
    }

    $chunk_remaining = $blob_chunk->size;
    $piece_no = $blob_chunk->head_piece;
    if ($bytes_displacement > 0) {
        $pieces_to_skip = floor($bytes_displacement / $piece_size);
        $piece_no += $pieces_to_skip;
        $chunk_remaining -= $pieces_to_skip * $piece_size;
        $bytes_displacement %= $piece_size;
    }

    while ($content_remaining > 0 && $chunk_remaining > 0) {
        $blob_no = floor($piece_no / $pieces_per_blob);

        if (!isset($blob_opened) || $blob_opened !== $blob_no) {
            if (isset($blob_opened)) fclose($blob_stream);
            $blob_stream = fopen(get_blob_path($blob_no), "rb");
            $blob_opened = $blob_no;
            $piece_displacement = $piece_no % $pieces_per_blob;
            if ($piece_displacement > 0) fseek($blob_stream, $piece_displacement * ($piece_size + 1));
        }

        $bin_data = fread($blob_stream, $piece_size + 1);
        $bin_data =  openssl_decrypt($bin_data, "AES-256-CBC", $blob_key, OPENSSL_RAW_DATA, $iv);

        if ($bytes_displacement > 0) {
            $bin_data = substr($bin_data, $bytes_displacement);
            $bytes_displacement = 0;
        }

        $bytes_to_send = min($content_remaining, $chunk_remaining, $piece_size);
        buffer_bytes($bin_data, $bytes_to_send, $buffer_size);

        $content_remaining -= $bytes_to_send;
        $chunk_remaining -= $bytes_to_send;
        $piece_no++;
    }

    fclose($blob_stream);
    unset($blob_opened);
}
