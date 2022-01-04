const loginForm = document.getElementById('login-form');
const errorDiv = document.getElementById('error');

loginForm.addEventListener('submit', (event) => {
    
    event.preventDefault();

    const usernameElement = document.getElementById('username');
    const passwordElement = document.getElementById('password');

    const userData = {
        username: usernameElement.value,
        password: passwordElement.value
    };

    fetch('./../../backend/api/login.php', {
        method: 'POST',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.hasOwnProperty('error'))
        {
            errorDiv.innerText = data['error'];
            usernameElement.value = '';
            passwordElement.value = '';
        }
        else if (data.hasOwnProperty('success'))
        {
            location.replace("./../pages/upload_page.html");
        }    
    });
});