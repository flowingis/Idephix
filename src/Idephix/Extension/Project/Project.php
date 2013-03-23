<?php

namespace Idephix\Extension\Project;

use Idephix\Idephix;
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

    public function setIdephix(Idephix $idx)
    {
        $this->idx = $idx;
    }

    public function rsyncProject($remote_dir, $local_dir = null, $exclude = null, $extra_opts = null, $ssh_opts = null)
    {
        if (substr($remote_dir, -1) != '/')
        {
            $remote_dir .= '/';
        }

        $target = $this->idx->getCurrentTarget();
        $user = $target['ssh_params']['user'];
        $host = $this->idx->getCurrentTargetHost();

        if (file_exists($exclude))
        {
          $extra_opts .= ' --exclude-from='.$exclude;
        }

        $cmd = "rsync -rlDcz --force --delete --progress $extra_opts -e 'ssh' ./ $user@$host:$remote_dir";

        return $this->idx->local($cmd);
    }
}
