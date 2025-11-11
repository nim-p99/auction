<?php 
$config = include('/opt/lampp/htdocs/auction_config/db_config.php');

$connection = mysqli_connect(
  $config['host'],
  $config['username'],
  $config['password'],
  $config['database']
);

if (!$connection) {
  die("Database connection failed: " . myqsli_connect_error());
}

