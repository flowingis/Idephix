<?php
namespace Idephix\Task;

use Idephix\Test\IdephixTestCase;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamWrapper;

class InitIdxFileTest extends IdephixTestCase
{
    public function setUp()
    {
        @include_once 'vfsStream/vfsStream.php';

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
    }

    public function testInitIdxFile()
    {
        $idx = $this->getMock('\Idephix\IdephixInterface');
        $idx->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $idx->output->expects($this->exactly(4))
            ->method('writeln');

        $initIdxFile = new InitIdxFile('vfs://root');
        $initIdxFile->setIdephix($idx);
        $initIdxFile->initFile();

        $this->assertTrue(file_exists('vfs://root/idxfile.php'));
    }

    public function testInitWithExistingIdxFile()
    {
        vfsStreamWrapper::getRoot()->addChild(new vfsStreamFile('idxfile.php'));

        $idx = $this->getMock('\Idephix\IdephixInterface');
        $idx->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $idx->output->expects($this->at(0))
            ->method('writeln')
            ->with('<error>An idxfile.php already exists, generation skipped.</error>')
            ;

        $initIdxFile = new InitIdxFile('vfs://root');
        $initIdxFile->setIdephix($idx);
        $initIdxFile->initFile();
    }
}
