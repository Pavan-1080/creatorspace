```php
<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/db.php";

$message = "";
$error = "";

// Add a new project
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_project"])) {
    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $image_url = trim($_POST["image_url"] ?? "");
    $project_url = trim($_POST["project_url"] ?? "");

    if ($title === "" || $category === "" || $description === "") {
        $error = "Please fill in the title, category, and description.";
    } else {
        $created_by = (int) $_SESSION["user_id"];

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO projects (title, category, description, image_url, project_url, created_by)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssssi",
            $title,
            $category,
            $description,
            $image_url,
            $project_url,
            $created_by
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Project added successfully!";
        } else {
            $error = "Could not add the project. Please try again.";
        }

        mysqli_stmt_close($stmt);
    }
}

// Delete a project
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_project"])) {
    $project_id = (int) ($_POST["project_id"] ?? 0);

    if ($project_id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM projects WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $project_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Project deleted successfully.";
        } else {
            $error = "Could not delete the project.";
        }

        mysqli_stmt_close($stmt);
    }
}

// Get projects
$projects = mysqli_query(
    $conn,
    "SELECT id, title, category, description, created_at
     FROM projects
     ORDER BY created_at DESC"
);

// Analytics
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

$categoryResult = mysqli_query(
    $conn,
    "SELECT category, COUNT(*) AS total
     FROM projects
     GROUP BY category
     ORDER BY total DESC"
);

$categories = [];
$categoryCounts = [];

if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $row["category"];
        $categoryCounts[] = (int) $row["total"];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | CreatorSpace</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="logo">Creator<span>Space.</span></a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="projects.php">Projects</a>
            <a href="logout.php" class="btn btn-outline">Log Out</a>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <span class="eyebrow">ADMINISTRATION</span>
        <h1 style="font-size:38px;">Admin Panel</h1>
        <p style="color:var(--muted);margin-top:8px;">
            Manage projects and monitor your CreatorSpace website.
        </p>

        <?php if ($message !== ""): ?>
            <p class="notice"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

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

        <div class="project-card" style="margin:30px 0;">
            <h2>Project Analytics</h2>
            <p style="color:var(--muted);margin:8px 0 20px;">
                Number of projects in each category.
            </p>
            <div style="max-width:700px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <div class="form-section" style="padding:0;margin:40px 0;">
            <div class="form-card" style="max-width:100%;">
                <h2>Add a Project</h2>
                <p style="color:var(--muted);margin:8px 0 24px;">
                    Add a project to the public CreatorSpace portfolio.
                </p>

                <form method="POST">
                    <label for="title">Project Title *</label>
                    <input class="form-control" id="title" name="title"
                           maxlength="200" required>

                    <label for="category">Category *</label>
                    <input class="form-control" id="category" name="category"
                           maxlength="100" placeholder="Web Design, Development, AI..." required>

                    <label for="description">Description *</label>
                    <textarea class="form-control" id="description" name="description"
                              rows="4" required></textarea>

                    <label for="image_url">Image URL (optional)</label>
                    <input class="form-control" type="url" id="image_url" name="image_url"
                           maxlength="500" placeholder="https://example.com/image.jpg">

                    <label for="project_url">Project URL (optional)</label>
                    <input class="form-control" type="url" id="project_url" name="project_url"
                           maxlength="500" placeholder="https://example.com">

                    <button class="btn" type="submit" name="add_project" value="1">
                        Add Project
                    </button>
                </form>
            </div>
        </div>

        <div style="margin-top:40px;">
            <h2>Manage Projects</h2>
            <p style="color:var(--muted);margin:8px 0 24px;">
                Projects currently stored in the database.
            </p>

            <div class="project-grid">
                <?php if ($projects && mysqli_num_rows($projects) > 0): ?>
                    <?php while ($project = mysqli_fetch_assoc($projects)): ?>
                        <div class="project-card">
                            <span class="project-number">
                                PROJECT #<?= (int) $project["id"] ?>
                            </span>
                            <span class="project-category">
                                <?= htmlspecialchars($project["category"]) ?>
                            </span>
                            <h3><?= htmlspecialchars($project["title"]) ?></h3>
                            <p><?= htmlspecialchars($project["description"]) ?></p>

                            <form method="POST"
                                  onsubmit="return confirm('Delete this project?');"
                                  style="margin-top:20px;">
                                <input type="hidden" name="project_id"
                                       value="<?= (int) $project["id"] ?>">
                                <button class="btn btn-outline" type="submit"
                                        name="delete_project" value="1">
                                    Delete Project
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No projects have been added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">© <?= date("Y") ?> CreatorSpace Admin</div>
</footer>

<script>
const categoryLabels = <?= json_encode($categories) ?>;
const categoryData = <?= json_encode($categoryCounts) ?>;

new Chart(document.getElementById("categoryChart"), {
    type: "bar",
    data: {
        labels: categoryLabels.length ? categoryLabels : ["No projects yet"],
        datasets: [{
            label: "Projects",
            data: categoryData.length ? categoryData : [0],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});
</script>

</body>
</html>
```