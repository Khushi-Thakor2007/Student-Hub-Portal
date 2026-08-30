function loginUser()
{
    //get value from the user
    let email =document.getElementById("email").value;
    let password =document.getElementById("password").value;

    //check if email textbox is empty or not
    if(email=="")
    {
        alert("Please enter your email!");
        return;
    }
    //check if email textbox is empty or not
    if(password=="")
    {
        alert("Please enter your password!");
        return;
    }
    //successfully done message
    alert("Login Successful!");
}
function registeruser()
{
let name=document.getElementById("name").value;
let studentId=document.getElementById("studentId").value;
let email=document.getElementById("email").value;
let mobile=document.getElementById("mobile").value;
let password=document.getElementById("registerPassword").value;
let confirmPassword=document.getElementById("confirmPassword").value;
let terms=document.getElementById("terms").checked;

if(name=="")
{
alert("Please enter your full name!");
return;
}
if(studentId=="")
{
alert("Please enter your Student ID!");
return;
}
if(email=="")
{
alert("Please enter your email!");
return;
}
if(mobile=="")
{ 
alert("Please enter your mobile number!");
return;
}
if(password=="")
{
alert("Please enter your password!");
return;
}
if(confirmPassword=="")
{
alert("Please confirm your password!");
return;
}
if(password!=confirmPassword)
{
alert("Passwords do not match!");
return;
}
if(!terms)
{
alert("Please agree to the Terms & Conditions!");
return;
}
alert("Registration Successful!");
}
/*=================== light- dark mode =====================*/
function toggleTheme()
{
document.body.classList.toggle("dark-theme");

let button=document.querySelector(".theme-toggle");

if(document.body.classList.contains("dark-theme"))
{
button.innerHTML="☀️";
localStorage.setItem("theme","dark");
}
else
{
button.innerHTML="🌙";
localStorage.setItem("theme","light");
}
}
window.addEventListener("DOMContentLoaded",function()
{
let theme=localStorage.getItem("theme");
let button=document.querySelector(".theme-toggle");

if(theme=="dark")
{
document.body.classList.add("dark-theme");
button.innerHTML="☀️";
}
});
// ================= DARK / LIGHT THEME ================= for all the pages exsept login and register

const themeToggle = document.getElementById("themeToggle");

// Load saved theme
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-theme");
    themeToggle.textContent = "☀️";
} else {
    themeToggle.textContent = "🌙";
}

// Toggle theme
themeToggle.addEventListener("click", function () {

    document.body.classList.toggle("dark-theme");

    if (document.body.classList.contains("dark-theme")) {
        localStorage.setItem("theme", "dark");
        themeToggle.textContent = "☀️";
    } else {
        localStorage.setItem("theme", "light");
        themeToggle.textContent = "🌙";
    }

});