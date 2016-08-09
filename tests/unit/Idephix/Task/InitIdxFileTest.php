<?php
namespace Idephix\Task;

use Idephix\Test\IdephixTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamWrapper;

class InitIdxFileTest extends IdephixTestCase
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
        $idx = $this->getMock('\Idephix\TaskExecutor');
        $idx->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
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

        $idx = $this->getMock('\Idephix\TaskExecutor');
        $idx->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $idx->output->expects($this->at(0))
            ->method('writeln')
            ->with('<error>An idxfile.php already exists, generation skipped.</error>')
            ;

        $initIdxFile = new InitIdxFile('vfs://root', 'vfs://root/Deploy/idxfile.php', 'vfs://root/Deploy/idxrc.php');
        $initIdxFile->setIdephix($idx);
        $initIdxFile->initFile();
    }
}
