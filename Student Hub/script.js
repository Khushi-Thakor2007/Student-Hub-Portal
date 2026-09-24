function loginUser()
{
    //get value from the user
    let email =document.getElementById("email").value;
    let password =document.getElementById("password").value;
    let emailpattern=/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    let passwordpattern=/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    //check if email textbox is empty or not
    if(email==="")
    {
        alert("Please enter your email!");
        return;
    }
    if(!emailpattern.test(email))
    {
        alert("Please enter a valid email address!");
        return;
    }
    // check if email textbox is empty or not
    if(password==="")
    {
        alert("Please enter your password!");
        return;
    }
    if (!passwordpattern.test(password))
    {
        alert("Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character!");
        return;
    }
    if(password.length<8)
    {
        alert("Password must contain at least 8 characters!");
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
let namePattern = /^[A-Za-z ]{3,40}$/;
let studentIdPattern = /^[A-Za-z0-9]{4,20}$/;
let emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
let mobilePattern = /^[6-9][0-9]{9}$/;
let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/; 
     if(name==="")
    {
        alert("Please enter your full name!");
        return;
    }
    if (!namePattern.test(name)) 
    {
        alert("Name should contain only letters and spaces!");
        return;
    }
    if(studentId==="")
    {
        alert("Please enter your Student ID!");
    return;
    }
    if (!studentIdPattern.test(studentId)) 
    {
        alert("Please enter a valid Student ID!");
        return;
    }
    if(email==="")
    {
        alert("Please enter your email!");
        return;
    }
    if (!emailPattern.test(email)) 
    {
        alert("Please enter a valid email address!");
        return;
    }
    if(mobile==="")
    { 
        alert("Please enter your mobile number!");
        return;
    }
    if (!mobilePattern.test(mobile)) 
    {
        alert("Please enter a valid 10-digit mobile number!");
        return false;
    }
    if(password==="")
    {
        alert("Please enter your password!");
        return;
    }
    if (!passwordPattern.test(password)) 
    {
        alert("Password must contain at least 8 characters, " + "one uppercase letter, one lowercase letter, " + "one number and one special character!");
        return false;
    }
    if(confirmPassword==="")
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
// ================= EVENTS JSON =================

if (document.getElementById("eventsContainer")) {

    fetch("json/events.json")
        .then(response => response.json())
        .then(events => {

            const container = document.getElementById("eventsContainer");

            events.forEach(event => {

                container.innerHTML += `
                    <div class="event-box">

                        <img src="${event.image}" alt="${event.title}">

                        <div class="event-details">

                            <h3>${event.title}</h3>

                            <p><b>Date:</b> ${event.date}</p>

                            <p><b>Time:</b> ${event.time}</p>

                            <p><b>Venue:</b> ${event.venue}</p>

                            <p>${event.description}</p>

                        </div>

                        <button>Register</button>

                    </div>
                `;

            });

        })
        .catch(error => {
            console.error("Error loading events:", error);
        });
}
// ================= MATERIAL JSON =================

if (document.getElementById("materialContainer")) {

    fetch("json/material.json")
        .then(response => response.json())
        .then(materials => {

            console.log(materials);

            const container = document.getElementById("materialContainer");

            materials.forEach(material => {

                container.innerHTML += `
                    <div class="material-box">

                        <img src="${material.icon}" alt="${material.fileType}">

                        <div class="material-details">

                            <h3>${material.fileName}</h3>

                            <p>${material.title}</p>

                        </div>

                        <a href="${material.fileName}" download>
                            <button>Download</button>
                        </a>

                    </div>
                `;

            });

        })
        .catch(error => {
            console.error("Error loading materials:", error);
        });
}