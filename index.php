```php
<?php
require_once "db.php";

$projectQuery = mysqli_query(
    $conn,
    "SELECT id, title, category, description
     FROM projects
     ORDER BY created_at DESC
     LIMIT 3"
);

$projects = [];

while ($row = mysqli_fetch_assoc($projectQuery)) {
    $projects[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CreatorSpace | Creative Portfolio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="logo">Creator<span>Space.</span></a>

        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="#about">About</a>
            <a href="login.php" class="btn">Admin Login</a>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow">CREATIVE PORTFOLIO & IDEAS</span>

            <h1>Ideas into <span>digital</span> experiences.</h1>

            <p>
                Welcome to CreatorSpace — a portfolio of creative projects,
                experiments and ideas built with technology and imagination.
            </p>

            <div class="hero-actions">
                <a href="projects.php" class="btn">Explore Projects →</a>
                <a href="#about" class="btn btn-outline">About CreatorSpace</a>
            </div>
        </div>

        <div class="hero-card">
            <h3>What you'll find here</h3>

            <div class="feature">
                <div class="feature-icon">01</div>
                <div>
                    <strong>Creative Projects</strong>
                    <p>Explore selected web and digital projects.</p>
                </div>
            </div>

            <div class="feature">
                <div class="feature-icon">02</div>
                <div>
                    <strong>Ideas & Insights</strong>
                    <p>Discover experiments, concepts and useful resources.</p>
                </div>
            </div>

            <div class="feature">
                <div class="feature-icon">03</div>
                <div>
                    <strong>Technology</strong>
                    <p>See how design and development come together.</p>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="section" id="projects">
    <div class="container">
        <div class="section-heading">
            <h2>Featured Projects</h2>
            <p>A selection of work from the CreatorSpace collection.</p>
        </div>

        <div class="project-grid">
            <?php if (count($projects) > 0): ?>
                <?php foreach ($projects as $index => $project): ?>
                    <article class="project-card">
                        <span class="project-number">
                            PROJECT <?= str_pad($index + 1, 2, "0", STR_PAD_LEFT) ?>
                        </span>

                        <h3><?= htmlspecialchars($project["title"]) ?></h3>

                        <p>
                            <?= htmlspecialchars($project["description"]) ?>
                        </p>

                        <span class="project-category">
                            <?= htmlspecialchars($project["category"]) ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <article class="project-card">
                    <span class="project-number">PROJECT 01</span>
                    <h3>Creative Portfolio</h3>
                    <p>A showcase for digital ideas, design and development.</p>
                    <span class="project-category">Web Design</span>
                </article>

                <article class="project-card">
                    <span class="project-number">PROJECT 02</span>
                    <h3>Digital Experiments</h3>
                    <p>A space to explore creative concepts and new technologies.</p>
                    <span class="project-category">Technology</span>
                </article>

                <article class="project-card">
                    <span class="project-number">PROJECT 03</span>
                    <h3>Creative Resources</h3>
                    <p>Useful tools and ideas for building better digital experiences.</p>
                    <span class="project-category">Resources</span>
                </article>
            <?php endif; ?>
        </div>

        <div style="text-align:center; margin-top:30px;">
            <a href="projects.php" class="btn btn-outline">View All Projects →</a>
        </div>
    </div>
</section>

<section class="section" id="about">
    <div class="container">
        <div class="cta">
            <h2>Creativity meets technology.</h2>
            <p>
                CreatorSpace brings projects and ideas together in one
                simple, dynamic portfolio.
            </p>
            <a href="projects.php" class="btn">Discover the Projects</a>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        © <?= date("Y") ?> CreatorSpace. Built with PHP and MySQL.
    </div>
</footer>

</body>
</html>
```