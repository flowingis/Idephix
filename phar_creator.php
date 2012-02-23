<?php

/**
 * Phar creator, build the phar package
 *
 * @example php phar_creator.php
 */

$phar = new Phar('idefix.phar');
$phar->buildFromDirectory('src');

$stub = <<<ENDSTUB
<?php
Phar::mapPhar('idefix.phar');
require 'phar://idefix.phar/do.php';
__HALT_COMPILER();
ENDSTUB;

$phar->setStub($stub);

?>