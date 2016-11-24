<?php
namespace Idephix\Task\Builtin;

use Idephix\Context;
use Idephix\Task\Parameter;
use Idephix\Extension\ContextAwareInterface;
use Idephix\Task\Task;

class InitIdxFile implements ContextAwareInterface, Task
{
    private $ctx;
    private $baseDir;
    private $idxFileTemplate;
    private $idxRcTemplate;

    public function __construct($writeTo = '.', $idxFileTemplate, $idxRcTemplate)
    {
        $this->baseDir = $writeTo;
        $this->idxFileTemplate = $idxFileTemplate;
        $this->idxRcTemplate = $idxRcTemplate;
    }

    /**
     * @param string $writeTo
     * @return static
     */
    public static function fromDeployRecipe($writeTo = '.')
    {
        return new static(
            $writeTo,
            __DIR__ . '/../../Cookbook/Deploy/idxfile.php',
            __DIR__ . '/../../Cookbook/Deploy/idxrc.php'
        );
    }

    public function setContext(Context $ctx)
    {
        $this->ctx = $ctx;
    }

    public function name()
    {
        return 'initFile';
    }

    public function description()
    {
        return 'Init idx configurations and tasks file';
    }

    public function parameters()
    {
        return Parameter\Collection::dry();
    }

    public function userDefinedParameters()
    {
        return new Parameter\UserDefinedCollection($this->parameters());
    }

    public function code()
    {
        return array($this, 'initFile');
    }

    public function initFile()
    {
        $this->initIdxFile();
        $this->initIdxRc();
    }

    private function initIdxRc()
    {
        $data = file_get_contents($this->idxRcTemplate);
        $this->writeFile('idxrc.php', $data);
    }

    private function initIdxFile()
    {
        $data = file_get_contents($this->idxFileTemplate);
        $this->writeFile('idxfile.php', $data);
    }

    private function writeFile($filename, $data)
    {
        $idxFile = $this->baseDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($idxFile)) {
            $this->ctx->writeln("<error>An {$filename} already exists, generation skipped.</error>");

            return;
        }

        $this->ctx->writeln("Creating basic {$filename} file...");

        if (!is_writable($this->baseDir) || false === file_put_contents($idxFile, $data)) {
            throw new \Exception("Cannot write {$filename}, check your permission configuration.");
        }

        $this->ctx->writeln("{$filename} file created.");
    }
}
