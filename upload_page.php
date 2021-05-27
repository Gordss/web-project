<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TODO add title</title>

    <link rel="stylesheet" href="styles/style.css">
    <script src="scripts/upload_archive.js"></script>
</head>
<body>
<header id="main-header">
    <a href=""><h1>Zip to ... Converter</h1></a>
    <h3>Greetings,
        <?php
        session_start();
        echo $_SESSION['username']; ?>
    </h3>
    <nav>
        <ul id="navigation-list">
            <li>
                <a href="logout.php">Log out</a>
            </li>
        </ul>
    </nav>
</header>
<main>
    <table>
        <tr>
            <td>
                <section>
                    <form enctype="multipart/form-data">
                        <label>
                            <h3>Choose a zip file to upload & convert to CSV:</h3>
                        </label>
                        <input id="file-input" type="file" name="file">
                        <button id="file-input-btn" type="submit">Convert</button>
                    </form>
                </section>

            </td>
            <td>
                <h3 id="download-label">Converted CSV file:</h3>
                <a id="download-csv"><h3 id="download-link-label"></h3></a>
                <textarea>
                </textarea>
            </td>
        </tr>
    </table>
</main>
</body>
</html>