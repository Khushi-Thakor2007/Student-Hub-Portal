
<?php
session_start();

$timeout = 900; // 15 minutes

// Reject users who have not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: Login.html");
    exit;
}

// Expire inactive sessions
if (
    !isset($_SESSION["last_activity"]) ||
    (time() - $_SESSION["last_activity"]) > $timeout
) {
    $_SESSION = [];
    session_destroy();

    header("Location: Login.html?timeout=1");
    exit;
}

// Update last activity
$_SESSION["last_activity"] = time();

// Only students may access this dashboard
if (($_SESSION["role"] ?? "") !== "student") {
    if (($_SESSION["role"] ?? "") === "admin") {
        header("Location: AdminDashboard.php");
        exit;
    }

    http_response_code(403);
    exit("Access denied.");
}

$username = $_SESSION["name"];
?>
<!DOCTYPE html>
<html>
    <head>
     <title>Student Hub - Dashboard</title>
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Homemade+Apple&display=swap" rel="stylesheet">

     <link rel="stylesheet" href="style.css">
    </head>

     <body>
<button id="themeToggle" class="theme-toggle">🌙</button>
         <div class="container">


            <!-- Header -->
          <div class="header">

             <div class="logo-title">
            <img src="Pictures/Logo.png" alt="Student Hub Logo" width="60" height="60">

               <h1>Student Hub</h1>
          </div>

             <a href="php/logout.php" class="register-btn">LOGOUT</a>

        </div>
    <hr>
    <!-- Navigation Bar -->
    <div class="navbar">

        <a href="dashboard.php" class="active">DASHBOARD</a>

        <a href="profile.html">PROFILE</a>

        <a href="contact.html">CONTACT</a>

        <a href="events.html">EVENTS</a>

        <a href="Notification.html">ACADEMIC INFO</a>

        <a href="about.html">ABOUT</a>

        <a href="feedback.html">FEEDBACK</a>

    </div>
    <hr>

<div class="dashboard-content">

    <h2 class="welcome-user">
        Hi, <?php echo htmlspecialchars($username); ?> 👋
    </h2>

    <p class="dashboard-subtitle">
        Welcome back! Here's a quick overview of your academic activities.
    </p>

    <div class="dashboard-grid">

        <div class="dashboard-box">
            <h3>📅 Upcoming Events</h3>
            <p>View upcoming college events and important dates.</p>
        </div>

        <div class="dashboard-box">
            <h3>📢 Notices</h3>
            <p>Latest announcements from your college.</p>
        </div>

        <div class="dashboard-box">
            <h3>📈 Attendance</h3>
            <p>Your current attendance is <b>75%</b>.</p>
        </div>

        <div class="dashboard-box">
            <h3>📝 Assignments</h3>
            <p>3 assignments are pending submission.</p>
        </div>

        <div class="dashboard-box">
            <h3>🕒 Recent Activities</h3>
            <p>See your latest updates and activities.</p>
        </div>

        <div class="dashboard-box">
            <h3>🔗 Quick Links</h3>
            <p>Access study materials and useful resources.</p>
        </div>

    </div>

</div>

</div>
</div>
</div>
<script src="script.js"></script>
</body>
</html>