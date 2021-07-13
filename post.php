<?php
require_once 'redis.php';

$isLoggedIn = isLoggedIn();

$r = getRedis();

$id = $_GET['id'];

$post = $r->hGetAll('post:'.$id);
$tags = $r->sMembers('post:'.$id.':tags');

if (!$post) {
    header("Location: /posts.php");
}

$isUpvoted = $r->sIsMember('upvoted:'.$_GET['id'], $post['user']);
$isDownvoted = $r->sIsMember('downvoted:'.$_GET['id'], $post['user']);

if (isset($_POST['submit']) && isset($_POST['vote'])) {
    $vote = $_POST['vote'];

    if ($isUpvoted && $vote === 'down') {
        $r->sMove('upvoted:'.$id, 'downvoted:'.$id, $post['user']);
        $r->zIncrBy('posts:score', -2, $id);
        $isUpvoted = !$isUpvoted;
        $isDownvoted = !$isDownvoted;
    } elseif ($isDownvoted && $vote === 'up') {
        $r->sMove('downvoted:'.$id, 'upvoted:'.$id, $post['user']);
        $r->zIncrBy('posts:score', 2, $id);
        $isUpvoted = !$isUpvoted;
        $isDownvoted = !$isDownvoted;
    } elseif (!$isUpvoted && !$isDownvoted) {
        $r->hIncrBy('post:'.$id, 'votes', 1);
        if ($vote === 'up') {
            $r->sAdd('upvoted:'.$id, $post['user']);
            $r->zIncrBy('posts:score', 1, $id);
            $isUpvoted = !$isUpvoted;
        } elseif ($vote === 'down') {
            $r->sAdd('downvoted:'.$id, $post['user']);
            $r->zIncrBy('posts:score', -1, $id);
            $isDownvoted = !$isDownvoted;
        }
    }
}

$post = $r->hGetAll('post:'.$id);
$user = $r->hGet('user:'.$post['user'], 'email');
$score = $r->zScore('posts:score', $id);
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
<h1><?= $post['title'] ?></h1>
<div>
    <div style="color:gray"><?= 'Posted by '.$user.' at '.date('m/d/Y', $post['created_at']) ?></div>
    <div style="max-width:600px;width:100%"><?= $post['content'] ?></div>
    <div style="color:slategrey">Tags:
        <?php
            foreach ($tags as $key => $tag) {
                echo $tag;
                if ($key !== array_key_last($tags)) echo ' , ';
            }
        ?>
    </div>
</div>
<div>
    <div style="color:forestgreen">Score <?= $score ?> within <?= $post['votes'] ?> votes.
        <?php
            if ($isUpvoted) echo 'You voted up.';
            elseif ($isDownvoted) echo 'You voted down.';
        ?>
    </div>
    <div>
        <form method="post">
            <input type="radio" id="up" name="vote" value="up"><label for="up">Up</label>
            <input type="radio" id="down" name="vote" value="down"><label for="down">Down</label>
            <input type="submit" name="submit" value="Vote">
        </form>
    </div>
</div>
</body>
</html>
