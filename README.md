### Set up:
in htdocs directory - create a directory 'auction_config'.
Inside auction_config, create a file 'db_config.php'. 

#### directory structure:
```
htdocs
  -> auction_config
    -> db_config.php
  -> auction
    -> all website files
    -> ...
```
<img width="592" height="91" alt="image" src="https://github.com/user-attachments/assets/cfb5dbb2-c50a-4e24-a992-b74a911df99c" />
<img width="598" height="81" alt="image" src="https://github.com/user-attachments/assets/b56fe37d-a24a-42f0-9bd3-46b3f8b36db0" />



#### db_config.php:
```
<?php
return array(
  'host' => 'localhost',
  'username' => 'auctionadmin',
  'password' => 'adminpassword',
  'database' => 'auction_site'
);

directory structure:
htdocs
-> auction_config
  -> db_config.php
-> auction
  -> all auction files
  -> .....
```
#### ensure correct user details on myPhpAdmin:
<img width="831" height="445" alt="image" src="https://github.com/user-attachments/assets/f65d73ff-ed37-4097-8d26-15aee7f7953b" />
check host name and username are correct as above.
