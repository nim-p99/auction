<?php
// 1. Load DB Credentials
$config = include(__DIR__ . '/../../auction_config/db_config.php');

// 2. Define Base URL
// This is the "Root" of your website. Used for links/redirects.
define('BASE_URL', '/auction');

// 3. Connect
$connection = mysqli_connect(
  $config['host'],
  $config['username'],
  $config['password'],
  $config['database']
);

// 4. Check Connection
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

// 5. Set Charset
// as message may contain emoji.
mysqli_set_charset($connection, "utf8mb4");
?>
