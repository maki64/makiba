# makiba
small imageboard script- on devel
A futaba-style imageboard script that doesn't require a database.
~~~~~~~~~~~~~~~~~~~

#credits
tripcode!q/7 - helping out with the software
Pineapple - helping out aswell
qa-tari - several fixes suggested on /qa/
sukiyaki - made his code better :^)
~~~~~~~~~~~~~~~~~~~

# Admin pannel Installation
~~~~~~~~~~~~
-> sudo apt install php-mbstring
make sure php-mbstring is installed

~~~~~~~~~~~~
# Installation
~~~~~~~~~~~~
make sure to install php-gd
https://www.php.net/manual/image.installation.php
imagecreatefromjpeg is in plain php undefined
debian:
-> sudo apt install php-gd

+If you need help, answers are here: https://stackoverflow.com/questions/13338339/imagecreatefromjpeg-and-similar-functions-are-not-working-in-php




To install, move all the files in the repo to a directory in the
 root of your webserver. For example, for a board called `/b/`, move
 them to /usr/share/nginx/html/b/. Create the directories `src`,
`thumb` and `res` and the files `bans`, `bad`, `posts` and `info`,
 make sure they are all writable to by the web server user.

Should work on any unix-like system with PHP 5.5 or above and GD Library.

Protip: you can use it as a blog too, by setting the following:
ADMINOPONLY=1,
SORTBUMP=0
REPLYBOX_TOP=0
SHOWPOSTLIST=1
PERPAGE=1 if you want 
