<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zip to CSV converter</title>

    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/history.css">

    <script src="scripts/history.js"></script>
</head>
<body>
<header id="main-header">
    <a href="upload_page.php"><h1>Zip to CSV Converter</h1></a>
    <h3>Greetings,
        <?php
        session_start();
        if (!isset($_SESSION['username'])) {
            header('Location: login.php');
        }
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
            <td>Archive Name</td>
            <td>MD5 sum</td>
            <td>Uploaded at</td>
            <td colspan=2>Actions</td>
        </tr>
        <?php
        require "Storage.php";
        $archives = Storage::getInstance()->fetchArchivesForUser($_SESSION['username']);
        foreach ($archives as $archive) {
            $id = $archive['id'];
            $time = $archive['uploaded_at'];
            $name = $archive['name'];
            $md5_sum = $archive['md5_sum'];
            echo <<<TABLEROW
            <tr>
                <td>$id</td>
                <td>$name</td>
                <td>$md5_sum</td>
                <td>$time</td>
                <td class="actions-td"><a id="archive-$id-csv-link" class="archive-csv-link" href="">Download</a></td>
                <td class="actions-td"><a id="archive-$id-delete-link" class="archive-delete-link" href="">Delete</a></td>
            </tr>
            TABLEROW;
        }
        ?>
    </table>
</main>
</body>
</html>