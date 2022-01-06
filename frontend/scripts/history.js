const historyTable = document.getElementById('history-table');

loadGreetingHeader();

window.addEventListener('DOMContentLoaded', () => {

    fetch('./../../backend/api/history.php')
        .then(res => res.json())
        .then(data => {
            
            if (!data.hasOwnProperty('error'))
            {
                data.forEach(element => {
                    const td_id = document.createElement('td');
                    const archiveId = element['id'];
                    td_id.innerText = archiveId;

                    const td_name = document.createElement('td');
                    td_name.innerText = element['name'];

                    const td_md5Sum = document.createElement('td');
                    td_md5Sum.innerText = element['md5-sum'];

                    const td_createDate = document.createElement('td');
                    td_createDate.innerText = element['create-date'];

                    const td_dowloadCSV = document.createElement('td');
                    td_dowloadCSV.className = "actions-td";
                    const a_downloadCSV = document.createElement('a');
                    a_downloadCSV.className = "archive-csv-link";
                    a_downloadCSV.innerText = "Download CSV";

                    /*
                    a_downloadCSV.addEventListener('click', (e) => {
                        fetch(`./../../backend/api/archive.php?id=${archiveId}`)
                            .then(res => {
                                if (res.status === 200) {
                                    res.text().then(text => {
                                        var lines = text.split('\n');
                                        lines.pop(); // There is an extra line in the end
                    
                                        link.setAttribute('download', element['name']);
                                        link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURI(text));
                                    });
                                } else {
                                    a_downloadCSV.removeAttribute('href');
                                    a_downloadCSV.style.color = 'grey';
                                    a_downloadCSV.innerText = 'corrupted archive';
                                }
                            });
                    });
                    */
                    td_dowloadCSV.appendChild(a_downloadCSV);
                    
                    const td_dowloadOptions = document.createElement('td');
                    td_dowloadOptions.className = "actions-td";
                    const a_downloadOptions = document.createElement('a');
                    a_downloadOptions.className = "archive-options-link";
                    a_downloadOptions.innerText = "Download options";
                    td_dowloadOptions.appendChild(a_downloadOptions);
                    
                    const td_delete = document.createElement('td');
                    td_delete.className = "actions-td";
                    const a_delete = document.createElement('a');
                    a_delete.className = "archive-delete-link";
                    a_delete.innerText = "Delete";
                    td_delete.appendChild(a_delete);

                    const tr = document.createElement('tr');
                    tr.appendChild(td_id);
                    tr.appendChild(td_name);
                    tr.appendChild(td_md5Sum);
                    tr.appendChild(td_createDate);
                    tr.appendChild(td_dowloadCSV);
                    tr.appendChild(td_dowloadOptions);
                    tr.appendChild(td_delete);
                    historyTable.appendChild(tr);
                });   
            }
        });

    /*
    document.querySelectorAll('.archive-csv-link').forEach(link => {
        const archiveID = link.id.split('-')[1];
        fetch(`archive.php?id=${archiveID}`).then(response => {
            if (response.status === 200) {
                response.text().then(text => {
                    var lines = text.split('\n');
                    lines.pop(); // There is an extra line in the end

                    const archiveName = link.parentElement.parentElement.children[1].innerHTML;
                    const filename = archiveName.substring(0, archiveName.length - 3).concat('csv');
                    link.setAttribute('download', filename);
                    link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURI(text));
                });
            } else {
                link.removeAttribute('href');
                link.style.color = 'grey';
                link.innerHTML = 'corrupted archive';
            }
        });
    });

    document.querySelectorAll('.archive-options-link').forEach(link => {
        const archiveID = link.id.split('-')[1];
        fetch(`archive.php?id=${archiveID}&options=true`).then(response => {
            if (response.status === 200) {
                response.text().then(text => {

                    const archiveName = link.parentElement.parentElement.children[1].innerHTML;
                    const filename = archiveName.substring(0, archiveName.length - 3).concat('json');
                    var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(text);

                    link.setAttribute("href", dataStr);
                    link.setAttribute("download", filename);
                });
            } else {
                link.removeAttribute('href');
                link.style.color = 'grey';
                link.innerHTML = 'corrupted archive';
            }
        });
    });

    document.querySelectorAll('.archive-delete-link').forEach(link => {
        const archiveID = link.id.split('-')[1];
        link.addEventListener('click', event => {
            event.preventDefault();

            fetch(`archive.php?id=${archiveID}`, {
                method: 'DELETE'
            }).then(response => {
                if (response.status === 204) {
                    let archiveTableEntry = link.parentElement.parentElement;
                    archiveTableEntry.parentElement.removeChild(archiveTableEntry);
                }
            });
        })
    });
    */
});

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
            const username = data['username'];
            greetingHeader.innerText += ` ${username}`;
        }
        else
        {
            // TODO: add error handling when error is thrown to get username from server
        }    
    });
}