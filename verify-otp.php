```php
<?php
session_start();
require_once __DIR__ . "/db.php";

$message = "";
$success = false;

if (!isset($_SESSION["pending_user_id"])) {
    header("Location: register.php");
    exit;
}

$userId = (int) $_SESSION["pending_user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = trim($_POST["otp"] ?? "");

    if (!preg_match('/^\d{6}$/', $otp)) {
        $message = "Enter the six-digit verification code.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, otp_hash, expires_at
             FROM otp_verifications
             WHERE user_id = ?
               AND purpose = 'registration'
             ORDER BY id DESC
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $verification = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$verification) {
            $message = "No verification code was found. Please register again.";
        } elseif (strtotime($verification["expires_at"]) < time()) {
            $message = "Your verification code has expired. Please register again.";
        } elseif (!password_verify($otp, $verification["otp_hash"])) {
            $message = "Incorrect verification code. Please try again.";
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET email_verified_at = NOW()
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param($stmt, "i", $userId);
            $verified = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($verified) {
                $stmt = mysqli_prepare(
                    $conn,
                    "DELETE FROM otp_verifications
                     WHERE user_id = ? AND purpose = 'registration'"
                );

                mysqli_stmt_bind_param($stmt, "i", $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                unset($_SESSION["pending_user_id"]);

                $success = true;
                $message = "Email verified successfully! You can now log in.";
            } else {
                $message = "Verification failed. Please try again.";
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
    <title>Verify Email | CreatorSpace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="logo">Creator<span>Space.</span></a>
        <div class="nav-links">
            <a href="index.php">Home</a>
        </div>
    </div>
</nav>

<section class="form-section">
    <div class="container">
        <div class="form-card">
            <h1>Verify your email</h1>
            <p>Enter the six-digit code sent to your email address.</p>

            <?php if ($message !== ""): ?>
                <div class="notice <?= $success ? "" : "error" ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="otp">Verification code</label>
                        <input
                            class="form-control"
                            id="otp"
                            name="otp"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            minlength="6"
                            autocomplete="one-time-code"
                            placeholder="Enter 6-digit code"
                            required
                        >
                    </div>

                    <button class="btn" type="submit">Verify Email</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="btn">Go to Login</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">© <?= date("Y") ?> CreatorSpace</div>
</footer>

</body>
</html>
```