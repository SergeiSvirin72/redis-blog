<?php
session_start();

require_once 'redis.php';

if (isLoggedIn()) {
    header("Location: /index.php");
}

if (isset($_POST['submit'])) {
    $r = getRedis();

    $email = strtolower($_POST['email']);
    $password = $_POST['password'];
    $passwordRepeat = $_POST['password_repeat'];

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

    if (empty($errors)) {
        $userId = $r->incr('next_user_id');
        $r->hMSet('user:'.$userId, [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
        $r->zAdd('users', $userId, $email);
        $_SESSION['user'] = $userId;
        header("Location: /index.php");
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
<h1>Register</h1>
<form method="post">
    <table>
        <?php if (!empty($errors)): ?>
            <tr><td colspan="2" style="color: red"><?= $errors[0] ?></td></tr>
        <?php endif; ?>
        <tr>
            <td><label for="email">E-mail*</label></td>
            <td><input type="text" id="email" name="email" value="<?= $_POST['email'] ?: '' ?>"></td>
        </tr>
        <tr>
            <td><label for="password">Password*</label></td>
            <td><input type="password" id="password" name="password"></td>
        </tr>
        <tr>
            <td><label for="password_repeat">Repeat password*</label></td>
            <td><input type="password" id="password_repeat" name="password_repeat"></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" name="submit" value="Register"></td>
        </tr>
    </table>
</form>
</body>
</html>
