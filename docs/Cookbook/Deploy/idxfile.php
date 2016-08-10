<?php

function deploy(Idephix\Context $idx, $go = false)
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
    $idx->prepareArtifact($localArtifact, $repository, $deployBranch, $go);
    $idx->prepareSharedFilesAndFolders($remoteBaseDir, $sharedFolders, $sharedFiles, $go);
    try {
        $idx->remote("cd $remoteBaseDir && cp -pPR `readlink {$linkedRelease}` $nextRelease");
    } catch (\Exception $e) {
        $idx->output()->writeln('<info>First deploy, sending the whole project</info>');
    }
    $dryRun = $go ? '' : '--dry-run';
    $idx->rsyncProject($nextRelease, $localArtifact . '/', $rsyncExclude, $dryRun, $go);
    $idx->linkSharedFilesAndFolders($sharedFiles, $sharedFolders, $nextRelease, $remoteBaseDir, $go);
    $idx->switchToNextRelease($remoteBaseDir, $nextRelease, $go);
}

function prepareArtifact(Idephix\Context $idx, $localArtifact, $repository, $deployBranch, $go = false)
{
    $idx->local(
        "
        rm -Rf {$localArtifact} && \\
        git clone {$repository} {$localArtifact} && \\
        cd {$localArtifact} && \\
        git fetch && \\
        git checkout --force {$deployBranch} && \\
        composer install --no-dev --prefer-dist --no-progress --optimize-autoloader --no-interaction
        ",
        !$go
    );
}

function prepareSharedFilesAndFolders(Idephix\Context $idx, $remoteBaseDir, $sharedFolders, $sharedFiles, $go = false)
{
    $idx->remote(
        "mkdir -p {$remoteBaseDir}/releases && \\
         mkdir -p {$remoteBaseDir}/shared",
        !$go
    );
    foreach ($sharedFolders as $folder) {
        $idx->remote("mkdir -p {$remoteBaseDir}/shared/{$folder}", !$go);
    }
    foreach ($sharedFiles as $file) {
        $sharedFile = "{$remoteBaseDir}/shared/{$file}";
        $idx->remote("mkdir -p `dirname '{$sharedFile}'` && touch \"$sharedFile\"", !$go);
    }
}
function linkSharedFilesAndFolders(Idephix\Context $idx, $sharedFiles, $sharedFolders, $nextRelease, $remoteBaseDir, $go = false)
{
    foreach (array_merge($sharedFiles, $sharedFolders) as $item) {
        $idx->remote("rm -r $nextRelease/$item", !$go);
        $idx->remote("ln -nfs $remoteBaseDir/shared/$item $nextRelease/$item", !$go);
    }
}

function switchToNextRelease(Idephix\Context $idx, $remoteBaseDir, $nextRelease, $go = false)
{
    $idx->remote(
        "
        cd $remoteBaseDir && \\
        ln -nfs $nextRelease current",
        !$go
    );
}
