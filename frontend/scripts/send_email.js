const loginForm = document.getElementById('email-form');
const errorDiv = document.getElementById('error');

loginForm.addEventListener('submit', (event) => {
    
    event.preventDefault();

    const usernameElement = document.getElementById('username');
    const emailElement = document.getElementById('email');

    const userData = {
        username: usernameElement.value,
        email: emailElement.value
    };

    fetch('./../../backend/api/send_email.php', {
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
            emailElement.value = '';
        }
        else if (data.hasOwnProperty('success'))
        {
            document.getElementById("msg").style.visibility = "visible";
            //errorDiv.style.visibility = "hidden";
        }    
    });
});