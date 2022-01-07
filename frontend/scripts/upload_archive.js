var delimiter = ',', typeIndex = -1;
const MAX_FILE_SIZE_BYTES = 524288000; // 500 MB
const uploadForm = document.getElementById('upload-form');
const options = document.getElementById('options-input');

loadGreetingHeader();

options.innerHTML = `{
    "delimiter": ",",
    "included-fields": ["id","parent_id","name","type","parent-name","content-length","md5_sum","is_leaf", "css", "url"],
    "include-header": true,
    "skip-zip-filename": false,
    "uppercase": false,
    "is-leaf-numeric": false,
    "url-prefix": "http://localhost/download.php?file=",
    "url-suffix": "&force_download=true",
    "url-field-urlencoded": "id"
}`;

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
            delimiter = options.delimiter ? options.delimiter : ',';
            typeIndex = options['included-fields'] ? options['included-fields'].indexOf('type') : -1;

            const lines = text.split("\n");
            lines.pop(); // There is an empty line in the end
            lines.forEach(line => {
                const lineElement = document.createElement('div');
                lineElement.innerHTML = line;
                resultPlaceholder.appendChild(lineElement);
            })
            // createCsvDownloadLink(text, zip['name']);
            // updateDownloadHTMLLink();
        });
    })
}


function getUploadedFile() {
    return document.getElementById('file-input').files[0];
}

/*
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
*/


function terminateRequest(reason) {
    let resultPlaceholder = document.getElementById('csv-result-placeholder');
    resultPlaceholder.style.color = 'red';
    resultPlaceholder.innerHTML = reason;
}