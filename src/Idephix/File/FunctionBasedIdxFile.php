<?php
namespace Idephix\File;

use Idephix\Config;
use Idephix\File\Node\IdxTaskVisitor;
use Idephix\IdxSetupCollector;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;

class FunctionBasedIdxFile implements IdxFile
{

    /**
     * @var Parser
     */
    private $parser;

    public function __construct($idxfile, $configFile = null)
    {
        $executionContext = $this->extractEnvFromConfigFile($configFile);
        $this->setupCollector = new IdxSetupCollector($executionContext);

        $this->parser = new Parser(new Lexer());
        $this->traverers = new NodeTraverser();
        $this->traverers->addVisitor(new NameResolver());
        $this->traverers->addVisitor(new IdxTaskVisitor($this->setupCollector));

        $stmts = $this->parser->parse(file_get_contents($idxfile));
        $this->traverers->traverse($stmts);
    }

    public function executionContext()
    {
        return $this->setupCollector->getExecutionContext();
    }

    public function output()
    {
        $this->setupCollector->output();
    }

    public function input()
    {
        $this->setupCollector->input();
    }

    public function tasks()
    {
        return $this->setupCollector->getTasks();
    }

    public function libraries()
    {
        return array();
    }

    /**
     * @param $configFile
     * @return Config
     */
    private function extractEnvFromConfigFile($configFile)
    {
        if ($configFile) {
            /** @var Config $executionContext */
            $executionContext = require_once $configFile;
        } else {
            $executionContext = Config::dry();
        }

        return $executionContext;
    }
}
