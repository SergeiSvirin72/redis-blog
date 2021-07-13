<?php
const ARTICLES_PER_PAGE = 3;

require_once 'redis.php';

$r = getRedis();

// Validate GET parameters
$page = (integer) $_GET['page'];
if (!is_int($page) || $page <= 0) {
    $page = 1;
}

$sort = strtolower($_GET['sort']);
switch ($sort) {
    case 'score':
        break;
    default:
        $sort = 'created_at';
}

$tags = $r->sMembers('tags');
$selectedTags = $_GET['tags'];
$params = '';
foreach ($selectedTags as $key => $selectedTag) {
    if (!in_array($selectedTag, $tags)) {
        unset($selectedTags[$key]);
    } else {
        $params .= '&tags[]='.$selectedTag;
    }
}

$posts = [];

$start = ($page-1) * ARTICLES_PER_PAGE;
$end = $start + ARTICLES_PER_PAGE - 1;

$postsKey = 'posts:'.$sort;
// Array of intersecting sets
$tagsPostSets = [$postsKey];
foreach ($selectedTags as $selectedTag) {
    // Make key for new sorted set of intersected posts
    $postsKey .= ':'.$selectedTag;
    // Push for each selected category
    $tagsPostSets[] = 'tag:'.$selectedTag;
}

if (!$r->exists($postsKey)) {
    $r->zInterStore($postsKey, $tagsPostSets);
    $r->expire($postsKey, 10);
}

$filteredPostsRange = $r->zRevRange($postsKey, $start, $end);

foreach ($filteredPostsRange as $postsRangeItem) {
    $post = $r->hGetAll('post:'.$postsRangeItem);
    $post['id'] = $postsRangeItem;
    $post['user'] = $r->hGet('user:'.$post['user'], 'email');
    $post['score'] = $r->zScore('posts:score', $postsRangeItem);
    $post['tags'] = $r->sMembers('post:'.$postsRangeItem.':tags');
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
    <a href="posts.php?sort=created_at<?= $params ?>">Latest</a> |
    <a href="posts.php?sort=score<?= $params ?>">Top</a>
</div>
<div>
    <form>
        <input type="hidden" name="sort" value="<?= $sort ?>">
        <?php foreach ($tags as $tag): ?>
            <div style="display:inline-block">
                <input type="checkbox"
                       id="<?= $tag ?>"
                       name="tags[]"
                       <?php if (in_array($tag, $selectedTags)) echo ' checked'; ?>
                       value="<?= $tag ?>">
                <label for="<?= $tag ?>"><?= $tag ?></label>
            </div>
        <?php endforeach; ?>
        <input type="submit" name="submit" value="Select">
    </form>
</div>
<div>
    <?php foreach ($posts as $post): ?>
        <div>
            <h2><?= $post['title'] ?></h2>
            <div style="color:gray"><?= 'Posted by '.$post['user'].' at '.date('m/d/Y', $post['created_at']) ?></div>
            <div style="color:slategrey">Tags:
                <?php
                    foreach ($post['tags'] as $key => $tag) {
                        echo $tag;
                        if ($key !== array_key_last($post['tags'])) echo ' , ';
                    }
                ?>
            </div>
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
            echo '<a href="posts.php?sort='.$sort.'&page='.$value.$params.'">'.$value.'</a>';
            if ($key !== array_key_last($pages)) echo ' | ';
        }
    ?>
</div>
</body>
</html>
