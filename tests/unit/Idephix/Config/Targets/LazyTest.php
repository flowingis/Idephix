<?php
namespace Idephix\Config\Targets;

class LazyTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigShouldBeAbleToLazyLoadValues()
    {
        $c = new Targets(
            array(
                'pippo' => function () {
                    return 'pluto';
                },
                'pluto' => array(
                    'nonna papera' => function () {
                        return 'qui';
                    },
                )
            )
        );

        $lazyConfig = new Lazy($c);

        $this->assertEquals('pluto', $lazyConfig->get('pippo'));
        $this->assertEquals('qui', $lazyConfig->get('pluto.nonna papera'));
    }

    public function testConfigShouldExecuteOnlyClosure()
    {
        $c = new Targets(array(
            'foo' => 'Copy'
        ));

        $lazyConfig = new Lazy($c);

        $this->assertEquals('Copy', $lazyConfig->get('foo'));
    }
}
