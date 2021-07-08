window.addEventListener('DOMContentLoaded', () => {
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
});