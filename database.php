<?php 
$connection = mysqli_connect("localhost", "auction", "ucl", "Auction");

if (mysqli_connect_errno())
  echo 'failed to connect to the MySQL server: '. mysqli_connect_error();
?>
