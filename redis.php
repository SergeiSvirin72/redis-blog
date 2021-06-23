<?php

function getRedis() {
    static $r = false;

    if ($r) {
        return $r;
    }

    $r = new Redis();
    $r->connect('127.0.0.1', 6379);
    return $r;
}

function isLoggedIn() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }

    if (!isset($_SESSION['user'])) {
        return false;
    }

    return $_SESSION['user'];
}
