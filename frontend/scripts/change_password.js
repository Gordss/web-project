const loginForm = document.getElementById('email-form');
const errorDiv = document.getElementById('error');

loginForm.addEventListener('submit', (event) => {
    
    event.preventDefault();

    const emailElement = document.getElementById('email');
    const passwordElement = document.getElementById('password');

    const userData = {
        email: emailElement.value,
        password: passwordElement.value
    };

    fetch('./../../backend/api/change_password.php', {
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
            emailElement.value = '';
            passwordElement.value = '';
        }
        else if (data.hasOwnProperty('success'))
        {
            document.getElementById("msg").style.visibility = "visible";
        }    
    });
});