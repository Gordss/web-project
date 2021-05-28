<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zip to CSV Converter</title>

    <link rel="stylesheet" href="styles/style.css">
    <script src="scripts/upload_archive.js"></script>
</head>
<body>
<header id="main-header">
    <a href=""><h1>Zip to CSV Converter</h1></a>
</header>
<main>
    <?php
    session_start();
    $destination = isset($_SESSION['username']) ? 'upload_page.php' : 'login.php';
    header("Location: $destination");
    ?>
</main>
</body>
</html>