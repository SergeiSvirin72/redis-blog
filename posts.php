<?php
session_start();

require_once 'redis.php';

$isLoggedIn = isLoggedIn();

$r = getRedis();

$posts = [];
$postsByCreatedAt = $r->zRange('postsByCreatedAt', 0, -1);
foreach ($postsByCreatedAt as $postByCreatedAt) {
    $post = $r->hGetAll('post:'.$postByCreatedAt);
    $post['id'] = $postByCreatedAt;
    $posts[] = $post;
}
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
<h1>Posts</h1>
<div>
    <?php foreach ($posts as $post): ?>
        <div>
            <h2><?= $post['title'] ?></h2>
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 50%"><?= $post['content'] ?></div>
            <a href="/post.php?id=<?= $post['id'] ?>">Read more...</a>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
