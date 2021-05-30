<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zip to CSV converter</title>

    <link rel="stylesheet" href="styles/style.css">
    <script src="scripts/upload_archive.js"></script>
</head>
<body>
<header id="main-header">
    <a href=""><h1>Zip to CSV Converter</h1></a>
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
    <section id="upload-section">
        <section id="upload-input-subsection">
            <form enctype="multipart/form-data">
                <label for="file-input">Choose a zip file to upload & convert to CSV:</label>
                <input id="file-input" type="file" name="file">
                <fieldset class="options-fieldset">
                    <legend>Conversion Options</legend>
                    <textarea id="options-input" name="options" cols="40" rows="20"></textarea>
                </fieldset>
                <fieldset class="options-fieldset">
                    <legend>Visualization options</legend>
                    <label>
                        Text files
                        <input id="txt-files-color" type="color"/>
                    </label>
                    <label>
                        Image files
                        <input id="img-files-color" type="color"/>
                    </label>
                    <label>
                        Directories
                        <input id="dir-files-color" type="color"/>
                    </label>
                    <label>
                        Default
                        <input id="default-color" type="color"/>
                    </label>
                </fieldset>
                <button id="file-input-btn" type="submit">Convert</button>
            </form>
        </section>
        <section id="upload-output-subsection">
            <h3 id="download-label">Converted CSV file:</h3>
            <a id="download-csv"><h3 id="download-link-label"></h3></a>
            <p id="csv-result-placeholder"></p>
        </section>
    </section>
</main>
</body>
</html>