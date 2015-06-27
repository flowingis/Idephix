<?php

namespace Idephix\File;

class FunctionBasedIdxFileTest extends \PHPUnit_Framework_TestCase
{
    private $idxFile;

    protected function setUp()
    {
        $this->idxFile = tmpfile();
    }

    public function testItShouldReadTargetsFromVariable()
    {
        $idxFileContent =<<<'EOD'
<?php

$targets = array('foo' => 'bar');
EOD;

        $idxFile = $this->writeTestIdxFile($idxFileContent);
        $file = new FunctionBasedIdxFile($idxFile);

        $this->assertEquals(array('foo' => 'bar'), $file->targets());
    }

    /**
     * @param $idxFileContent
     * @return resource
     */
    private function writeTestIdxFile($idxFileContent)
    {
        fwrite($this->idxFile, $idxFileContent);
        $tmpFileData = stream_get_meta_data($this->idxFile);

        return $tmpFileData['uri'];
    }
}