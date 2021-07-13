<?php

function getRedis(): Redis
{
    static $r = false;

    if ($r) {
        return $r;
    }

    $r = new Redis();
    $r->connect('127.0.0.1', 6379);

    return $r;
}

function getUserByCredentials(string $email, string $password): ?int
{
    $r = getRedis();

    $email = strtolower($email);

    $userId = $r->zScore('users', $email);
    if (!$userId) {
        return null;
    }

    $passwordHash = $r->hGet('user:'.$userId, 'password');
    if (!$passwordHash) {
        return null;
    }

    $passwordVerify = password_verify($password, $passwordHash);
    if (!$passwordVerify) {
        return null;
    }

    return $userId;
}

function isLoggedIn(): ?int
{
    $r = getRedis();

    $authSecret = $_COOKIE['auth'];
    if (empty($authSecret)) {
        return null;
    }

    $userId = $r->hGet('auths', $authSecret);
    if (!$userId) {
        return null;
    }

    $expectedAuthSecret = $r->hGet('user:'.$userId, 'auth');
    if ($authSecret !== $expectedAuthSecret) {
        return null;
    }

    return $userId;
}

function generateRandomString(int $length = 60): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

function authUser(int $userId): void
{
    $r = getRedis();

    $authsecret = generateRandomString();
    $year = 3600 * 24 * 365;

    $r->hSet('user:'.$userId, 'auth', $authsecret);
    $r->hSet('auths', $authsecret, $userId);

    setcookie('auth', $authsecret, time() + $year);

    header('Location: /index.php');
}

function validateRegister(string $email, string $password, string $passwordRepeat): array
{
    $r = getRedis();
    $errors = [];

    $email = strtolower($email);

    if (empty($email) || empty($password) || empty($passwordRepeat)) {
        $errors[] = 'Fields with * are required.';
    }
    if (strlen(trim($password)) < 8) {
        $errors[] = 'Password field should be more than 8 characters.';
    }
    if ($password !== $passwordRepeat) {
        $errors[] = 'Password and repeat fields don\'t match.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid e-mail address.';
    }
    if ($r->zScore('users', $email)) {
        $errors[] = 'Entered e-mail address is already in use.';
    }

    return $errors;
}

function registerUser(string $email, string $password): int
{
    $r = getRedis();

    $email = strtolower($email);

    $userId = $r->incr('user:next_id');
    $r->hMSet('user:'.$userId, [
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ]);
    $r->zAdd('users', $userId, $email);

    return $userId;
}