<?php
namespace Idephix\Config;

class LazyConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigShouldBeAbleToLazyLoadValues()
    {
        $c = new Config(
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

        $lazyConfig = new LazyConfig($c);

        $this->assertEquals('pluto', $lazyConfig->get('pippo'));
        $this->assertEquals('qui', $lazyConfig->get('pluto.nonna papera'));
    }

    public function testConfigShouldExecuteOnlyClosure()
    {
        $c = new Config(array(
            'foo' => 'Copy'
        ));

        $lazyConfig = new LazyConfig($c);

        $this->assertEquals('Copy', $lazyConfig->get('foo'));
    }
}