<?php
namespace Idephix\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $c = new Config(array('pippo' => 'pluto', 'paperino' => 'minni'));

        $this->assertEquals('pluto', $c->get('pippo'));
        $this->assertEquals('minni', $c->get('paperino', 'default'));
        $this->assertEquals(null, $c->get('tarapio'));
        $this->assertEquals('tapioca', $c->get('tarapio', 'tapioca'));
    }

    public function testGetterMultidimension()
    {
        $c = new Config(
            array(
                'pippo' => array(
                    'nonna papera' => 'qui',
                    'quo' => 'qua',
                    'paperino' => 'minni'
                ),
                'pippo.quo' => 'first level'
            ));

        $this->assertTrue(is_array($c->get('pippo')));
        $this->assertEquals('minni', $c->get('pippo.paperino'));
        $this->assertEquals('first level', $c->get('pippo.quo'));
    }
}
