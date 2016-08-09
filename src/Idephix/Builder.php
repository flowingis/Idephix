<?php

namespace Idephix;

interface Builder
{
    /**
     * @return null|integer
     */
    public function run();

    /**
     * Add a Command to the application.
     * The "--go" parameters should be defined as "$go = false".
     *
     * @param string $name
     * @param \Closure $code
     * @return Builder
     */
    public function add($name, \Closure $code = null);

    /**
     * @param Extension $extension
     * @return
     */
    public function addExtension(Extension $extension);
}
