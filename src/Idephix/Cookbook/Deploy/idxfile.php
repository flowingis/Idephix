<?php

function deploy(Idephix\Context $context, $go = false)
{
    $sharedFiles = $context->get('deploy.shared_files', array());
    $sharedFolders = $context->get('deploy.shared_folders', array());
    $remoteBaseDir = $context->get('deploy.remote_base_dir');
    $rsyncExclude = $context->get('deploy.rsync_exclude');
    $repository = $context->get('deploy.repository');
    $deployBranch = $context->get('deploy.branch');
    $nextRelease = "$remoteBaseDir/releases/" . time();
    $linkedRelease = "$remoteBaseDir/current";
    $localArtifact = '.deploy';
    $context->prepareArtifact($localArtifact, $repository, $deployBranch, $go);
    $context->prepareSharedFilesAndFolders($remoteBaseDir, $sharedFolders, $sharedFiles, $go);
    try {
        $context->remote("cd $remoteBaseDir && cp -pPR `readlink {$linkedRelease}` $nextRelease");
    } catch (\Exception $e) {
        $context->output()->writeln('<info>First deploy, sending the whole project</info>');
    }
    $dryRun = $go ? '' : '--dry-run';
    $context->rsyncProject($nextRelease, $localArtifact . '/', $rsyncExclude, $dryRun, $go);
    $context->linkSharedFilesAndFolders($sharedFiles, $sharedFolders, $nextRelease, $remoteBaseDir, $go);
    $context->switchToNextRelease($remoteBaseDir, $nextRelease, $go);
}

function prepareArtifact(Idephix\Context $context, $localArtifact, $repository, $deployBranch, $go = false)
{
    $context->local(
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

function prepareSharedFilesAndFolders(Idephix\Context $context, $remoteBaseDir, $sharedFolders, $sharedFiles, $go = false)
{
    $context->remote(
        "mkdir -p {$remoteBaseDir}/releases && \\
         mkdir -p {$remoteBaseDir}/shared",
        !$go
    );
    foreach ($sharedFolders as $folder) {
        $context->remote("mkdir -p {$remoteBaseDir}/shared/{$folder}", !$go);
    }
    foreach ($sharedFiles as $file) {
        $sharedFile = "{$remoteBaseDir}/shared/{$file}";
        $context->remote("mkdir -p `dirname '{$sharedFile}'` && touch \"$sharedFile\"", !$go);
    }
}
function linkSharedFilesAndFolders(Idephix\Context $context, $sharedFiles, $sharedFolders, $nextRelease, $remoteBaseDir, $go = false)
{
    foreach (array_merge($sharedFiles, $sharedFolders) as $item) {
        $context->remote("rm -r $nextRelease/$item", !$go);
        $context->remote("ln -nfs $remoteBaseDir/shared/$item $nextRelease/$item", !$go);
    }
}

function switchToNextRelease(Idephix\Context $context, $remoteBaseDir, $nextRelease, $go = false)
{
    $context->remote(
        "
        cd $remoteBaseDir && \\
        ln -nfs $nextRelease current",
        !$go
    );
}
