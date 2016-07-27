<?php

function deploy(Idephix\IdephixInterface $idx, $go = false)
{
    $dryRun = !$go;
    
    /** @var \Idephix\Context $target */
    $target = $idx->getCurrentTarget();
    $sharedFiles = $target->get('deploy.shared_files', array());

    $sharedFolders = $target->get('deploy.shared_folders', array());
    $remoteBaseDir = $target->get('deploy.remote_base_dir');
    $rsyncExclude = $target->get('deploy.rsync_exclude');
    $repository = $target->get('deploy.repository');
    $deployBranch = $target->get('deploy.branch');

    $nextRelease = "$remoteBaseDir/releases/" . time();
    $linkedRelease = "$remoteBaseDir/current";
    $localArtifact = '.deploy';

    $idx->local(
        "
        rm -Rf {$localArtifact} && \\
        git clone {$repository} {$localArtifact} && \\
        cd {$localArtifact} && \\
        git fetch && \\
        git checkout --force {$deployBranch} && \\
        composer install --no-dev --prefer-dist --no-progress --optimize-autoloader --no-interaction
    ",
        $dryRun
    );

    $idx->remote(
        "mkdir -p {$remoteBaseDir}/releases && \\
         mkdir -p {$remoteBaseDir}/shared",
        $dryRun
    );

    foreach ($sharedFolders as $folder) {
        $idx->remote("mkdir -p {$remoteBaseDir}/shared/{$folder}", $dryRun);
    }

    foreach ($sharedFiles as $file) {
        $sharedFile = "{$remoteBaseDir}/shared/{$file}";
        $idx->remote("mkdir -p `dirname '{$sharedFile}'` && touch \"$sharedFile\"", $dryRun);
    }

    try {
        $idx->remote("cd $remoteBaseDir && cp -pPR `readlink {$linkedRelease}` $nextRelease", $dryRun);
    } catch (\Exception $e) {
        $idx->output()->writeln('<info>First deploy, sending the whole project</info>');
    }

    $extraOptions = $dryRun ? '--dry-run' : '';

    $idx->rsyncProject($nextRelease, $localArtifact . '/', $rsyncExclude, $extraOptions);

    foreach (array_merge($sharedFiles, $sharedFolders) as $item) {
        $idx->remote("rm -r $nextRelease/$item", $dryRun);
        $idx->remote("ln -nfs $remoteBaseDir/shared/$item $nextRelease/$item", $dryRun);
    }

    $idx->remote(
        "
        cd $remoteBaseDir && \\
        ln -nfs $nextRelease current",
        $dryRun
    );
}
