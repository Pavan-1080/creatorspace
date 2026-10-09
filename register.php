```php
<?php
session_start();

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/send-otp.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $message = "Enter your name, a valid email, and a password of at least 8 characters.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $existingUser = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($existingUser);
        mysqli_stmt_close($stmt);

        if ($exists) {
            $message = "An account with this email already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (name, email, password, role)
                 VALUES (?, ?, ?, 'user')"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $name,
                $email,
                $passwordHash
            );

            if (mysqli_stmt_execute($stmt)) {
                $userId = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                $otp = (string) random_int(100000, 999999);
                $otpHash = password_hash($otp, PASSWORD_DEFAULT);
                $expiresAt = date("Y-m-d H:i:s", time() + 600);
                $purpose = "registration";

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO otp_verifications
                     (user_id, otp_hash, purpose, expires_at)
                     VALUES (?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "isss",
                    $userId,
                    $otpHash,
                    $purpose,
                    $expiresAt
                );

                $otpSaved = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($otpSaved && sendOTPEmail($email, $name, $otp)) {
                    $_SESSION["pending_user_id"] = $userId;

                    header("Location: verify-otp.php");
                    exit;
                }

                // Don't leave an account that cannot complete verification.
                $cleanup = mysqli_prepare(
                    $conn,
                    "DELETE FROM users WHERE id = ? AND email_verified_at IS NULL"
                );
                mysqli_stmt_bind_param($cleanup, "i", $userId);
                mysqli_stmt_execute($cleanup);
                mysqli_stmt_close($cleanup);

                $message = "We couldn't send the verification email. Check your Gmail settings and try registering again.";
            } else {
                mysqli_stmt_close($stmt);
                $message = "Unable to create the account. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CreatorSpace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="logo">Creator<span>Space.</span></a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="login.php">Login</a>
        </div>
    </div>
</nav>

<section class="form-section">
    <div class="container">
        <div class="form-card">
            <h1>Create an account</h1>
            <p>Register and verify your email to get started.</p>

            <?php if ($message !== ""): ?>
                <div class="notice error">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="name">Full name</label>
                    <input
                        class="form-control"
                        id="name"
                        name="name"
                        type="text"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        class="form-control"
                        id="email"
                        name="email"
                        type="email"
                        maxlength="150"
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
                        minlength="8"
                        required
                    >
                </div>

                <button class="btn" type="submit">Register & Verify Email</button>
            </form>

            <p style="margin-top:20px; margin-bottom:0;">
                Already registered? <a href="login.php">Log in</a>
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