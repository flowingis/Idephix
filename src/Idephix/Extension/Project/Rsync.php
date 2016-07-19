<?php

namespace Idephix\Extension\Project;

use Idephix\Extension;
use Idephix\Extension\MethodCollection;
use Idephix\IdephixInterface;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\Task\TaskCollection;

/**
 * Provide a basic rsync interface based on current idx target parameters
 */
class Rsync implements IdephixAwareInterface, Extension
{
    /**
     * @var \Idephix\IdephixInterface
     */
    private $idx;

    public function setIdephix(IdephixInterface $idx)
    {
        $this->idx = $idx;
    }

    public function name()
    {
        return 'rsync';
    }

    /** @return MethodCollection */
    public function methods()
    {
        return MethodCollection::ofCallables(
            array(
                new Extension\CallableMethod('rsyncProject', array($this, 'rsyncProject'))
            )
        );
    }

    /** @return TaskCollection */
    public function tasks()
    {
        return TaskCollection::dry();
    }

    public function rsyncProject($remoteDir, $localDir = null, $exclude = null, $extraOpts = null)
    {
        if (substr($remoteDir, -1) != '/') {
            $remoteDir .= '/';
        }

        $target = $this->idx->getCurrentTarget();

        if ($target === null) {
            throw new \InvalidArgumentException('Target not provided. Please provide a valid target.');
        }

        $user = $target->get('ssh_params.user');
        $host = $this->idx->getCurrentTargetHost();
        $port = $target->get('ssh_params.port');

        if (file_exists($exclude)) {
            $extraOpts .= ' --exclude-from='.escapeshellarg($exclude);
        } elseif (!empty($exclude)) {
            $exclude = is_array($exclude) ? $exclude : array($exclude);
            $extraOpts .= array_reduce($exclude, function ($carry, $item) {
                return $carry.' --exclude='.escapeshellarg($item);
            });
        }

        $sshCmd = 'ssh';
        if ($port) {
            $sshCmd .= ' -p ' . $port;
        }

        $cmd = "rsync -rlDcz --force --delete --progress $extraOpts -e '$sshCmd' $localDir $user@$host:$remoteDir";

        return $this->idx->local($cmd);
    }
}
