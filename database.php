<?php
  $connection = mysqli_connect("localhost", "auctionadmin", "", "auction");

  if (mysqli_connect_errno())
    echo 'Failed to connect to the MySQL server: '. mysqli_connect_error();

?>