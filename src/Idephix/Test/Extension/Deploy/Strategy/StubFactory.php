<?php
namespace Idephix\Test\Extension\Deploy\Strategy;

use Idephix\Context;
use Idephix\Extension\Deploy\Strategy\FactoryInterface;
use Idephix\IdephixInterface;

class StubFactory implements FactoryInterface
{
    private $map;

    public function __construct()
    {
        $this->map = array('Copy' => StubCopy::class);
    }

    public function fromTarget(Context $target, IdephixInterface $idx)
    {
        $strategyName = $target->get('deploy.strategy', 'Copy');
        if (!array_key_exists($strategyName, $this->map)) {
            throw new \Exception(sprintf('No deploy strategy %s found. Check you configuration.', $strategyName));
        }

        $strategyClass = $this->map[$strategyName];

        return new $strategyClass($idx, $target);
    }
}
