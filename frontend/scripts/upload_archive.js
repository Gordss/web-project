let delimiter = ',', typeIndex = -1;
const MAX_FILE_SIZE_BYTES = 524288000; // 500 MB

window.onload = function ()
{
    loadGreetingHeader();
    initializeOptions();

    const uploadForm = document.getElementById('upload-form');
    uploadForm.addEventListener('submit', processConversion);

    const logoutA = document.getElementById('logout');
    logoutA.addEventListener('click', (event) => {
        fetch('./../../backend/api/logout.php')
        .then(res => res.json())
        .then(data => {
            // TODO: add error handling when error is thrown on logging out  
        });
    });
}

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

async function initializeOptions() {
    const params = new URLSearchParams(window.location.search);
    const loadedConversion = params.get('conversion');
    const options = document.getElementById('options-input');

    let oldOptionsJson = await fetchOptions(loadedConversion);
    let optionsStr = null;

    if (oldOptionsJson != null) {
        oldOptionsJson["input-data"] = "history";
        oldOptionsJson["input-config"] = "history";
        oldOptionsJson = addNewOption(oldOptionsJson, "history-meta", loadedConversion, 2);
        optionsStr = JSON.stringify(oldOptionsJson, null, '\t');
    }

    const defaultOptions = `{
\t"input-data": "upload",
\t"input-config": "textarea",
\t"delimiter": ",",
\t"included-fields": ["id","parent-id","name","type","parent-name","content-length","md5-sum","is-leaf", "css", "url"],
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

    options.innerHTML = optionsStr != null ? optionsStr : defaultOptions;

    options.addEventListener('keydown', (e) => optionsKeyDownHandler(e, options));
}

function addNewOption(obj, key, value, index) {
	// Create a temp object and index variable
	var temp = {};
	var i = 0;

	// Loop through the original object
	for (var prop in obj) {
		if (obj.hasOwnProperty(prop)) {

			// If the indexes match, add the new item
			if (i === index && key && value) {
				temp[key] = value;
			}

			// Add the current item in the loop to the temp obj
			temp[prop] = obj[prop];

			// Increase the count
			i++;

		}
	}

	// If no index, add to the end
	if (!index && key && value) {
		temp[key] = value;
	}

	return temp;
}

function optionsKeyDownHandler(e, options)
{
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
}

async function processConversion(event) {

    event.preventDefault();
    document.getElementById("download-csv").style.display = 'none';

    const resultPlaceholder = document.getElementById('csv-result-placeholder');
    const formData = new FormData(document.getElementById('upload-form'));
    let optionsJson;
    let requestedDelimiter;

    // validate if options are valid JSON
    try {
        optionsJson = JSON.parse(formData.get('options'));
        requestedDelimiter = optionsJson?.delimiter ? optionsJson.delimiter : ',';
    } catch (e) {
        terminateRequest('The options for the conversion must be a valid JSON string');
        return;
    }

    let zip = await getUploadedFile(optionsJson, isInputLoadedFromHistory(optionsJson));
    if (!zip) {
        terminateRequest('A file must be uploaded for conversion');
        return;
    }

    if (zip.size > MAX_FILE_SIZE_BYTES) {
        terminateRequest(`The uploaded archive's size must not exceed ${MAX_FILE_SIZE_BYTES} bytes`);
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

function isInputLoadedFromHistory(json)
{
    return json.hasOwnProperty('input-data') &&
        json['input-data'] === "history" &&
        json.hasOwnProperty('input-config') &&
        json['input-config'] === "history" &&
        json.hasOwnProperty('history-meta');
}

async function fetchOptions(converionId)
{
    return converionId ? fetch(`./../../backend/api/archive.php?id=${converionId}&options=true`)
        .then(res => res.json())
        .then(data => {
            if (data.hasOwnProperty('success')) {
                return JSON.parse(data['success']);
            }
            else {
                return null;
            }
        }) : null;
}

async function getUploadedFile(json, isLoadedFileFromHistory) {

    if (isLoadedFileFromHistory)
    {
        // { ServerName, OriginalName}
        const fileData = await fetchSourceServerName(json['history-meta']);
    
        if (fileData == null ||
            !fileData.hasOwnProperty('ServerName') ||
            !fileData.hasOwnProperty('OriginalName'))
        {
            return null;
        }

        const { ServerName, OriginalName } = fileData;

        return fetch(`./../../backend/files/${ServerName}`)
            .then(res => res.blob())
            .then(data => {
                return new File([data], OriginalName, {type: data.type});
            });
    }
    else
    {
        return document.getElementById('file-input').files[0];   
    }
}

async function fetchSourceServerName(conversionId)
{
    return fetch(`./../../backend/api/archive.php?id=${conversionId}&servername=true`)
        .then(res => res.json())
        .then(data => {
            return data.hasOwnProperty('success') ? data['success'] : null;
        });
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