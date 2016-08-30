<?php

namespace Idephix\Task\Builtin;

use Idephix\Task\Parameter;
use Idephix\Idephix;
use Idephix\Task\Task;

class SelfUpdate implements IdephixAwareInterface, Task
{
    private $ctx;

    public function setContext(Idephix $ctx)
    {
        $this->ctx = $ctx;
    }

    public function name()
    {
        return 'selfupdate';
    }

    public function description()
    {
        return 'Update Idephix to the latest version';
    }

    public function code()
    {
        return array($this, 'update');
    }

    public function parameters()
    {
        return Parameter\Collection::dry();
    }

    public function userDefinedParameters()
    {
        return new Parameter\UserDefinedCollection($this->parameters());
    }

    /**
     * Based by composer self-update
     */
    public function update()
    {
        $baseUrl = 'http://getidephix.com/';
        $latest = trim(file_get_contents($baseUrl.'version'));

        if (Idephix::VERSION !== $latest) {
            $this->idx->output->writeln(sprintf('Updating to version <info>%s</info>.', $latest));

            $remoteFilename = $baseUrl.'idephix.phar';
            $localFilename = $_SERVER['argv'][0];
            $tempFilename = basename($localFilename, '.phar').'-temp.phar';

            file_put_contents($tempFilename, file_get_contents($remoteFilename));

            try {
                chmod($tempFilename, 0777 & ~umask());
                // test the phar validity
                $phar = new \Phar($tempFilename);
                // free the variable to unlock the file
                unset($phar);
                rename($tempFilename, $localFilename);
            } catch (\Exception $e) {
                @unlink($tempFilename);
                if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                    throw $e;
                }
                $this->ctx->writeln('<error>The download is corrupted ('.$e->getMessage().').</error>');
                $this->ctx->writeln('<error>Please re-run the self-update command to try again.</error>');
            }
        } else {
            $this->ctx->writeln('<info>You are using the latest idephix version.</info>');
        }
    }
}
