<?php

namespace Idephix\Extension\Project;

use Idephix\Extension\MethodProvider;
use Idephix\Extension\MethodCollection;
use Idephix\Extension\ContextAwareInterface;
use Idephix\Context;

/**
 * Provide a basic rsync interface based on current Idephix context
 */
class Rsync implements ContextAwareInterface, MethodProvider
{
    /**
     * @var \Idephix\Context
     */
    private $ctx;

    public function setContext(Context $ctx)
    {
        $this->ctx = $ctx;
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

    public function rsyncProject($remoteDir, $localDir = null, $exclude = null, $extraOpts = null)
    {
        if (substr($remoteDir, -1) != '/') {
            $remoteDir .= '/';
        }

        if (file_exists($exclude)) {
            $extraOpts .= ' --exclude-from='.escapeshellarg($exclude);
        } elseif (!empty($exclude)) {
            $exclude = is_array($exclude) ? $exclude : array($exclude);
            $extraOpts .= array_reduce($exclude, function ($carry, $item) {
                return $carry.' --exclude='.escapeshellarg($item);
            });
        }

        $sshCmd = 'ssh';

        $sshParams = $this->ctx->getSshParams();
        $port = isset($sshParams['port']) ? $sshParams['port'] : 22;

        if ($port) {
            $sshCmd .= ' -p ' . $port;
        }

        $remoteConnection = $this->connectionString(
            $this->ctx->getCurrentHost(),
            $sshParams['user']
        );

        $cmd = "rsync -rlDcz --force --delete --progress $extraOpts -e '$sshCmd' $localDir $remoteConnection:$remoteDir";

        return $this->ctx->local($cmd);
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
