<?php
namespace Idephix\Task;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamWrapper;
use Idephix\Task\Builtin\InitIdxFile;

class InitIdxFileTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        @include_once 'vfsStream/vfsStream.php';

        $structure = array(
            'Deploy' => array(
                'idxfile.php' => 'function foo(){ echo "bar"};',
                'idxrc.php' => 'return \Idephix\Config::dry();',
            )
        );

        vfsStream::setup('root', null, $structure);
    }

    /**
     * @test
     */
    public function it_should_create_files()
    {
        $context = $this->prophesize('\Idephix\Context');
        $context->writeln(\Prophecy\Argument::any())->shouldBeCalled();


        $initIdxFile = new InitIdxFile(
            'vfs://root',
            'vfs://root/Deploy/idxfile.php',
            'vfs://root/Deploy/idxrc.php'
        );

        $initIdxFile->setContext($context->reveal());
        $initIdxFile->initFile();

        $this->assertTrue(file_exists('vfs://root/idxfile.php'));
        $this->assertTrue(file_exists('vfs://root/idxrc.php'));

        $this->assertEquals('function foo(){ echo "bar"};', file_get_contents('vfs://root/idxfile.php'));
        $this->assertEquals('return \Idephix\Config::dry();', file_get_contents('vfs://root/idxrc.php'));
    }

    /**
     * @test
     */
    public function it_should_return_error_if_file_exists()
    {
        vfsStreamWrapper::getRoot()->addChild(new vfsStreamFile('idxfile.php'));

        $context = $this->prophesize('\Idephix\Context');
        $context->writeln('Creating basic idxrc.php file...')->shouldBeCalled();
        $context->writeln('idxrc.php file created.')->shouldBeCalled();
        $context->writeln('<error>An idxfile.php already exists, generation skipped.</error>')->shouldBeCalled();


        $initIdxFile = new InitIdxFile(
            'vfs://root',
            'vfs://root/Deploy/idxfile.php',
            'vfs://root/Deploy/idxrc.php'
        );

        $initIdxFile->setContext($context->reveal());
        $initIdxFile->initFile();
    }

    public function testInitFromDeployRecipe()
    {
        $context = $this->getMockBuilder('\Idephix\Context')
            ->disableOriginalConstructor()
            ->getMock();
        
        $initIdxFile = InitIdxFile::fromDeployRecipe('vfs://root');
        $initIdxFile->setContext($context);
        $initIdxFile->initFile();

        $this->assertTrue(file_exists('vfs://root/idxfile.php'));
        $this->assertTrue(file_exists('vfs://root/idxrc.php'));

        $this->assertEquals(file_get_contents(__DIR__ . '/../../../../src/Idephix/Cookbook/Deploy/idxfile.php'), file_get_contents('vfs://root/idxfile.php'));
        $this->assertEquals(file_get_contents(__DIR__ . '/../../../../src/Idephix/Cookbook/Deploy/idxrc.php'), file_get_contents('vfs://root/idxrc.php'));
    }
}
