<?php

function deploy(Idephix\IdephixInterface $idx)
{
    /** @var \Idephix\Context $config */
    $config = $idx->getCurrentTarget();
    $sharedFiles = $config->get('deploy.shared_files', array());

    $sharedFolders = $config->get('deploy.shared_folders', array());
    $sshHost = $config->get('hosts');
    $remoteBaseDir = $config->get('deploy.remote_base_dir');
    $rsyncExclude = $config->get('deploy.rsync_exclude');
    $rsyncInclude = $config->get('deploy.rsync_include');
    $repository = $config->get('deploy.repository');

    $nextRelease = "$remoteBaseDir/releases/" . time();
    $linkedRelease = "$remoteBaseDir/current";
    $localArtifact = '.deploy';

    //prepare the deployable release (locally)
    $idx->local(
        "
        rm -Rf {$localArtifact} && \\
        git clone {$repository} {$localArtifact} && \\
        cd {$localArtifact} && \\
        git fetch && \\
        git checkout --force origin/master && \\
        composer install --no-dev --prefer-dist --no-progress --optimize-autoloader --no-interaction
    "
    );

    //Prepare remote directories
    $idx->remote(
        "mkdir -p {$remoteBaseDir}/releases && \\
         mkdir -p {$remoteBaseDir}/shared"
    );

    foreach ($sharedFolders as $folder) {
        $idx->remote("mkdir -p {$remoteBaseDir}/shared/{$folder}");
    }

    foreach ($sharedFiles as $file) {
        $sharedFile = "{$remoteBaseDir}/shared/{$file}";
        $idx->remote("mkdir -p `dirname '{$sharedFile}'` && touch \"$sharedFile\"");
    }

    //copy the current release into the next release so rsync will not transfer unmodified file
    try {
        $idx->remote("cd $remoteBaseDir && cp -pPR `readlink {$linkedRelease}` $nextRelease");
    } catch (\Exception $e) {
        $idx->output()->writeln('<info>First deploy, sending the whole project</info>');
    }

    //sync next release
    $idx->local(
        "rsync -rlpDvcz --delete --exclude-from={$rsyncExclude} --include-from={$rsyncInclude} {$localArtifact}/ {$sshHost}:{$nextRelease}"
    );

    //prepare shared items for next release
    foreach (array_merge($sharedFiles, $sharedFolders) as $item) {
        $idx->remote("ln -nfs $remoteBaseDir/shared/$item $nextRelease/$item");
    }

    //link next release as current release
    $idx->remote("
        cd $remoteBaseDir && \\
        ln -nfs $nextRelease current
    ");
}
