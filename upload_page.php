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
                    <legend>
                        Conversion Options
                        <span
                                class="tooltip"
                                data-title="A JSON string for configuring options for the zip->CSV conversion"
                                data-delimiter="    &quot;delimiter&quot; (string) - will be used to separate the data on each row."
                                data-fields="    &quot;included-fields&quot; - (array of strings) - what file properties to include in the result CSV. Possible values are: &quot;id&quot;, &quot;parent_id&quot;, &quot;name&quot;, &quot;type&quot;, &quot;parent-type&quot;, &quot;content-length&quot;, &quot;md5_sum&quot;, &quot;is_leaf&quot;, &quot;css&quot;."
                                data-header="    &quot;included-header&quot; (boolean) - whether to include a header line in result CSV"
                                data-uppercase="    &quot;uppercase&quot; - (boolean) - whether to convert the result CSV to uppercase."
                                data-is-leaf="    &quot;is-leaf-numeric&quot; - (boolean) - whether to use 0/1 instead of false/true for the &quot;is_leaf&quot; field value"
                                data-zip-name="    &quot;skip-zip-filename&quot; - (boolean) - whether to skip the zip filename in the files' names"
                                data-css="    &quot;css-directory&quot; / &quot;css-text-file&quot; / &quot;css-image-file&quot; / &quot;css-default&quot; - (string) - value of the &quot;css&quot; column for the corresponding file type">
                            <img alt="info icon" id="info_icon" src="images/info_btn.png"/>
                        </span>
                        <a id="options_info"></a>
                    </legend>
                    <textarea id="options-input" name="options" cols="40" rows="10"></textarea>
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
            <section id="download-links-section">
                <a id="download-csv"><h3 id="download-link-label"></h3></a>
                <a id="download-html" onclick="updateDownloadHTMLLink()"><h3 id="html-download-link-label"></h3></a>
            </section>
            <p id="csv-result-placeholder"></p>
        </section>
    </section>
</main>
</body>
</html>