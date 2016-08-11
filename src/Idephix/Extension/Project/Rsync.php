<?php

namespace Idephix\Extension\Project;

use Idephix\Extension;
use Idephix\Extension\MethodCollection;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\Idephix;
use Idephix\Task\TaskCollection;

/**
 * Provide a basic rsync interface based on current idx target parameters
 */
class Rsync implements IdephixAwareInterface, Extension
{
    /**
     * @var \Idephix\Context
     */
    private $idx;

    public function setIdephix(Idephix $idx)
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

        $context = $this->idx->getContext();

        if ($context === null) {
            throw new \InvalidArgumentException('Target not provided. Please provide a valid target.');
        }


        $port = $context->get('ssh_params.port');

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

        $remoteConnection = $this->connectionString($context['target.host'], $context->get('ssh_params.user'));

        $cmd = "rsync -rlDcz --force --delete --progress $extraOpts -e '$sshCmd' $localDir $remoteConnection:$remoteDir";

        return $this->idx->local($cmd);
    }

    /**
     * @param $host
     * @param null $user
     * @return string
     */
    private function connectionString($host, $user = null)
    {
        $remoteConnection = '';
        $remoteConnection .= is_null($user) ? '' : "$user@";
        $remoteConnection .= $host;

        return $remoteConnection;
    }
}
