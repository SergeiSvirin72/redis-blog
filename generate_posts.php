<?php
require_once 'vendor/autoload.php';

require_once 'redis.php';


$r = getRedis();
//$client = new GuzzleHttp\Client();
//
//for ($i = 0; $i < 20; $i++) {
//    $res = $client->get('https://loripsum.net/api/2/medium');
//    $body = $res->getBody();
//    $content = $body->getContents();
//    $title = 'Title '.($i + 1);
//    $createdAt = mt_rand(1, time());
//
//    $postId = $r->incr('next_post_id');
//    $r->hMSet('post:'.$postId, [
//        'title' => $title,
//        'content' => $content,
//        'user' => mt_rand(1, 3),
//        'votes' => 0,
//        'created_at' => $createdAt,
//    ]);
//    $r->sAdd('posts', $title);
//    $r->zAdd('postsByCreatedAt', $createdAt, $postId);
//}

//$postsIds = $r->zRange('postsByCreatedAt', 0, -1);
//foreach ($postsIds as $id) {
//    $r->zAdd('postsByScore', 0, $id);
//}
//print_r($postsIds);
