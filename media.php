<?php
    $project_root = $_SERVER['DOCUMENT_ROOT'];
    $media_path = $_SERVER['MEDIA_PATH'];
    $file = $_GET["file"];
    switch($_GET["type"]) {
        case "cover":
            header("Content-Type:image/jpeg");
            readfile(file_exists("$media_path/covers/$file.jpg")
                ? "$media_path/covers/$file.jpg"
                : "$project_root/images/default-cover.jpg"
            );
            break;
        case "star":
            header("Content-Type:image/jpeg");
            readfile(file_exists("$media_path/entities/$file.jpg")
                ? "$media_path/entities/$file.jpg"
                : "$project_root/images/default-star.jpg"
            );
            break;
        case "vid":
            $file_fullpath = "$media_path/vids/$file.mp4";

            $fp = @fopen($file_fullpath, 'rb');
            if(!$fp) {
                http_response_code(404);
                die();
            }

            $file_size = filesize($file_fullpath);
            $length = $file_size;
            $start = 0;
            $end = $file_size - 1;

            header('Content-type: video/mp4');
            header("Accept-Ranges: 0-$length");
            if (isset($_SERVER['HTTP_RANGE'])) {
                $c_start = $start;
                $c_end = $end;

                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                if (strpos($range, ',') !== false) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$file_size");
                    exit;
                }
                if ($range == '-') {
                    $c_start = $file_size - substr($range, 1);
                } else {
                    $range  = explode('-', $range);
                    $c_start = $range[0];
                    $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $file_size;
                }
                $c_end = ($c_end > $end) ? $end : $c_end;
                if ($c_start > $c_end || $c_start > $file_size - 1 || $c_end >= $file_size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$file_size");
                    exit;
                }
                $start = $c_start;
                $end = $c_end;
                $length = $end - $start + 1;
                fseek($fp, $start);
                header('HTTP/1.1 206 Partial Content');
            }
            header("Content-Range: bytes $start-$end/$file_size");
            header("Content-Length: ".$length);

            $buffer = 1024 * 8;
            while(!feof($fp) && ($p = ftell($fp)) <= $end) {
                if ($p + $buffer > $end) {
                    $buffer = $end - $p + 1;
                }
                set_time_limit(0);
                echo fread($fp, $buffer);
                flush();
            }

            fclose($fp);
            break;
        default:
            http_response_code(404);
    }
?>
