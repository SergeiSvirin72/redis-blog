<?php
require_once 'redis.php';

if (isLoggedIn()) {
    header('Location: /index.php');
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordRepeat = $_POST['password_repeat'];

    $errors = validateRegister($email, $password, $passwordRepeat);

    if (empty($errors)) {
        $userId = registerUser($email, $password);
        authUser($userId);
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
<h1>Register</h1>
<form method="post">
    <table>
        <?php if (!empty($errors)): ?>
            <tr><td colspan="2" style="color:red"><?= $errors[0] ?></td></tr>
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
