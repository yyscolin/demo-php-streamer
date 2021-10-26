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
            $file_size = filesize($file_fullpath);
        } else {
            $piece_size = 512 * 1024 - 1;
            $pieces_per_blob = 256;

            $db_query = "select * from media_files where fid=(select fid from vid_media where vid=? and part=?)";
            $stmt = $con->prepare($db_query);
            $stmt->bind_param('ss', $vid, $part);
            $stmt->execute();
            $db_response = $stmt->get_result();
            // $db_row = $db_response->fetch_assoc();
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
            $fp = fopen($file_fullpath, 'rb');
            if ($byte_start > 0) fseek($fp, $byte_start);
            while(!feof($fp) && ($p = ftell($fp)) <= $byte_end) {
                if ($p + $buffer_size > $byte_end) $buffer_size = $byte_end - $p + 1;
                set_time_limit(0);
                echo fread($fp, $buffer_size);
                flush();
            }
            fclose($fp);
        } else {
            foreach ($blob_chunks as $blob_chunk) {
                if ($byte_start <= $blob_chunk->size) {
                    $piece_no = $blob_chunk->head_piece;
                    if ($byte_start > 0) {
                        $piece_no += floor($byte_start / $piece_size);
                        $byte_start = $byte_start % $piece_size;
                    }

                    while ($content_length > 0 && $blob_chunk->size > 0) {
                        set_time_limit(0);
                        $blob_no = floor($piece_no / $pieces_per_blob);
                        $piece_index = $piece_no % $pieces_per_blob;

                        if (!isset($blob_opened) || $blob_opened !== $blob_no) {
                            if (isset($blob_opened)) fclose($blob_stream);
                            $blob_stream = fopen($_SERVER['BLOB_PATH']."/$blob_no", "rb");
                            $blob_opened = $blob_no;
                            if ($piece_index > 0) fseek($blob_stream, $piece_index * ($piece_size + 1));
                        }

                        $bin_data = fread($blob_stream, $piece_size + 1);
                        $bin_data = openssl_decrypt(
                            $bin_data,
                            "AES-256-CBC",
                            file_get_contents($_SERVER["BLOB_KEY"]),
                            OPENSSL_RAW_DATA,
                            $iv
                        );

                        if ($byte_start > 0) {
                            $bin_data = substr($bin_data, $byte_start);
                            $byte_start = 0;
                        }

                        $bytes_to_send = min($content_length, $blob_chunk->size, $piece_size);
                        if (strlen($bin_data) > $bytes_to_send) $bin_data = substr($bin_data, 0, $bytes_to_send);

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

                        $content_length -= $bytes_to_send;
                        $blob_chunk->size -= $bytes_to_send;
                        $piece_no++;
                    }

                    fclose($blob_stream);
                    unset($blob_opened);
                }
                $byte_start -= $blob_chunk->size;
            }
        }
        break;
    default:
        http_response_code(404);
}
