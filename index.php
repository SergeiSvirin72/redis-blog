<?php
const ARTICLES_PER_PAGE = 3;

session_start();

require_once 'redis.php';

$page = (integer) $_GET['page'];
if (!is_int($page) || $page <= 0) {
    $page = 1;
}

$sort = strtolower($_GET['sort']);
switch ($sort) {
    case 'byscore':
        $sort = 'ByScore';
        break;
    default:
        $sort = 'ByCreatedAt';
}

$r = getRedis();

$posts = [];

$start = ($page-1) * ARTICLES_PER_PAGE;
$end = $start + ARTICLES_PER_PAGE - 1;

$postsKey = 'posts'.$sort;
$postsRange = $r->zRevRange($postsKey, $start, $end);

foreach ($postsRange as $postsRangeItem) {
    $post = $r->hGetAll('post:'.$postsRangeItem);
    $post['id'] = $postsRangeItem;
    $post['user'] = $r->hGet('user:'.$post['user'], 'email');
    $post['score'] = $r->zScore('postsByScore', $postsRangeItem);
    $posts[] = $post;
}

$pagesNum = ceil($r->zCard($postsKey) / ARTICLES_PER_PAGE);
$pages = range(1, $pagesNum);
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
<?php require_once 'header.php' ?>
<h1>Posts</h1>
<div>
    <a href="index.php?sort=ByCreatedAt">Latest</a> |
    <a href="index.php?sort=ByScore">Top</a>
</div>
<div>
    <?php foreach ($posts as $post): ?>
        <div>
            <h2><?= $post['title'] ?></h2>
            <div style="color:gray"><?= 'Posted by '.$post['user'].' at '.date('m/d/Y', $post['created_at']) ?></div>
            <div style="color:forestgreen">Score <?= $post['score'] ?> within <?= $post['votes'] ?> votes.</div>
            <div style="display:-webkit-box;max-width:600px;width:100%;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                <?= $post['content'] ?>
            </div>
            <a href="/post.php?id=<?= $post['id'] ?>">Read more...</a>
        </div>
    <?php endforeach; ?>
</div>
<div>
    <?php
        foreach ($pages as $key => $value) {
            echo '<a href="index.php?sort='.$sort.'&page='.$value.'">'.$value.'</a>';
            if ($key !== array_key_last($pages)) {
                echo ' | ';
            }
        }
    ?>
</div>
</body>
</html>
