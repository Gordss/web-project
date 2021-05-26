window.addEventListener('DOMContentLoaded', () => {
    document.querySelector('form').addEventListener('submit', uploadArchive);
});

function uploadArchive(event) {
    event.preventDefault();

    let zip = getUploadedFile();
    let zipName = zip['name'];

    const formData = new FormData();
    formData.append('file', zip);

    fetch('upload.php', {
        method: 'POST',
        body: formData
    }).then(response => response.text())
        .then(text => {
            document.querySelector('textarea').innerHTML = text;
            createCsvDownloadLink(text, zipName);
        });
}

function getUploadedFile() {
    return document.getElementById('file-input').files[0];
}

function createCsvDownloadLink(csvContent, zipName) {
    let fileName = zipName.substring(0, zipName.length - 3).concat("csv");

    console.log(fileName);

    var encodedUri = encodeURI(csvContent);
    var link = document.getElementById("download-csv");
    link.setAttribute("href", 'data:text/csv;charset=utf-8,' + encodedUri);
    link.setAttribute("download", fileName);

    document.getElementById("download-link-label").innerText = fileName;

    link.style.display = "inline";
}