```php
<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === "") {
        $message = "Enter a valid email address and password.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password, role, email_verified_at
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user["password"])) {
            if (empty($user["email_verified_at"])) {
                $message = "Your email has not been verified yet. Email OTP verification will be connected next.";
            } else {
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                header("Location: dashboard.php");
                exit;
            }
        } else {
            $message = "Incorrect email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CreatorSpace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="logo">Creator<span>Space.</span></a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="register.php">Register</a>
        </div>
    </div>
</nav>

<section class="form-section">
    <div class="container">
        <div class="form-card">
            <h1>Welcome back</h1>
            <p>Log in to your CreatorSpace account.</p>

            <?php if ($message !== ""): ?>
                <div class="notice error">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        class="form-control"
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        class="form-control"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button class="btn" type="submit">Log In</button>
            </form>

            <p style="margin-top:20px; margin-bottom:0;">
                Don't have an account? <a href="register.php">Register</a>
            </p>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">© <?= date("Y") ?> CreatorSpace</div>
</footer>

</body>
</html>
```