<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zip to CSV converter</title>

    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/history.css">
</head>
<body>
<header id="main-header">
    <a href=""><h1>Zip to CSV Converter</h1></a>
    <h3>Greetings,
        <?php
        session_start();
        echo $_SESSION['username']; ?>
    </h3>
    <nav>
        <ul id="navigation-list">
            <li>
                <a href="upload_page.php">Upload</a>
            </li>
            <li>
                <a href="history.php">Upload history</a>
            </li>
            <li>
                <a href="logout.php">Log out</a>
            </li>
        </ul>
    </nav>
</header>
<main>
    <table>
        <caption>Uploaded archives</caption>
        <tr>
            <td>Archive ID</td>
            <td>Uploaded at</td>
            <td>More</td>
        </tr>
        <?php
        require "Storage.php";
        $archives = Storage::getInstance()->fetchArchivesForUser($_SESSION['username']);
        foreach ($archives as $archive) {
            $id = $archive['id'];
            $time = $archive['uploaded_at'];
            echo <<<TABLEROW
            <tr>
                <td>$id</td>
                <td>$time</td>
                <td><a href="archive.php?id=$id">Files</a></td>
            </tr>
            TABLEROW;
        }
        ?>
    </table>
</main>
</body>
</html>