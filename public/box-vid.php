<?php

function print_vid_box($id) {
    echo "
        <a class='poster vid-box' href='/vid/$id'>
            <p>$id</p>
            <p class='border'>$id</p>
            <img class='poster' src='/media/covers/$id.jpg'>
        </a>";
}