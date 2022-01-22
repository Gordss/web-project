var delimiter = ',', typeIndex = -1;
const MAX_FILE_SIZE_BYTES = 524288000; // 500 MB
const uploadForm = document.getElementById('upload-form');
options = document.getElementById('options-input');

loadGreetingHeader();

options.innerHTML = `{
\t"delimiter": ",",
\t"included-fields": ["id","parent_id","name","type","parent-name","content-length","md5_sum","is_leaf", "css", "url"],
\t"include-header": true,
\t"skip-zip-filename": false,
\t"uppercase": false,
\t"is-leaf-numeric": false,
\t"url-prefix": "http://localhost/download.php?file=",
\t"url-suffix": "&force_download=true",
\t"url-field-urlencoded": "id",
\t"file-type-color" : {
\t\t"text": "rgb(0, 0, 0)",
\t\t"image": "rgb(0, 0, 0)",
\t\t"directory": "rgb(0, 0, 0)"
\t}
}`;

options.addEventListener('keydown', (e) => {
    const beforeKey = options.selectionStart;
    const afterKey = options.selectionEnd;

    if(e.key == 'Tab') {
        e.preventDefault();
        options.value = options.value.substring(0, beforeKey) + "\t" + options.value.substring(afterKey);
        options.selectionEnd = beforeKey + 1;
    }
    else if(e.key == 'Enter') {
        e.preventDefault();
        options.value = options.value.substring(0, beforeKey) + "\n\t" + options.value.substring(afterKey);
        options.selectionEnd = beforeKey + 2;
    }
    else if(e.key == '\"') {
        if(options.value.substring(beforeKey, beforeKey + 1) == "\"") {
            options.selectionEnd = beforeKey + 1;
        }
        else {
            options.value = options.value.substring(0, beforeKey) + "\"" + options.value.substring(afterKey);
            options.selectionEnd = beforeKey;
        }
    }
})

uploadForm.addEventListener('submit', uploadArchive);

const logoutA = document.getElementById('logout');
logoutA.addEventListener('click', (event) => {
    fetch('./../../backend/api/logout.php')
    .then(res => res.json())
    .then(data => {
        // TODO: add error handling when error is thrown on logging out  
    });
});

function loadGreetingHeader() {
    const greetingHeader = document.getElementById('greeting');

    fetch('./../../backend/api/get_current_user.php')
    .then(res => res.json())
    .then(data => {
        if (data.hasOwnProperty('logged') && data['logged'] == true && data.hasOwnProperty('username'))
        {
            greetingHeader.innerText += ` ${data['username']}`;
        }
        else
        {
            // TODO: add error handling when error is thrown to get username from server
        }    
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

    const formData = new FormData(document.getElementById('upload-form'));

    var requestedDelimiter;
    try {
        const options = JSON.parse(formData.get('options'));
        requestedDelimiter = options?.delimiter ? options.delimiter : ',';
    } catch (e) {
        terminateRequest('The options for the conversion must be a valid JSON string');
        return;
    }

    // handles files with name `something.other.thing.zip`
    const nameWithoutExtension = zip.name.split('.').slice(0, -1).join('');
    
    if (nameWithoutExtension.includes(requestedDelimiter) || nameWithoutExtension.includes('.')
        || nameWithoutExtension.includes(',')) {
        const msgAddition = requestedDelimiter !== ',' ? ` and '${requestedDelimiter}'` : '';
        terminateRequest(`The uploaded file's name must not contain the following symbols: '.', ',' ${msgAddition}`);
        return;
    }

    formData.append('file', zip);

    fetch('./../../backend/api/archive.php', {
        method: 'POST',
        body: formData
    }).then(response => {
        response.text().then(text => {
            if (response.status !== 200) {
                terminateRequest(text);
                return;
            }

            resultPlaceholder.innerHTML = '';
            resultPlaceholder.style.color = 'white';

            const options = JSON.parse(response.headers.get('X-Applied-Options'));
            const fileColor = options['file-type-color'];

            delimiter = options.delimiter ? options.delimiter : ',';
            typeIndex = options['included-fields'] ? options['included-fields'].indexOf('type') : -1;

            const lines = text.split("\n");
            lines.pop(); // There is an empty line in the end
            lines.forEach(line => {
                const lineElement = document.createElement('div');
                lineElement.innerHTML = line;
                lineElement.style.color = colorFile(line, delimiter, typeIndex, fileColor);
                resultPlaceholder.appendChild(lineElement);
            })

            createCsvDownloadLink(text, zip['name']);
            updateDownloadHTMLLink();
        });
    })
}

function colorFile(line, delimiter, typeIndex, fileColor) {
    const defaultColor = "black";
    if (typeIndex < 0) {
        return defaultColor;
    }
    const fileType = line.split(delimiter)[typeIndex].toLowerCase().trimEnd();
    if (['txt', 'md', 'doc', 'docx'].includes(fileType)) {
        return fileColor['text'];
    }
    if (['jpg', 'png', 'gif'].includes(fileType)) {
        return fileColor['image'];
    }
    if ('directory' === fileType) {
        return fileColor['directory'];
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