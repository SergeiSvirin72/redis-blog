<?php
require_once 'redis.php';
$r = getRedis();
?>
<div>
    <a href="index.php">Home</a> |
    <a href="posts.php">Posts</a> |
    <a href="create_post.php">Create post</a> |
    <?php if (isLoggedIn()): ?>
        <span><?= $r->hGet('user:'.$_SESSION['user'], 'email'); ?></span> |
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a> |
        <a href="register.php">Register</a>
    <?php endif; ?>
</div>
