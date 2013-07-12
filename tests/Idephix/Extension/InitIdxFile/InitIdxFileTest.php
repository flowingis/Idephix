<?php
namespace Idephix\Extension\Deploy;

use Idephix\Tests\Test\IdephixTestCase;
use Idephix\Extension\InitIdxFile\InitIdxFile;
use Idephix\Config\Config;

class InitIdxFileTest extends IdephixTestCase
{
    public function testInitIdxFile()
    {
        $idx = $this->getMock('\Idephix\IdephixInterface');
        $idx->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $idx->output->expects($this->exactly(2))
            ->method('writeln');

        $initIdxFile = new InitIdxFile();
        $initIdxFile->setIdephix($idx);
        $initIdxFile->initFile();

        $this->assertTrue(file_exists('idxfile.php'));

        unlink('idxfile.php');
    }
}
