var currentTypeIndex = undefined;

window.addEventListener('DOMContentLoaded', () => {
    document.querySelector('form').addEventListener('submit', uploadArchive);
    document.querySelectorAll('input[type=color]').forEach(input => {
        input.addEventListener('input', onColorChange);
    })
});


function onColorChange() {
    document.querySelectorAll('#csv-result-placeholder span').forEach(span => {
        span.style.color = getColourForLine(span.innerHTML);
    });
}

function uploadArchive(event) {
    event.preventDefault();

    let zip = getUploadedFile();
    if (!zip) {
        return;
    }

    const formData = new FormData(document.querySelector('form'));
    formData.append('file', zip);

    fetch('upload.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        response.text().then(text => {
            const resultPlaceholder = document.getElementById('csv-result-placeholder');
            resultPlaceholder.innerHTML = '';
            const lines = text.split("\n");
            lines.pop(); // There is an empty line in the end
            setTypeIndex(lines[0]);
            lines.forEach(line => {
                const lineElement = document.createElement('span');
                lineElement.style.color = getColourForLine(line);
                lineElement.innerHTML = line;
                resultPlaceholder.appendChild(lineElement);
            })
            if (response.status === 200) {
                createCsvDownloadLink(text, zip['name']);
            }
        });
    })
}

function setTypeIndex(headerLine) {
    const fields = headerLine.split(',');
    for (let i = 0; i < fields.length; i++) {
        if (fields[i] === 'type') {
            currentTypeIndex = i;
            return;
        }
    }
    currentTypeIndex = undefined;
}

function getColourForLine(line) {
    const defaultColor = document.getElementById('default-color').value;
    if (!currentTypeIndex) {
        return defaultColor;
    }
    switch (line.split(',')[currentTypeIndex].toLowerCase()) {
        case 'txt':
        case 'md':
        case 'doc':
        case 'docx':
            return document.getElementById('txt-files-color').value;
        case 'jpg':
        case 'png':
        case 'gif':
            return document.getElementById('img-files-color').value;
        case 'directory':
            return document.getElementById('dir-files-color').value;
        default:
            return defaultColor;
    }
}

function getUploadedFile() {
    return document.getElementById('file-input').files[0];
}

function createCsvDownloadLink(csvContent, zipName) {
    let fileName = zipName.substring(0, zipName.length - 3).concat("csv");

    var encodedUri = encodeURI(csvContent);
    var link = document.getElementById("download-csv");
    link.setAttribute("href", 'data:text/csv;charset=utf-8,' + encodedUri);
    link.setAttribute("download", fileName);

    document.getElementById("download-link-label").innerText = fileName;

    link.style.display = "inline";
}