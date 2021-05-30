<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zip to CSV Converter</title>

    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<header id="main-header">
    <a href=""><h1>Zip to CSV Converter</h1></a>
</header>
<main>
    <section class="credentials-section">
        <form class="credentials-form" method="POST" action="">
            <input type="text" name="username" placeholder="Username"/>
            <input type="password" name="password" placeholder="Password"/>
            <input type="submit" value="Register"/>
        </form>
        <p>Already registered? <a href="login.php">Log in here</a></p>
        <?php

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die;
        }

        require_once "Storage.php";

        if (!isset($_POST['username']) || !isset($_POST['password']) || empty($_POST['username']) || empty($_POST['password'])) {
            printError('Both username and password have to be set');
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        if (strlen($username) < 8) {
            printError('Username must be at least 8 characters in length');
        }

        if (!preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $password)) {
            printError('Password must be at least 8 characters in length and contain at least one number, one upper case letter, one lower case letter and one special character.');
        }

        $error = Storage::getInstance()->registerUser($username, $password);
        if (!empty($error)) {
            printError('This username is already taken');
        }
        header('Location: login.php');

        function printError($error)
        {
            echo '<div class="error">' . $error . '</div>';
            die;
        }

        ?>
    </section>
</main>
</body>
</html>