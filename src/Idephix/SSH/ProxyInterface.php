<?php

namespace Idephix\SSH;

interface ProxyInterface
{
    public function connect($host, $port);

    public function authByPassword($user, $pwd);
    public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd);
    public function authByAgent($user);

    /**
     * @param string $cmd the command to be execute
     *
     * @return true in case of success, false otherwise
     */
    public function exec($cmd);

    /**
     * Copy a file via scp from $localPath to $remotePath
     *
     * @param $localPath source local path
     * @param $remotePath destination remote path
     * @return bool
     */
    public function put($localPath, $remotePath);

    /**
     * Copy a file via scp from $remotePath to $localPath
     *
     * @param $remotePath source remote path
     * @param $localPath destination local path
     * @return bool
     */
    public function get($remotePath, $localPath);

    public function getLastError();
    public function getLastOutput();
}
