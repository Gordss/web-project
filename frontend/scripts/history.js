const conversionsPerPage = 10;
let currentPage = 1;

window.onload = function ()
{
    loadGreetingHeader();
    loadPage(1);
    
    const btnPrev = document.getElementById('btn-prev');
    btnPrev.onclick = prevPage;
    const btnNext = document.getElementById('btn-next');
    btnNext.onclick = nextPage;

    const logoutA = document.getElementById('logout');
    logoutA.addEventListener('click', () => {
        fetch('./../../backend/api/logout.php')
        .then(res => res.json())
        .then(data => {
            if (data.hasOwnProperty('error'))
            {
                location.replace("./../pages/server_error_page.html");
            }
        });
    });
}

async function loadPage(pageNumber)
{
    const noRecordsLabel = document.getElementById('no-records-label');
    const historyTable = document.getElementById('history-table');
    const tableNavigationDiv = document.getElementById('table-navigation');
    const totalConversionsCount = await getConversionsCount();
    // check if any conversions exist
    if (totalConversionsCount == 0)
    {
        noRecordsLabel.innerText = 'There are no conversions!';
        historyTable.style.display = 'none';
        tableNavigationDiv.style.display = 'none';
        return;
    }
    else
    {
        noRecordsLabel.style.display = 'none';
    }
    
    while (historyTable.childNodes.length > 4)
    {
        historyTable.removeChild(historyTable.lastElementChild);    
    }

    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const currentPage = document.getElementById('curr-page');

    // page number validation
    if (pageNumber < 1) pageNumber = 1;
    if (pageNumber > numPages(totalConversionsCount)) pageNumber = numPages(totalConversionsCount);

    currentPage.innerText = `Page: ${pageNumber}`;
    const offset = (pageNumber - 1) * conversionsPerPage;

    fetch(`./../../backend/api/history.php?perpage=${conversionsPerPage}&offset=${offset}`)
        .then(res => res.json())
        .then(data => {
            
            if (!data.hasOwnProperty('error'))
            {
                data.forEach(element => {
                    const archiveId = element['id'];
                    const md5 = element['md5-sum'];

                    const td_name = document.createElement('td');
                    const a_name = document.createElement('a');
                    a_name.className = "action-link";
                    a_name.innerText = element["name"];
                    const splitName = element["name"].split('.');
                    serveName = element['source-path'];
                    serveName = serveName.split('/').pop();
                    a_name.setAttribute('href', `./../../backend/files/${serveName}`);
                    a_name.setAttribute('download', element["name"]);
                    td_name.appendChild(a_name);

                    const td_md5Sum = document.createElement('td');
                    td_md5Sum.innerText = md5;

                    const td_createDate = document.createElement('td');
                    td_createDate.innerText = element['create-date'];

                    const td_dowloadCSV = document.createElement('td');
                    td_dowloadCSV.className = "actions-td";
                    const a_downloadCSV = document.createElement('a');
                    a_downloadCSV.className = "action-link";
                    const initialTextCsv = "Convert";
                    a_downloadCSV.innerText = initialTextCsv;
                    a_downloadCSV.style.cursor = 'pointer';

                    a_downloadCSV.addEventListener('click', () => downloadCSV(a_downloadCSV, archiveId, splitName, initialTextCsv));
                    td_dowloadCSV.appendChild(a_downloadCSV);
                    
                    const td_dowloadOptions = document.createElement('td');
                    td_dowloadOptions.className = "actions-td";
                    const a_downloadOptions = document.createElement('a');
                    a_downloadOptions.className = "action-link";
                    a_downloadOptions.innerText = "Download options";

                    a_downloadOptions.style.cursor = 'pointer';
                    a_downloadOptions.addEventListener('click', downloadOptions(a_downloadOptions, archiveId, splitName));
                    td_dowloadOptions.appendChild(a_downloadOptions);

                    const td_loadInUpload = document.createElement('td');
                    td_loadInUpload.className = "actions-td";
                    const a_loadInUpload = document.createElement('a');
                    a_loadInUpload.className = "action-link";
                    a_loadInUpload.innerText = "Load";
                    a_loadInUpload.title = "Loads the source file and options in the upload page.";
                    a_loadInUpload.style.cursor = 'pointer';
                    a_loadInUpload.addEventListener('click', () => redirectToUpload(archiveId));
                    td_loadInUpload.appendChild(a_loadInUpload);

                    const td_delete = document.createElement('td');
                    td_delete.className = "actions-td";
                    const a_delete = document.createElement('a');
                    a_delete.className = "action-delete-link";
                    a_delete.innerText = "Delete";
                    a_delete.style.cursor = 'pointer';
                    
                    a_delete.addEventListener('click', 
                        () => {
                        fetch(`./../../backend/api/archive.php?id=${archiveId}`, {
                            method: 'DELETE'
                        }).then(response => {
                            if (response.status === 204) {
                                historyTable.removeChild(tr);
                            }
                        });}
                    );
                    td_delete.appendChild(a_delete);

                    const tr = document.createElement('tr');
                    tr.appendChild(td_name);
                    tr.appendChild(td_md5Sum);
                    tr.appendChild(td_createDate);
                    tr.appendChild(td_dowloadCSV);
                    tr.appendChild(td_dowloadOptions);
                    tr.appendChild(td_loadInUpload);
                    tr.appendChild(td_delete);
                    historyTable.appendChild(tr);
                });   
            }
        });
    
    // make buttons disabled if user is on first or last page
    btnPrev.disabled = pageNumber == 1;
    btnNext.disabled = pageNumber == numPages(totalConversionsCount);
}

