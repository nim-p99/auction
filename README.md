### Set up:
in htdocs directory - create a directory 'auction_config'.
Inside auction_config, create a file 'db_config.php'. 

#### db_config.php:
<?php
return array(
  'host' => 'localhost',
  'username' => 'auctionadmin',
  'password' => 'adminpassword',
  'database' => 'auction_site'
);

#### directory structure:
htdocs
-> auction_config
  -> db_config.php
-> auction
  -> all auction files
  -> .....
