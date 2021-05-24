window.addEventListener('DOMContentLoaded', () => {
    document.querySelector('form').addEventListener('submit', uploadArchive);
});

function uploadArchive(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append('file', getUploadedFile());

    fetch('upload.php', {
        method: 'POST',
        body: formData
    }).then(response => response.text())
        .then(text => {
            console.log(text);
            document.querySelector('textarea').innerText = text;
        });
}

function getUploadedFile() {
    return document.getElementById('file-input').files[0];
}