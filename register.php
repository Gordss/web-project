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
        <form class="credentials-form" method="POST" action="">
            <input type="text" name="username" placeholder="Username"/>
            <input type="password" name="password" placeholder="Password"/>
            <input type="submit" value="Register"/>
        </form>
        <p>Already registered? <a href="login.php">Log in here</a></p>
        <?php
        require_once "Storage.php";

        if (!isset($_POST['username']) || !isset($_POST['password']) || empty($_POST['username']) || empty($_POST['password'])) {
            die;
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        $error = Storage::getInstance()->registerUser($username, $password);
        if (empty($error)) {
            header('Location: login.php');
            die;
        }
        ?>
        <div class="error">Registration unsuccessful: Username is already taken</div>
    </section>
</main>
</body>
</html>