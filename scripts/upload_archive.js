var delimiter = ',', typeIndex = -1;
const MAX_FILE_SIZE_BYTES = 2097152;

window.addEventListener('DOMContentLoaded', () => {
    document.querySelector('form').addEventListener('submit', uploadArchive);
    document.querySelectorAll('input[type=color]').forEach(input => {
        input.addEventListener('input', onColorChange);
    })

    document.querySelector('#options-input').innerHTML = `{
        "delimiter": ",",
        "included-fields": ["id","parent_id","name","type","parent-name","content-length","md5_sum","is_leaf"],
        "include-header": true,
        "uppercase": false
}`; // Default JSON for the input
});


function onColorChange() {
    document.querySelectorAll('#csv-result-placeholder span').forEach(span => {
        span.style.color = getColourForLine(span.innerHTML, delimiter, typeIndex);
    });
}

function uploadArchive(event) {

    event.preventDefault();
    document.getElementById("download-csv").style.display = 'none';

    var resultPlaceholder = document.getElementById('csv-result-placeholder');

    let zip = getUploadedFile();
    if (!zip) {
        terminateRequest('A file must be uploaded for conversion');
        return;
    }

    if (zip.size > MAX_FILE_SIZE_BYTES) {
        terminateRequest(`The uploaded archive's size must not exceed ${MAX_FILE_SIZE_BYTES} bytes`);
        return;
    }

    const formData = new FormData(document.querySelector('form'));

    var requestedDelimiter;
    try {
        const options = JSON.parse(formData.get('options'));
        requestedDelimiter = options?.delimiter ? options.delimiter : ',';
    } catch (e) {
        terminateRequest('The options for the conversion must be a valid JSON string');
        return;
    }
    const nameWithoutExtension = zip.name.split('.').slice(0, -1).join('');
    if (nameWithoutExtension.includes(requestedDelimiter) || nameWithoutExtension.includes('.')
        || nameWithoutExtension.includes(',')) {
        const msgAddition = requestedDelimiter !== ',' ? ` and '${requestedDelimiter}'` : '';
        terminateRequest(`The uploaded file's name must not contain the following symbols: '.', ',' ${msgAddition}`);
        return;
    }

    formData.append('file', zip);

    fetch('archive.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        response.text().then(text => {
            if (response.status !== 200) {
                terminateRequest(text);
                return;
            }

            console.log(text);
            resultPlaceholder.innerHTML = '';
            resultPlaceholder.style.color = 'white';

            const options = JSON.parse(response.headers.get('X-Applied-Options'));
            delimiter = options.delimiter ? options.delimiter : ',';
            typeIndex = options['included-fields'] ? options['included-fields'].indexOf('type') : -1;

            const lines = text.split("\n");
            lines.pop(); // There is an empty line in the end
            lines.forEach(line => {
                const lineElement = document.createElement('span');
                lineElement.style.color = getColourForLine(line, delimiter, typeIndex);
                lineElement.innerHTML = line;
                resultPlaceholder.appendChild(lineElement);
                resultPlaceholder.appendChild(document.createElement('br'));
            })
            createCsvDownloadLink(text, zip['name']);
            updateDownloadHTMLLink();
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
    const fileName = zipName.substring(0, zipName.length - 3).concat("csv");
    document.getElementById("download-link-label").innerText = fileName;

    let link = document.getElementById("download-csv");
    link.setAttribute("href", 'data:text/csv;charset=utf-8,' + encodeURI(csvContent));
    link.setAttribute("download", fileName);
    link.style.display = "inline";
}

function updateDownloadHTMLLink() {
    const zipName = document.getElementById('download-csv').innerText;
    const fileName = zipName.substring(0, zipName.length - 3).concat('html');
    document.getElementById('html-download-link-label').innerText = fileName;

    let link = document.getElementById('download-html');
    const htmlContent = document.getElementById('csv-result-placeholder').innerHTML;
    link.setAttribute('href', 'data:text/html;charset=utf-8,' + encodeURI(htmlContent));
    link.setAttribute('download', fileName);
    link.style.display = 'inline';
}


function terminateRequest(reason) {
    let resultPlaceholder = document.getElementById('csv-result-placeholder');
    resultPlaceholder.style.color = 'red';
    resultPlaceholder.innerHTML = reason;
}