<?php

if (!$_SESSION['auth']) {
    include('public/login.html');
    exit();
}