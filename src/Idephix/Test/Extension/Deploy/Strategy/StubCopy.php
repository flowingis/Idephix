<?php
namespace Idephix\Test\Extension\Deploy\Strategy;

use Idephix\Context;
use Idephix\Extension\Deploy\Strategy\DeployStrategyInterface;
use Idephix\IdephixInterface;

class StubCopy implements DeployStrategyInterface
{
    /** @var  IdephixInterface */
    private $idx;
    /**
     * @param IdephixInterface $idx
     * @param Context $currentContext
     */
    public function __construct(IdephixInterface $idx, Context $currentContext)
    {
        $this->idx = $idx;
    }

    /**
     * The main deploy method
     *
     * Should implement copy of the code to the destination folder
     *
     * @return string|null
     */
    public function deploy()
    {
        $this->idx->local('rsync command');
    }
}
