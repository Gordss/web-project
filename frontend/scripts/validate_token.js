const errorDiv = document.getElementById('error');
const param = window.location.href.split('?');

window.addEventListener('load', (event) => {

    //validation.html?token=alnaouoenoa taking the token and giving it to php 
    fetch(`./../../backend/api/validate_token.php?${param[1]}`)
    .then(res => res.json())
    .then(data => {
        if (data.hasOwnProperty('error'))
        {
            errorDiv.innerText = data['error'];
        }
        else if (data.hasOwnProperty('success'))
        {
            location.replace("./../pages/change_password.html");
        }    
    });

});
   