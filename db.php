```php
<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "creatorspace";

$conn = mysqli_connect(
    $host,
    $username,
    $password,
    $database
);

if (!$conn) {
    die("Database connection failed.");
}

mysqli_set_charset($conn, "utf8mb4");

?>
```