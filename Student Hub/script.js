function loginUser() 
{ 
    let email = document.getElementById("email").value; 
    let password = document.getElementById("password").value; 

    let emailpattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/; 

   let passwordpattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,128}$/;

    if(email === "") 
    { 
        alert("Please enter your email!"); 
        return; 
    } 

    if(!emailpattern.test(email)) 
    { 
        alert("Please enter a valid email address!"); 
        return; 
    } 

    if(password === "") 
    { 
        alert("Please enter your password!"); 
        return; 
    } 

    if(!passwordpattern.test(password)) 
    { 
        alert("Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character!"); 
        return; 
    } 

    fetch("php/login.php", 
    { 
        method: "POST", 

        headers: 
        { 
            "Content-Type": "application/x-www-form-urlencoded" 
        }, 

        body: new URLSearchParams(
        { 
            email: email, 
            password: password 
        }) 
    }) 

    .then(response => response.json()) 

    .then(data => 
    { 
        console.log("PHP response:", data); 

        if(data.success) 
        { 
            alert("Welcome, " + data.user.name + "!"); 

            window.location.href = data.redirect; // Redirect to the appropriate page based on the user's role
        } 
        else 
        { 
            alert(data.message); 
        } 
    }) 

    .catch(error => 
    { 
        console.error("Login error:", error); 
        alert("Login error. Please try again."); 
    }); 

}

function registeruser()
{
    let name = document.getElementById("name").value.trim();
    let studentId = document.getElementById("studentId").value.trim();
    let email = document.getElementById("email").value.trim();
    let mobile = document.getElementById("mobile").value.trim();
    let password = document.getElementById("registerPassword").value;
    let confirmPassword = document.getElementById("confirmPassword").value;
    let terms = document.getElementById("terms").checked;

    let namePattern = /^[A-Za-z ]{3,40}$/;
    let studentIdPattern = /^[A-Za-z0-9]{4,20}$/;
    let emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
    let mobilePattern = /^[6-9][0-9]{9}$/;
    let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,128}$/;

    if (name === "")
    {
        alert("Please enter your full name!");
        return;
    }

    if (!namePattern.test(name))
    {
        alert("Name should contain only letters and spaces (3–40 characters)!");
        return;
    }

    if (studentId === "")
    {
        alert("Please enter your Student ID!");
        return;
    }

    if (!studentIdPattern.test(studentId))
    {
        alert("Please enter a valid Student ID (4–20 letters or numbers)!");
        return;
    }

    if (email === "")
    {
        alert("Please enter your email!");
        return;
    }

    if (email.length > 254 || !emailPattern.test(email))
    {
        alert("Please enter a valid email address!");
        return;
    }

    if (mobile === "")
    {
        alert("Please enter your mobile number!");
        return;
    }

    if (!mobilePattern.test(mobile))
    {
        alert("Please enter a valid 10-digit mobile number!");
        return;
    }

    if (password === "")
    {
        alert("Please enter your password!");
        return;
    }

    if (!passwordPattern.test(password))
    {
        alert(
            "Password must contain 8–128 characters, " +
            "one uppercase letter, one lowercase letter, " +
            "one number and one special character (@$!%*?&)."
        );
        return;
    }

    if (confirmPassword === "")
    {
        alert("Please confirm your password!");
        return;
    }

    if (password !== confirmPassword)
    {
        alert("Passwords do not match!");
        return;
    }

    if (!terms)
    {
        alert("Please agree to the Terms & Conditions!");
        return;
    }
    const registerForm = document.getElementById("registerForm");
    const submitButton = registerForm
        ? registerForm.querySelector('button[type="submit"]')
        : null;

    if (submitButton && submitButton.disabled)
    {
        return;
    }

    if (submitButton)
    {
        submitButton.disabled = true;
        submitButton.textContent = "Registering...";
    }
    const messageElement = document.getElementById("registerMessage");

    function showMessage(message, success)
    {
        if (messageElement)
        {
            messageElement.textContent = message;
            messageElement.style.color = success ? "green" : "red";
        }
        else
        {
            alert(message);
        }
    }

    fetch("php/register.php",
    {
        method: "POST",
        headers:
        {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams(
        {
            name: name,
            studentId: studentId,
            email: email,
            mobile: mobile,
            password: password,
            confirmPassword: confirmPassword,
            terms: terms ? "1" : "0"
        })
    })
    .then(response =>
    {
        if (!response.ok)
        {
            throw new Error("Server returned HTTP " + response.status);
        }

        return response.json();
    })
    .then(data =>
    {
        console.log("PHP response:", data);

        if (data.success)
        {
            showMessage(data.message || "Registration successful!", true);

            setTimeout(() =>
            {
                window.location.href = "Login.html";
            }, 1000);
        }
        else
        {
            showMessage(data.message || "Registration failed. Please try again.", false);

            if (submitButton)
            {
                submitButton.disabled = false;
                submitButton.textContent = "Register";
            }
        }
    })
    .catch(error =>
    {
        console.error("Registration error:", error);

        showMessage(
            "Unable to complete registration. Please check your connection and try again.",
            false
        );

        if (submitButton)
        {
            submitButton.disabled = false;
            submitButton.textContent = "Register";
        }
    });
}
/*=================== light- dark mode =====================*/

const themeToggle = document.querySelector(".theme-toggle");

if (themeToggle) {
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-theme");
        themeToggle.textContent = "☀️";
    } else {
        themeToggle.textContent = "🌙";
    }

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
}

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
// ================= NOTIFICATION JSON =================

if (document.getElementById("notificationContainer")) {

    fetch("json/notification.json")
        .then(response => response.json())
        .then(notifications => {

            const container = document.getElementById("notificationContainer");

            notifications.forEach(notification => {

                container.innerHTML += `
                    <div class="notification-box">

                        <div class="notification-info">

                            <h4>${notification.title}</h4>

                            <p>
                                ${notification.message}
                            </p>

                        </div>

                        <span class="notification-time">
                            ${notification.time}
                        </span>

                    </div>
                `;

            });

        })
        .catch(error => {
            console.error("Error loading notifications:", error);
        });
}
// ================= PROFILE JSON =================

if (document.getElementById("profileName")) {

    fetch("json/profile.json")
        .then(response => response.json())
        .then(profile => {

            document.getElementById("profileName").textContent = profile.name;
            document.getElementById("profileStudentId").textContent = profile.studentId;
            document.getElementById("profileDepartment").textContent = profile.department;
            document.getElementById("profileSemester").textContent = profile.semester;
            document.getElementById("profileMobile").textContent = profile.mobile;
            document.getElementById("profileEmail").textContent = profile.email;
            document.getElementById("profileAddress").textContent = profile.address;
            document.getElementById("profileInstitute").textContent = profile.institute;

        })
        .catch(error => {
            console.error("Error loading profile:", error);
        });
    }
