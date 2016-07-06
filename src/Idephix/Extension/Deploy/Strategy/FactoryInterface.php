<?php
namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Context;
use Idephix\IdephixInterface;

interface FactoryInterface
{
    public function fromTarget(Context $target, IdephixInterface $idx);
}
