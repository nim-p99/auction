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


### EMAIL SETUP

STEP 1:
open file: xampp>sendmail>sendmail.ini
if you don't see it ==> do ctrl + f => sendmail.ini it is the sendmail file that has configuration settings as the file type
==> it's  the highlighted one in this image ![alt text](image.png) 
==> delete everything in that file and paste this in:

; configuration for fake sendmail

; if this file doesn't exist, sendmail.exe will look for the settings in
; the registry, under HKLM\Software\Sendmail

[sendmail]

; you must change mail.mydomain.com to your smtp server,
; or to IIS's "pickup" directory.  (generally C:\Inetpub\mailroot\Pickup)
; emails delivered via IIS's pickup directory cause sendmail to
; run quicker, but you won't get error messages back to the calling
; application.

smtp_server=smtp.gmail.com

; smtp port (normally 25)

smtp_port=587

; SMTPS (SSL) support
;   auto = use SSL for port 465, otherwise try to use TLS
;   ssl  = alway use SSL
;   tls  = always use TLS
;   none = never try to use SSL

smtp_ssl=auto

; the default domain for this server will be read from the registry
; this will be appended to email addresses when one isn't provided
; if you want to override the value in the registry, uncomment and modify

;default_domain=mydomain.com

; log smtp errors to error.log (defaults to same directory as sendmail.exe)
; uncomment to enable logging

error_logfile=error.log

; create debug log as debug.log (defaults to same directory as sendmail.exe)
; uncomment to enable debugging

;debug_logfile=debug.log

; if your smtp server requires authentication, modify the following two lines

auth_username=comp0178test@gmail.com
auth_password=rxvu ovzd rbdm jmsb

; if your smtp server uses pop3 before smtp authentication, modify the 
; following three lines.  do not enable unless it is required.

pop3_server=
pop3_username=
pop3_password=

; force the sender to always be the following email address
; this will only affect the "MAIL FROM" command, it won't modify 
; the "From: " header of the message content

force_sender=comp0178test@gmail.com

; force the sender to always be the following email address
; this will only affect the "RCTP TO" command, it won't modify 
; the "To: " header of the message content

force_recipient=

; sendmail will use your hostname and your default_domain in the ehlo/helo
; smtp greeting.  you can manually set the ehlo/helo name if required

hostname=


STEP2: 
open file: xampp>php>php.ini
==> same as before if you don't see it ==> do ctrl + f => php.ini => it is the php file that has configuration settings as the file type it might only come up as "php" not "php.ini"

==> ctrl + f ==> extension=openssl
==> make sure it is "extension=openssl" not ";extension=openssl"

==> ctrl + f ==> [mail function]
==> set the following settings to:
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"

this is what it should look like:

[mail function]
; For Win32 only.
; https://php.net/smtp
SMTP=smtp.gmail.com
; https://php.net/smtp-port
smtp_port=587

; For Win32 only.
; https://php.net/sendmail-from
;sendmail_from = me@example.com

; For Unix only.  You may supply arguments as well (default: "sendmail -t -i").
; https://php.net/sendmail-path
sendmail_path ="\"C:\xampp\sendmail\sendmail.exe\" -t"

if you're stuck with the php.ini part: https://www.youtube.com/watch?v=aB6iovBcAAQ

Once all that is done => restart xampp (stop apache & myspl, close xampp and reopen, restart both apache & mysql)if you already have it running. 
in your browser open the send_email.php file, it should then say that you have sent the email.
check spam if it says it's sent 