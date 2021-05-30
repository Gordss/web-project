window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.archive-csv-link').forEach(link => {
        const archiveID = link.id.split('-')[1];
        fetch(`archive.php?id=${archiveID}`).then(response => {
            if (response.status === 200) {
                response.text().then(text => {
                    var lines = text.split('\n');
                    lines.pop(); // There is an extra line in the end

                    const filename = lines[1].split(',')[0].split('.').slice(0, -1).join() + '.csv';
                    link.setAttribute('href', 'data:text/csv;charset=utf-8' + encodeURI(text));
                    link.setAttribute('download', filename);

                    link.innerHTML = filename;
                });
            } else {
                link.removeAttribute('href');
                link.style.color = 'grey';
                link.innerHTML = 'corrupted archive';
            }
        });
    });
});