<?php
session_start();

require_once 'redis.php';

$isLoggedIn = isLoggedIn();

$r = getRedis();

$post = $r->hGetAll('post:'.$_GET['id']);
if (!$post) {
    header("Location: /posts.php");
}
$user = $r->hGet('user:'.$post['user'], 'email');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h1><?= $post['title'] ?></h1>
<div>
    <div style="color: gray"><?= 'Posted by '.$user.' at '.date('m/d/Y', $post['created_at']) ?></div>
    <div style="max-width: 50%"><?= $post['content'] ?></div>
</div>
</body>
</html>
