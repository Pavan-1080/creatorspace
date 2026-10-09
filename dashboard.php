```php
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/db.php";

$totalProjects = 0;
$totalArticles = 0;
$totalUsers = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM projects");
if ($result) {
    $totalProjects = (int) mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM articles");
if ($result) {
    $totalArticles = (int) mysqli_fetch_assoc($result)["total"];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($result) {
    $totalUsers = (int) mysqli_fetch_assoc($result)["total"];
}

$isAdmin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CreatorSpace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="logo">Creator<span>Space.</span></a>

        <div class="nav-links">
            <a href="index.php">Website</a>
            <a href="projects.php">Projects</a>
            <a href="logout.php" class="btn btn-outline">Log Out</a>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <span class="eyebrow">YOUR WORKSPACE</span>

        <h1 style="font-size:38px;">
            Welcome, <?= htmlspecialchars($_SESSION["name"] ?? "Creator") ?>!
        </h1>

        <p style="color:var(--muted); margin-top:8px;">
            Manage your creative content from one place.
        </p>

        <div class="dashboard-stats">
            <div class="stat-card">
                <p>Total Projects</p>
                <h2><?= $totalProjects ?></h2>
            </div>

            <div class="stat-card">
                <p>Total Articles</p>
                <h2><?= $totalArticles ?></h2>
            </div>

            <div class="stat-card">
                <p>Registered Users</p>
                <h2><?= $totalUsers ?></h2>
            </div>
        </div>

        <div class="project-grid">
            <div class="project-card">
                <span class="project-number">01 / EXPLORE</span>
                <h3>Browse Projects</h3>
                <p>Explore projects displayed on CreatorSpace.</p>
                <br>
                <a href="projects.php" class="btn">View Projects</a>
            </div>

            <div class="project-card">
                <span class="project-number">02 / ACCOUNT</span>
                <h3>Your Account</h3>
                <p>
                    Signed in as
                    <?= htmlspecialchars($_SESSION["email"] ?? "") ?>.
                </p>
                <br>
                <span class="project-category">
                    <?= $isAdmin ? "Administrator" : "Registered User" ?>
                </span>
            </div>

            <?php if ($isAdmin): ?>
                <div class="project-card">
                    <span class="project-number">03 / ADMIN</span>
                    <h3>Admin Controls</h3>
                    <p>Manage projects and review submitted content.</p>
                    <br>
                    <a href="admin.php" class="btn">Open Admin Panel</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        © <?= date("Y") ?> CreatorSpace
    </div>
</footer>

</body>
</html>
```