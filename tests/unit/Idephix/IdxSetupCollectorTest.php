<?php
namespace Idephix;

class IdxSetupCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_collect_libraries_from_configuration()
    {
        $config = Config::fromArray(array(
            'libraries' => array('deploy' => new TestLibrary())
        ));

        $setup = new IdxSetupCollector($config);
        $libraries = $setup->getLibraries();
        $this->assertCount(1, $libraries);
        $this->assertEquals(new TestLibrary(), $libraries['deploy']);
    }
}

class TestLibrary
{
}
