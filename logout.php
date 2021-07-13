<?php
include 'redis.php';

$userId = isLoggedIn();

if (!$userId) {
    header('Location: login.php');
}

$r = getRedis();

$authSecret = $r->hGet('user:'.$userId, 'auth');
$r->hDel('user:'.$userId, 'auth');
$r->hDel('auths', $authSecret);

setcookie('auth', '', time()-3600);
header('Location: /login.php');
