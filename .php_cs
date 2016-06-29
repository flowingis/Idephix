<?php

$finder = \Symfony\CS\Finder\DefaultFinder::create()
    ->in(array(__DIR__ . '/src', __DIR__ . '/tests'));

return \Symfony\CS\Config\Config::create()
    ->fixers(['long_array_syntax', 'single_quote'])
    ->level(\Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->finder($finder);