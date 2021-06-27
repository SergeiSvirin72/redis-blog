<?php
session_start();

require_once 'redis.php';

if (!isLoggedIn()) {
    header("Location: /login.php");
}

if (isset($_POST['submit'])) {
    $r = getRedis();

    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);

    if (empty($title) || empty($content)) {
        $errors[] = 'Fields with * are required.';
    }
    if (strlen(trim($title)) < 3) {
        $errors[] = 'Title field should be more than 3 characters.';
    }
    if (strlen(trim($content)) < 3) {
        $errors[] = 'Content field should be more than 3 characters.';
    }
    if ($r->sIsMember('posts', $title)) {
        $errors[] = 'Title field should be unique.';
    }

    if (empty($errors)) {
        $createdAt = time();

        $postId = $r->incr('next_post_id');
        $r->hMSet('post:'.$postId, [
            'title' => $title,
            'content' => $content,
            'user' => $_SESSION['user'],
            'votes' => 0,
            'created_at' => $createdAt,
        ]);
        $r->sAdd('posts', $title);
        $r->zAdd('postsByCreatedAt', $createdAt, $postId);
        $r->zAdd('postsByScore', 0, $postId);
        header("Location: /post.php?id=".$postId);
    }
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
<?php require_once 'header.php' ?>
<h1>Create post</h1>
<form method="post">
    <table>
        <?php if (isset($error)): ?>
            <tr><td colspan="2" style="color: red"><?= $error ?></td></tr>
        <?php endif; ?>
        <tr>
            <td><label for="title">Title*</label></td>
            <td><input type="text" id="title" name="title" value="<?= $_POST['title'] ?: '' ?>"></td>
        </tr>
        <tr>
            <td><label for="content">Content*</label></td>
            <td><textarea id="content" name="content"></textarea></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" name="submit" value="Create"></td>
        </tr>
    </table>
</form>
</body>
</html>
