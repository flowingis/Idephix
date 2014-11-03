<?php

/**
 * Phar creator, build the phar package
 *
 * @example php phar_creator.php
 */

$version = exec("git rev-parse HEAD");
$file = __DIR__.'/../src/Idephix/Idephix.php';
$content = file_get_contents($file);
$contentVersion = str_replace('@package_version@', $version, $content);
file_put_contents($file, $contentVersion);

$phar = new Phar('idephix.phar');
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->buildFromDirectory('..', '/(\.(php|dist)|idx)$/');

$stub = <<<ENDSTUB
#!/usr/bin/env php
<?php
Phar::mapPhar('idephix.phar');
require 'phar://idephix.phar/bin/idx';
__HALT_COMPILER();
ENDSTUB;

$phar->setStub($stub);
file_put_contents($file, $content);
chmod(__DIR__.'/idephix.phar',0775);
