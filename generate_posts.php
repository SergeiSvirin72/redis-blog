<?php
require_once 'vendor/autoload.php';

require_once 'redis.php';


$r = getRedis();
$client = new GuzzleHttp\Client();

$tags = $r->sAdd('tags', 'development', 'administration', 'design', 'management', 'marketing', 'scipop');

for ($i = 0; $i < 20; $i++) {
    $res = $client->get('https://loripsum.net/api/2/medium');
    $body = $res->getBody();
    $content = $body->getContents();
    $title = 'Title '.($i + 1);
    $createdAt = mt_rand(1, time());

    $postId = $r->incr('post:next_id');
    $r->hMSet('post:'.$postId, [
        'title' => $title,
        'content' => $content,
        'user' => mt_rand(1, 3),
        'votes' => 0,
        'created_at' => $createdAt,
    ]);
    $r->sAdd('posts', $title);
    $r->zAdd('posts:created_at', $createdAt, $postId);
    $r->zAdd('posts:score', 0, $postId);

    $tags = $r->sRandMember('tags', rand(0, 4));
    foreach ($tags as $tag) {
        $r->zAdd('tag:'.$tag, 0, $postId);
    }
    foreach ($tags as $tag) {
        $r->sAdd('post:'.$postId.':tags', $tag);
    }
}
//print_r($postsIds);
