<?php

function deploy(Idephix\IdephixInterface $idx, $go = false)
{
    /** @var \Idephix\Context $config */
    $config = $idx->getCurrentTarget();
    $sharedFiles = $config->get('deploy.shared_files', array());
    $sharedFolders = $config->get('deploy.shared_folders', array());
    $remoteBaseDir = $config->get('deploy.remote_base_dir');
    $rsyncExclude = $config->get('deploy.rsync_exclude');
    $repository = $config->get('deploy.repository');
    $deployBranch = $config->get('deploy.branch');
    $nextRelease = "$remoteBaseDir/releases/" . time();
    $linkedRelease = "$remoteBaseDir/current";
    $localArtifact = '.deploy';
    $idx->prepareArtifact($localArtifact, $repository, $deployBranch);
    $idx->prepareSharedFilesAndFolders($remoteBaseDir, $sharedFolders, $sharedFiles);
    try {
        $idx->remote("cd $remoteBaseDir && cp -pPR `readlink {$linkedRelease}` $nextRelease");
    } catch (\Exception $e) {
        $idx->output()->writeln('<info>First deploy, sending the whole project</info>');
    }
    $dryRun = $go ? '' : '--dry-run';
    $idx->rsyncProject($nextRelease, $localArtifact . '/', $rsyncExclude, $dryRun);
    $idx->linkSharedFilesAndFolders($sharedFiles, $sharedFolders, $nextRelease, $remoteBaseDir);
    $idx->switchToNextRelease($remoteBaseDir, $nextRelease);
}

function prepareArtifact(Idephix\IdephixInterface $idx, $localArtifact, $repository, $deployBranch)
{
    $idx->local(
        "
        rm -Rf {$localArtifact} && \\
        git clone {$repository} {$localArtifact} && \\
        cd {$localArtifact} && \\
        git fetch && \\
        git checkout --force {$deployBranch} && \\
        composer install --no-dev --prefer-dist --no-progress --optimize-autoloader --no-interaction
    "
    );
}

function prepareSharedFilesAndFolders(Idephix\IdephixInterface $idx, $remoteBaseDir, $sharedFolders, $sharedFiles)
{
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
}
function linkSharedFilesAndFolders(Idephix\IdephixInterface $idx, $sharedFiles, $sharedFolders, $nextRelease, $remoteBaseDir)
{
    foreach (array_merge($sharedFiles, $sharedFolders) as $item) {
        $idx->remote("rm -r $nextRelease/$item");
        $idx->remote("ln -nfs $remoteBaseDir/shared/$item $nextRelease/$item");
    }
}

function switchToNextRelease(Idephix\IdephixInterface $idx, $remoteBaseDir, $nextRelease)
{
    $idx->remote(
        "
        cd $remoteBaseDir && \\
        ln -nfs $nextRelease current"
    );
}
