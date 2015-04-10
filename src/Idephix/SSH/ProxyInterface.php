<?php

namespace Idephix\SSH;

interface ProxyInterface
{
    /**
     * @param string $host
     * @param integer $port
     */
    public function connect($host, $port);

    /**
     * @param string $user
     * @param string $pwd
     */
    public function authByPassword($user, $pwd);

    /**
     * @param string $user
     * @param string $public_key_file
     * @param string $private_key_file
     * @param string $pwd
     */
    public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd);

    /**
     * @param string $user
     */
    public function authByAgent($user);

    /**
     * @param string $cmd the command to be execute
     *
     * @return boolean true in case of success, false otherwise
     */
    public function exec($cmd);

    /**
     * Copy a file via scp from $localPath to $remotePath
     *
     * @param $localPath source local path
     * @param $remotePath destination remote path
     * @return boolean
     */
    public function put($localPath, $remotePath);

    /**
     * Copy a file via scp from $remotePath to $localPath
     *
     * @param $remotePath source remote path
     * @param $localPath destination local path
     * @return boolean
     */
    public function get($remotePath, $localPath);

    /**
     * @return string|null
     */
    public function getLastError();

    /**
     * @return string|null
     */
    public function getLastOutput();
}
