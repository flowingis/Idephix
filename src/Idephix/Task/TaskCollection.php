<?php
namespace Idephix\Task;

use Idephix\Exception\InvalidIdxFileException;
use Idephix\Task\Parsing\TaskCollector;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;

class TaskCollection extends CollectionIterator
{
    public static function ofTasks($array)
    {
        return new static(
            new \ArrayIterator(
                array_filter(
                    $array,
                    function ($task) {
                        return $task instanceof Task;
                    }
                )
            )
        );
    }

    public static function ofFunctions($idxFileContent)
    {
        $collection = static::dry();

        $parser = new Parser(new Lexer());
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new TaskCollector($collection));
        $stmts = $parser->parse($idxFileContent);
        $traverser->traverse($stmts);

        return $collection;
    }
    
    public static function parseFile($idxFile)
    {
        try {
            new \SplFileObject($idxFile);
        } catch (\RuntimeException $e) {
            throw new InvalidIdxFileException("$idxFile does not exists or is not readable");
        }

        return static::ofFunctions(file_get_contents($idxFile));
    }

    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Task) {
            throw new \DomainException('TaskCollection can only accept \Idephix\Task\Task object');
        }

        $this->getInnerIterator()->offsetSet($offset, $value);
    }

    public function has($taskName)
    {
        foreach ($this->getInnerIterator() as $task) {
            if ($taskName === $task->name()) {
                return true;
            }
        }

        return false;
    }

    public function get($taskName)
    {
        foreach ($this->getInnerIterator() as $task) {
            if ($taskName === $task->name()) {
                return $task;
            }
        }

        throw new \RuntimeException('Non existing task ' . $taskName);
    }
}
