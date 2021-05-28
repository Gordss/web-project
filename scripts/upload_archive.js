var delimiter = ',', typeIndex = -1;

window.addEventListener('DOMContentLoaded', () => {
    document.querySelector('form').addEventListener('submit', uploadArchive);
    document.querySelectorAll('input[type=color]').forEach(input => {
        input.addEventListener('input', onColorChange);
    })
});


function onColorChange() {
    document.querySelectorAll('#csv-result-placeholder span').forEach(span => {
        span.style.color = getColourForLine(span.innerHTML, delimiter, typeIndex);
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

    fetch('archive.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        const options = JSON.parse(response.headers.get('X-Applied-Options'));
        delimiter = options.delimiter ? options.delimiter : ',';
        typeIndex = options['included-fields'] ? options['included-fields'].indexOf('type') : -1;

        response.text().then(text => {
            const resultPlaceholder = document.getElementById('csv-result-placeholder');
            resultPlaceholder.innerHTML = '';
            const lines = text.split("\n");
            lines.pop(); // There is an empty line in the end
            lines.forEach(line => {
                const lineElement = document.createElement('span');
                lineElement.style.color = getColourForLine(line, delimiter, typeIndex);
                lineElement.innerHTML = line;
                resultPlaceholder.appendChild(lineElement);
                resultPlaceholder.appendChild(document.createElement('br'));
            })
            if (response.status === 200) {
                createCsvDownloadLink(text, zip['name']);
            }
        });
    })
}

function getColourForLine(line, delimiter, typeIndex) {
    const defaultColor = document.getElementById('default-color').value;
    if (typeIndex < 0) {
        return defaultColor;
    }
    const fileType = line.split(delimiter)[typeIndex].trimEnd().toLowerCase();
    if (['txt', 'md', 'doc', 'docx'].includes(fileType)) {
        return document.getElementById('txt-files-color').value;
    }
    if (['jpg', 'png', 'gif'].includes(fileType)) {
        return document.getElementById('img-files-color').value;
    }
    if ('directory' === fileType) {
        return document.getElementById('dir-files-color').value;
    }
    return defaultColor;
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