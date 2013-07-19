<?php
namespace Idephix\Extension\Deploy;

use Idephix\Tests\Test\IdephixTestCase;
use Idephix\Extension\InitIdxFile\InitIdxFile;
use org\bovigo\vfs\vfsStreamDirectory;
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
        $idx->output->expects($this->exactly(2))
            ->method('writeln');

        $initIdxFile = new InitIdxFile('vfs://root');
        $initIdxFile->setIdephix($idx);
        $initIdxFile->initFile();

        $this->assertTrue(file_exists('vfs://root/idxfile.php'));
    }
}
