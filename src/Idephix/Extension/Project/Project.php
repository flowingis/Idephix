<?php

namespace Idephix\Extension\Project;

use Idephix\IdephixInterface;
use Idephix\Extension\IdephixAwareInterface;

/**
 * @todo:
 * - allow for $exclude to be an array
 * - manage ports different from the default one
 * - check to undefined params in currentTarget
 */
class Project implements IdephixAwareInterface
{
    private $idx;

    public function setIdephix(IdephixInterface $idx)
    {
        $this->idx = $idx;
    }

    public function rsyncProject($remoteDir, $localDir = null, $exclude = null, $extraOpts = null, $sshOpts = null)
    {
        if (substr($remoteDir, -1) != '/') {
            $remoteDir .= '/';
        }

        $target = $this->idx->getCurrentTarget();
        $user = $target->get('ssh_params.user');
        $host = $this->idx->getCurrentTargetHost();

        if (file_exists($exclude)) {
          $extraOpts .= ' --exclude-from='.$exclude;
        }

        $cmd = "rsync -rlDcz --force --delete --progress $extraOpts -e 'ssh' $localDir $user@$host:$remoteDir";

        return $this->idx->local($cmd);
    }
}
