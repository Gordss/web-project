<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TODO add title</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<header id="main-header">
    <a href=""><h1>Zip to ... Converter</h1></a>
</header>
<main>
    <section class="credentials-section">
        <form class="credentials-form" method="post" action="">
            <input type="text" name="username" placeholder="Username"/>
            <input type="password" name="password" placeholder="Password"/>
            <input type="submit" value="Login"/>
        </form>
        <p>Not registered yet? <a href="register.php">Register here</a></p>
    </section>
    <?php
    require_once 'Storage.php';

    if (!isset($_POST['username']) || !isset($_POST['password']) || empty($_POST['username'] || empty($_POST['password']))) {
        die;
    }

    session_start();
    $username = $_POST['username'];
    $password = $_POST['password'];
    $userIsValid = Storage::getInstance()->verifyUserCredentials($username, $password);
    if ($userIsValid) {
        $_SESSION['username'] = $username;
        header('Location: index.php');
    }
    ?>
    <div class="error">An invalid combination of username and password was entered</div>
</main>
</body>
</html>