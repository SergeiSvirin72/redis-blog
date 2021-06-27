<?php
session_start();

require_once 'redis.php';

function login($email, $password) {
    $r = getRedis();
    $email = strtolower($email);

    $userId = $r->zScore('users', $email);
    if (!$userId) {
        return false;
    }

    $passwordHash = $r->hGet('user:'.$userId, 'password');
    if (!$passwordHash) {
        return false;
    }

    $passwordVerify = password_verify($password, $passwordHash);
    if (!$passwordVerify) {
        return false;
    }

    return $userId;
}

if (isLoggedIn()) {
    header("Location: /index.php");
}

if (isset($_POST['submit'])) {
    if ($userId = login($_POST['email'], $_POST['password'])) {
        $_SESSION['user'] = $userId;
        header("Location: /index.php");
    } else {
        $error = 'Entered credentials are invalid.';
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
<h1>Login</h1>
<form method="post">
    <table>
        <?php if (isset($error)): ?>
            <tr><td colspan="2" style="color: red"><?= $error ?></td></tr>
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
            <td colspan="2"><input type="submit" name="submit" value="Login"></td>
        </tr>
    </table>
</form>
</body>
</html>
