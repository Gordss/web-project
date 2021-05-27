var currentTypeIndex = undefined,
    currentDelimiter = ',';

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

    const customDelimiter = document.querySelector('input[name=delimiter]').value;
    if (customDelimiter.length === 1) {
        currentDelimiter = customDelimiter;
    } else {
        currentDelimiter = ',';
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
                resultPlaceholder.appendChild(document.createElement('br'));
            })
            if (response.status === 200) {
                createCsvDownloadLink(text, zip['name']);
            }
        });
    })
}

function setTypeIndex(headerLine) {
    const fields = headerLine.trim().split(currentDelimiter);
    for (let i = 0; i < fields.length; i++) {
        if (fields[i] === 'type') {
            currentTypeIndex = i;
            return;
        }
        currentTypeIndex = undefined;
    }
}

function getColourForLine(line) {
    const defaultColor = document.getElementById('default-color').value;
    if (typeof currentTypeIndex !== 'number') {
        return defaultColor;
    }
    const fileType = line.split(currentDelimiter)[currentTypeIndex].trimEnd().toLowerCase();
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