
fetch('./../../backend/api/get_current_user.php')
.then(res => res.json())
.then(data => {
    if (data.hasOwnProperty('logged') && data['logged'] == true)
    {
        location.replace("./../pages/upload_page.html");
    }
    else
    {
        location.replace("./../pages/login.html");
    }    
});