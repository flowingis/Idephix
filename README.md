Idephix automator
=================

Appunti:
--------

Mettere gli utenti sudoer sui server di destinazione senza password ma su comandi specifici (es. "php-fpm restart")
# /etc/sudoers
kea ALL = PASSWD:ALL, NOPASSWD: /usr/bin/php-fpm 

Installazione
-------------

1. Librerie

``` sh
$ cd src && php composer.phar install
```

2. Lista comandi disponibili in idxfile.php

``` sh
$ php src/do.php list
```

3. Lancia il comando di test

``` sh
$ php src/do.php idephix:test-params uno due tre
```

