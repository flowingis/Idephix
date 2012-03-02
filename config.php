<?php

/**
 * Config example
 */

$localBaseDir = '/var/www/myproject';

$ssh_params = array(
                'user' => 'ideato',
                'public_key_file' => '/Users/kea/.ssh/id_rsa_ideato.pub',
                'private_key_file' => '/Users/kea/.ssh/id_rsa_ideato',
                'private_key_file_pwd' => 'MyPassword'
              );

$targets = array(
                'prod' => array(
                                'hosts' => array('localhost'),
                                'localBaseFolder' => $localBaseDir,
                                'remoteBaseFolder' => "/tmp/idephix_test/",
                                'rsync_exclude_file' => 'rsync_exclude.txt'
                               ),
                'stag' => array(
                                'hosts' => array('192.168.69.106'),
                                'localBaseFolder' => $localBaseDir,
                                'remoteBaseFolder' => "/var/sites/casanoi.ideato.it/",
                                'rsync_exclude_file' => 'rsync_exclude.txt'
                               ),
              );

function deploy($d) {
  $d->remotePrepare();
  $d->copyCode();
  $d->remoteLinkSharedFolders();
  $d->assetic();
  $d->switchToTheNextRelease();
}

function runTest($d) {
  $d->runPhpUnit();
}