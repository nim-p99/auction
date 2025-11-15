<?php
$config = include('../auction_config/db_config.php');

$connection = mysqli_connect(
  $config['host'],
  $config['username'],
  $config['password'],
  $config['database']
);
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>


