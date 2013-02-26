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

4. SSH Proxy

Puoi scegliere il tuo proxy tra CLI o SSH lib di php oppure lasciare scegliere la migliore ad Idephix

5. Nelle anonymous function puoi specificare parametri (con valore) oppure delle opzioni (tipo flag) senza valore

ex. dichiarazione: add('pippo', function ($uno, $due = 'ipppo', bool $cinque = null)
ex. invocazione: idx pippo --cinque uno due
ex. invocazione: idx pippo uno
