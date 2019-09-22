<?php
namespace Idephix\Task;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamWrapper;
use Idephix\Task\Builtin\InitIdxFile;
use Symfony\Component\Console\Output\NullOutput;

class InitIdxFileTest extends \PHPUnit\Framework\TestCase
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

    public function testInitIdxFile()
    {
        $idx = $this->getMockBuilder('\Idephix\Idephix')
            ->disableOriginalConstructor()
            ->getMock();
        $idx->output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
            ->getMock();
        $idx->output->expects($this->exactly(4))
            ->method('writeln');

        $initIdxFile = new InitIdxFile('vfs://root', 'vfs://root/Deploy/idxfile.php', 'vfs://root/Deploy/idxrc.php');
        $initIdxFile->setIdephix($idx);
        $initIdxFile->initFile();

        $this->assertTrue(file_exists('vfs://root/idxfile.php'));
        $this->assertTrue(file_exists('vfs://root/idxrc.php'));

        $this->assertEquals('function foo(){ echo "bar"};', file_get_contents('vfs://root/idxfile.php'));
        $this->assertEquals('return \Idephix\Config::dry();', file_get_contents('vfs://root/idxrc.php'));
    }

    public function testInitWithExistingIdxFile()
    {
        vfsStreamWrapper::getRoot()->addChild(new vfsStreamFile('idxfile.php'));

        $idx = $this->getMockBuilder('\Idephix\Idephix')
            ->disableOriginalConstructor()
            ->getMock();
        $idx->output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
            ->getMock();
        $idx->output->expects($this->at(0))
            ->method('writeln')
            ->with('<error>An idxfile.php already exists, generation skipped.</error>')
            ;

        $initIdxFile = new InitIdxFile('vfs://root', 'vfs://root/Deploy/idxfile.php', 'vfs://root/Deploy/idxrc.php');
        $initIdxFile->setIdephix($idx);
        $initIdxFile->initFile();
    }

    public function testInitFromDeployRecipe()
    {
        $idx = $this->getMockBuilder('\Idephix\Idephix')
            ->disableOriginalConstructor()
            ->getMock();

        $idx->output = new NullOutput();
        $initIdxFile = InitIdxFile::fromDeployRecipe('vfs://root');
        $initIdxFile->setIdephix($idx);
        $initIdxFile->initFile();

        $this->assertTrue(file_exists('vfs://root/idxfile.php'));
        $this->assertTrue(file_exists('vfs://root/idxrc.php'));

        $this->assertEquals(file_get_contents(__DIR__ . '/../../../../src/Idephix/Cookbook/Deploy/idxfile.php'), file_get_contents('vfs://root/idxfile.php') );
        $this->assertEquals(file_get_contents(__DIR__ . '/../../../../src/Idephix/Cookbook/Deploy/idxrc.php'), file_get_contents('vfs://root/idxrc.php') );
    }

}
