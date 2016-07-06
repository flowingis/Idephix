<?php
namespace Idephix\Extension\Deploy\Strategy;

use Idephix\Context;
use Idephix\IdephixInterface;

class Factory implements FactoryInterface
{
    public function fromTarget(Context $target, IdephixInterface $idx)
    {
        $strategyClass = 'Idephix\\Extension\\Deploy\\Strategy\\'.$target->get('deploy.strategy', 'Copy');

        if (!class_exists($strategyClass)) {
            throw new \Exception(sprintf('No deploy strategy %s found. Check you configuration.', $strategyClass));
        }

        return new $strategyClass($idx, $target);
    }
}
