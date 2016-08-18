<?php
namespace Idephix\Task\Builtin;

use Idephix\Extension\IdephixAwareInterface;
use Idephix\Idephix;
use Idephix\Task\Task;
use Idephix\Task\Parameter\Collection;
use Idephix\Task\Parameter\UserDefinedCollection;

class InitIdxFile implements Task, IdephixAwareInterface
{
    private $idx;
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
            __DIR__ . '/../Cookbook/Deploy/idxfile.php',
            __DIR__ . '/../Cookbook/Deploy/idxrc.php'
        );
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
        return Collection::dry();
    }

    public function userDefinedParameters()
    {
        return new UserDefinedCollection($this->parameters());
    }

    public function code()
    {
        return array($this, 'initFile');
    }

    /**
     * Based by composer self-update
     */
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
            $this->idx->output->writeln("<error>An {$filename} already exists, generation skipped.</error>");

            return;
        }

        $this->idx->output->writeln("Creating basic {$filename} file...");

        if (!is_writable($this->baseDir) || false === file_put_contents($idxFile, $data)) {
            throw new \Exception("Cannot write {$filename}, check your permission configuration.");
        }

        $this->idx->output->writeln("{$filename} file created.");
    }

    /**
     * @param Idephix $idx
     */
    public function setIdephix(Idephix $idx)
    {
        $this->idx = $idx;
    }
}
