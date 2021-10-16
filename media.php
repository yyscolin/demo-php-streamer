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
            $db_query = "select head_blob, head_piece, size, iv from media_blobs where fid=(select fid from vid_media where vid=? and part=?) order by sequence";
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
            $piece_size = 512 * 1024 - 1;
            $pieces_per_blob = 128;
            $blob_size = $piece_size * $pieces_per_blob;

            foreach ($blob_chunks as $blob_chunk) {
                if ($byte_start <= $blob_chunk->size) {
                    $blob_no = $blob_chunk->head_blob;
                    $piece_no = $blob_chunk->head_piece;

                    while ($byte_start > $blob_size) {
                        $blob_no++;
                        $byte_start -= $blob_size;
                    }

                    while ($byte_start > $piece_size) {
                        if ($piece_no == $pieces_per_blob) {
                            $blob_no++;
                            $piece_no = 1;
                        } else {
                            $piece_no++;
                        }
                        $byte_start -= $piece_size;
                    }

                    while ($content_length > 0 && $blob_chunk->size > 0) {
                        if (!isset($blob_file)) $blob_file = fopen($_SERVER['BLOB_PATH']."/$blob_no", 'rb');
                        fseek($blob_file, ($piece_no - 1) * ($piece_size + 1));

                        $bin_data = fread($blob_file, $piece_size + 1);
                        $bin_data = openssl_decrypt(
                            $bin_data,
                            "AES-256-CBC",
                            $_SERVER["BLOB_KEY"],
                            OPENSSL_RAW_DATA,
                            $blob_chunk->iv
                        );

                        if ($byte_start > 0) {
                            $bin_data = substr($bin_data, $byte_start);
                            $byte_start = 0;
                        }

                        $bytes_to_send = min($content_length, $blob_chunk->size, $piece_size);
                        if (strlen($bin_data) > $bytes_to_send) $bin_data = substr($bin_data, 0, $bytes_to_send);

                        echo $bin_data;
                        flush();

                        $content_length -= $bytes_to_send;
                        $blob_chunk->size -= $bytes_to_send;

                        if ($piece_no == $pieces_per_blob) {
                            $blob_no++;
                            $piece_no = 1;
                            fclose($blob_file);
                            unset($blob_file);
                        } else {
                            $piece_no++;
                        }
                    }

                    if ($blob_file) {
                        fclose($blob_file);
                        unset($blob_file);
                    }
                }
                $byte_start -= $blob_chunk->size;
            }
        }
        break;
    default:
        http_response_code(404);
}
