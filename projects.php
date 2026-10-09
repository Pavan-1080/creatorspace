
<?php
require_once __DIR__ . "/db.php";

function getProjects($conn, $search = "", $category = "")
{
    $sql = "SELECT id, title, category, description
            FROM projects
            WHERE 1=1";

    $types = "";
    $params = [];

    if ($search !== "") {
        $sql .= " AND (title LIKE ? OR category LIKE ? OR description LIKE ?)";
        $term = "%" . $search . "%";
        $types .= "sss";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    if ($category !== "") {
        $sql .= " AND category = ?";
        $types .= "s";
        $params[] = $category;
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    if ($types !== "") {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $projects = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $projects[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $projects;
}

function getCategories($conn)
{
    $categories = [];
    $result = mysqli_query(
        $conn,
        "SELECT DISTINCT category FROM projects ORDER BY category"
    );

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row["category"];
        }
    }

    return $categories;
}

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

// AJAX requests return project data as JSON.
if (isset($_GET["ajax"])) {
    header("Content-Type: application/json; charset=utf-8");

    echo json_encode([
        "projects" => getProjects($conn, $search, $category)
    ]);

    exit;
}

$projects = getProjects($conn, $search, $category);
$categories = getCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | CreatorSpace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="logo">Creator<span>Space.</span></a>

        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="login.php" class="btn">Admin Login</a>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">

        <div class="section-heading">
            <h2>Our Projects</h2>
            <p>Explore the projects and ideas in CreatorSpace.</p>
        </div>

        <div class="search-bar">
            <input
                type="search"
                id="projectSearch"
                class="form-control"
                placeholder="Search by project name, category or description..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, "UTF-8") ?>"
            >

            <select id="categoryFilter" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $item): ?>
                    <option
                        value="<?= htmlspecialchars($item, ENT_QUOTES, "UTF-8") ?>"
                        <?= $category === $item ? "selected" : "" ?>
                    >
                        <?= htmlspecialchars($item, ENT_QUOTES, "UTF-8") ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" id="clearFilters" class="btn btn-outline">
                Clear
            </button>
        </div>

        <p id="projectCount" style="color:var(--muted);margin:16px 0;">
            Showing <?= count($projects) ?> projects
        </p>

        <div class="project-grid" id="projectGrid">
            <?php if (count($projects) > 0): ?>
                <?php foreach ($projects as $index => $project): ?>
                    <article class="project-card">
                        <span class="project-number">
                            PROJECT <?= str_pad($index + 1, 2, "0", STR_PAD_LEFT) ?>
                        </span>

                        <h3><?= htmlspecialchars($project["title"]) ?></h3>

                        <p><?= htmlspecialchars($project["description"]) ?></p>

                        <span class="project-category">
                            <?= htmlspecialchars($project["category"]) ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="project-card">
                    <h3>No projects found</h3>
                    <p>Add a project through the admin panel or try different filters.</p>
                </div>
            <?php endif; ?>
        </div>

        <p id="searchError" class="error" hidden>
            Search could not be completed. Please try again.
        </p>

    </div>
</section>

<footer class="footer">
    <div class="container">
        © <?= date("Y") ?> CreatorSpace. Built with PHP and MySQL.
    </div>
</footer>

<script>
const searchInput = document.getElementById("projectSearch");
const categoryFilter = document.getElementById("categoryFilter");
const projectGrid = document.getElementById("projectGrid");
const projectCount = document.getElementById("projectCount");
const clearFilters = document.getElementById("clearFilters");
const searchError = document.getElementById("searchError");

let searchTimer;

function createProjectCard(project, index) {
    const article = document.createElement("article");
    article.className = "project-card";

    const number = document.createElement("span");
    number.className = "project-number";
    number.textContent = "PROJECT " + String(index + 1).padStart(2, "0");

    const title = document.createElement("h3");
    title.textContent = project.title;

    const description = document.createElement("p");
    description.textContent = project.description;

    const category = document.createElement("span");
    category.className = "project-category";
    category.textContent = project.category;

    article.append(number, title, description, category);

    return article;
}

async function searchProjects() {
    const search = searchInput.value.trim();
    const category = categoryFilter.value;

    const params = new URLSearchParams({
        ajax: "1",
        search: search,
        category: category
    });

    searchError.hidden = true;
    projectCount.textContent = "Searching...";

    try {
        const response = await fetch("projects.php?" + params.toString());

        if (!response.ok) {
            throw new Error("Search request failed");
        }

        const data = await response.json();

        projectGrid.replaceChildren();

        if (data.projects.length === 0) {
            const emptyCard = document.createElement("div");
            emptyCard.className = "project-card";

            const heading = document.createElement("h3");
            heading.textContent = "No projects found";

            const description = document.createElement("p");
            description.textContent = "Try another search term or category.";

            emptyCard.append(heading, description);
            projectGrid.appendChild(emptyCard);
        } else {
            data.projects.forEach((project, index) => {
                projectGrid.appendChild(createProjectCard(project, index));
            });
        }

        projectCount.textContent =
            "Showing " + data.projects.length +
            (data.projects.length === 1 ? " project" : " projects");

        const url = new URL(window.location.href);

        if (search) {
            url.searchParams.set("search", search);
        } else {
            url.searchParams.delete("search");
        }

        if (category) {
            url.searchParams.set("category", category);
        } else {
            url.searchParams.delete("category");
        }

        window.history.replaceState({}, "", url);

    } catch (error) {
        searchError.hidden = false;
        projectCount.textContent = "Unable to load projects.";
    }
}

searchInput.addEventListener("input", function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(searchProjects, 300);
});

categoryFilter.addEventListener("change", searchProjects);

clearFilters.addEventListener("click", function () {
    searchInput.value = "";
    categoryFilter.value = "";
    searchProjects();
});
</script>

</body>
</html>