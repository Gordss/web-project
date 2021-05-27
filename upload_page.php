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
                        <div class="options-container">
                            <fieldset id="include-options-fieldset">
                                <legend>Include fields</legend>
                                <label>
                                    Name
                                    <input type="checkbox" name="include-name" checked/>
                                </label>
                                <label>
                                    Parent Name
                                    <input type="checkbox" name="include-parent-name" checked/>
                                </label>
                                <label>
                                    Content Length
                                    <input type="checkbox" name="include-content-length" checked/>
                                </label>
                                <label>
                                    Type
                                    <input type="checkbox" name="include-type" checked/>
                                </label>
                                <label>MD5 sum
                                    <input type="checkbox" name="include-md5_sum" checked/>
                                </label>
                            </fieldset>
                            <fieldset>
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
                        </div>
                        <div class="options-container">
                            <fieldset id="additional-options">
                                <legend>Additional options</legend>
                                <label>Include header
                                    <input type="checkbox" name="include-header" checked/>
                                </label>
                                <input type="text" name="delimiter" placeholder="Custom delimiter" maxlength="1">
                            </fieldset>
                        </div>
                        <button id="file-input-btn" type="submit">Convert</button>
                    </form>
                </section>

            </td>
            <td>
                <h3 id="download-label">Converted CSV file:</h3>
                <a id="download-csv"><h3 id="download-link-label"></h3></a>
                <p id="csv-result-placeholder">

                </p>
            </td>
        </tr>
    </table>
</main>
</body>
</html>