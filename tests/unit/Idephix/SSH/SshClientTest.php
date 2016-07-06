<?php
namespace Idephix\SSH\Test;

use Idephix\SSH\SshClient;
use Idephix\Test\SSH\StubProxy;

class SshClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->params = array(
            'ssh_port' => 23,
            'user' => 'test',
            'public_key_file' => 'pubkey',
            'private_key_file' => 'privatekey',
            'private_key_file_pwd' => 'pwd');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You must set the host
     */
    public function testConnectNoHost()
    {
        $sshClient = new SshClient(new StubProxy());
        $sshClient->setParameters($this->params);
        $sshClient->connect();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Unable to connect
     */
    public function testConnectionFail()
    {
        $sshClient = new SshClient(new StubProxy());
        $sshClient->setParameters($this->params);
        $sshClient->setHost('fail_connection');
        $sshClient->connect();
    }

    public function testConnect()
    {
        $sshClient = new SshClient(new StubProxy());
        $sshClient->setParameters($this->params);
        $sshClient->setHost('localhost');
        $sshClient->connect();

        $this->assertEquals('text', $sshClient->exec('text'));
        $this->assertEquals('test out text', $sshClient->getLastOutput());
        $this->assertEquals('test err text', $sshClient->getLastError());
        $this->assertEquals('test', $sshClient->getUser());
        $this->assertEquals('23', $sshClient->getPort());
        $this->assertEquals('localhost', $sshClient->getHost());
        $sshClient->setHost('host');
        $this->assertEquals('host', $sshClient->getHost());
    }
}
/* @todo test the rest of connection method
        if (!empty($this->params['password']) && !$this->proxy->authByPassword($this->params['user'], $this->params['password'])) {
            throw new \Exception("Unable to authenticate via password");
        }

        if (!empty($this->params['public_key_file']) && !$this->proxy->authByPublicKey($this->params['user'], $this->params['public_key_file'], $this->params['private_key_file'], $this->params['private_key_file_pwd'])) {
            throw new \Exception("Unable to authenticate via public/private keys");
        }

        if (!$this->proxy->authByAgent($this->params['user'])) {
            throw new \Exception("Unable to authenticate via agent");
        }
*/
