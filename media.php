<?php

require_once($_SERVER['DOCUMENT_ROOT']."/public/mysql_connections.php");

$project_root = $_SERVER['DOCUMENT_ROOT'];
$media_path = $_SERVER['MEDIA_PATH'];
switch($_GET["type"]) {
    case "cover":
        header("Content-Type:image/jpeg");
        $file = $_GET["file"];
        readfile(file_exists("$media_path/covers/$file.jpg")
            ? "$media_path/covers/$file.jpg"
            : "$project_root/images/default-cover.jpg"
        );
        break;
    case "star":
        header("Content-Type:image/jpeg");
        $file = $_GET["file"];
        readfile(file_exists("$media_path/entities/$file.jpg")
            ? "$media_path/entities/$file.jpg"
            : "$project_root/images/default-star.jpg"
        );
        break;
    case "vid":
        $subfolder = $_GET["subfolder"];
        $vid = $_GET["vid"];
        $part = $_GET["part"];
        $file_fullpath = "$media_path/vids/$subfolder/$vid"."_$part.mp4";
        if (file_exists($file_fullpath)) {
            $fp = @fopen($file_fullpath, 'rb');
            $file_size = filesize($file_fullpath);
        } else {
            $file_size = 0;
            $blob_chunks = [];
            $db_query = "select blob_head, size, iv from media_files where fid=(select fid from vid_media where vid=? and part=?) order by blob_no";
            $stmt = $con->prepare($db_query);
            $stmt->bind_param('ss', $vid, $part);
            $stmt->execute();
            $db_response = $stmt->get_result();
            while ($row = mysqli_fetch_object($db_response)) {
                $blob_chunks[] = $row;
                $file_size += $row->size;
            }
        }

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

        $buffer_size = 4 * 1024;
        if (file_exists($file_fullpath)) {
            if ($byte_start > 0) fseek($fp, $byte_start);
            while(!feof($fp) && ($p = ftell($fp)) <= $byte_end) {
                if ($p + $buffer_size > $byte_end) $buffer_size = $byte_end - $p + 1;
                set_time_limit(0);
                echo fread($fp, $buffer_size);
                flush();
            }
            fclose($fp);
        } else {
            $blob_size = 4 * 1024 * 1024 - 1;
            foreach ($blob_chunks as $blob_chunk) {
                if ($byte_start <= $blob_chunk->size) {
                    $chunk_byte_end = min($byte_end, $blob_chunk->size);
                    $blob_first = $blob_chunk->blob_head + floor($byte_start / $blob_size);
                    $blob_last = $blob_chunk->blob_head + floor($chunk_byte_end / $blob_size);
                    for ($i = $blob_first; $i <= $blob_last; $i++) {
                        set_time_limit(0);

                        $bin_data = openssl_decrypt(
                            file_get_contents($_SERVER['BLOB_PATH']."/$i"),
                            "AES-256-CBC",
                            $_SERVER["BLOB_KEY"],
                            OPENSSL_RAW_DATA,
                            $blob_chunk->iv
                        );

                        if ($i == $blob_first && $byte_start > 0) {
                            $blob_byte_start = $byte_start % $blob_size;
                            if ($blob_byte_start > 0) $bin_data = substr($bin_data, $blob_byte_start);
                        } elseif ($i == $blob_last) {
                            $byte_length = $chunk_byte_end % $blob_size + 1;
                            if ($byte_length < $blob_size) $bin_data = substr($bin_data, 0, $byte_length);
                        }

                        while ($bin_data) {
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
                }
                $byte_start -= $blob_chunk->size;
                $byte_end -= $blob_chunk->size;
            }
        }
        break;
    default:
        http_response_code(404);
}