async function getConversionsCount()
{
    return fetch('./../../backend/api/history.php?count=true')
        .then(res => res.json())
        .then(data => {
            if (data.hasOwnProperty('success'))
            {
                return +data['success'];
            }
            else if (data.hasOwnProperty('error'))
            {
                location.replace("./../pages/server_error_page.html");
            }  
        });
}

function prevPage()
{
    if (currentPage > 1)
    {
        currentPage--;
        loadPage(currentPage);
    }
}

function nextPage()
{
    getConversionsCount()
        .then(totalConversionCount => {
            if (currentPage < numPages(totalConversionCount)) {
                currentPage++;
                loadPage(currentPage);
            }
        });
}

function numPages(totalConversionCount)
{
    return Math.ceil(totalConversionCount / conversionsPerPage);
}

function loadGreetingHeader() {
    const greetingHeader = document.getElementById('greeting');

    fetch('./../../backend/api/get_current_user.php')
    .then(res => res.json())
    .then(data => {
        if (data.hasOwnProperty('logged') && data['logged'] == true && data.hasOwnProperty('username'))
        {
            const username = data['username'];
            greetingHeader.innerText += ` ${username}`;
        }
        else if (data.hasOwnProperty('error'))
        {
            location.replace("./../pages/server_error_page.html");
        }   
    });
}

function downloadCSV(a_downloadCSV, archiveId, splitName, initialTextCsv) {
    // first click computes the CSV from the server
    // following clicks will download the computed CSV
    if (a_downloadCSV.innerText == initialTextCsv) {
        fetch(`./../../backend/api/archive.php?id=${archiveId}`)
        .then(res => {
            if (res.status === 200) {
                res.text().then(text => {
                    var lines = text.split('\n');
                    lines.pop(); // There is an extra line in the end
                    
                    const csvName = splitName.slice(0, -1).join('.').concat('.csv');
                    a_downloadCSV.setAttribute('download', csvName);
                    a_downloadCSV.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURI(text));
                    a_downloadCSV.innerText = "Download CSV";
                });
            } else {
                a_downloadCSV.removeAttribute('href');
                a_downloadCSV.style.color = 'grey';
                a_downloadCSV.innerText = 'corrupted archive';
            }
        });
    }
}

function downloadOptions(aTag, archiveId, splitName) {
    fetch(`./../../backend/api/archive.php?id=${archiveId}&options=true`)
    .then(res => res.json())
    .then(data => {
        if (data.hasOwnProperty('error'))
        {
            aTag.removeAttribute('href');
            aTag.style.color = 'grey';
            aTag.innerHTML = 'corrupted archive';
        }
        else if (data.hasOwnProperty('success'))
        {
            const filename = splitName.slice(0, -1).join('.').concat('.json');
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(data['success']);

            aTag.setAttribute("href", dataStr);
            aTag.setAttribute("download", filename);
            aTag.removeEventListener('click', downloadOptions);
        }
    })
}

function redirectToUpload(conversionId) {
    location.replace(`./../pages/upload_page.html?conversion=${conversionId}`);
}