<?php

namespace Idephix\Extension\SelfUpdate;

use Idephix\Idephix;
use Idephix\IdephixInterface;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\IdephixInterface;

class SelfUpdate implements IdephixAwareInterface
{
    private $idx;

    public function setIdephix(IdephixInterface $idx)
    {
        $this->idx = $idx;
    }

    /**
     * Based by composer self-update
     */
    public function update()
    {
        $baseUrl = 'http://getidephix.com/';
        $latest = trim(file_get_contents($baseUrl.'version'));

        if (Idephix::VERSION !== $latest) {
            $this->idx->output->writeln(sprintf("Updating to version <info>%s</info>.", $latest));

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
                $this->idx->output->writeln('<error>The download is corrupted ('.$e->getMessage().').</error>');
                $this->idx->output->writeln('<error>Please re-run the self-update command to try again.</error>');
            }
        } else {
            $this->idx->output->writeln("<info>You are using the latest idephix version.</info>");
        }
    }
}
