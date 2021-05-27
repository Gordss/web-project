<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TODO add title</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<?php
require 'header.php';
?>
<main>
    <?php
    require_once 'Storage.php';

    session_start();
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $userIsValid = Storage::getInstance()->verifyUserCredentials($username, $password);
        if ($userIsValid) {
            $_SESSION['username'] = $username;
            header('Location: index.php');
        } else {
            echo "<script>alert('Invalid combination of user and password')</script>"; // TODO better error msg
        }
    }
    ?>
    <section class="credentials-section">
        <form class="credentials-form" method="post" action="">
            <input type="text" name="username" placeholder="Username"/>
            <input type="password" name="password" placeholder="Password"/>
            <input type="submit" value="Login"/>
        </form>
        <p>Not registered yet? <a href="register.php">Register here</a></p>
    </section>
</main>
</body>
</html>