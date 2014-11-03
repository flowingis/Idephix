<?php
namespace Idephix\SSH\Test;

use Idephix\SSH\CLISshProxy;

class CLISshProxyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->exec = realpath(__DIR__.'/../').'/sshFakeClient';
        $this->proxy = new CLISshProxy();
        $this->proxy->setExecutable($this->exec);
    }

    public function testFakeCliPermission()
    {
        $result = $this->proxy->exec('text');
        $this->assertNotEquals(126, $this->proxy->getExitCode(), "You have to set the execution permission to ".$this->exec);
        $this->assertTrue($result, "Run command");
    }

    public function testConnect()
    {
        $this->assertTrue($this->proxy->connect('myhost', '23'));
        $this->assertTrue($this->proxy->exec('text'));

        $expectedCli = "Array
(
    [0] => $this->exec
    [1] => -p
    [2] => 23
    [3] => myhost
    [4] => text
)
";
    }

    public function testConnectByAgent()
    {
        $this->assertTrue($this->proxy->connect('myhost', '23'));
        $this->proxy->authByAgent('username');
        $this->assertTrue($this->proxy->exec('text'));

        $expectedCli = "Array
(
    [0] => $this->exec
    [1] => -p
    [2] => 23
    [3] => -l
    [4] => username
    [5] => myhost
    [6] => text
)
";
        $this->assertEquals($expectedCli, $this->proxy->getLastOutput());
    }

    public function testConnectByPublicKey()
    {
        $this->assertTrue($this->proxy->connect('myhost', '23'));
        $this->proxy->authByPublicKey('user', 'public_key_file', 'privateKeyFile', 'pwd');
        $this->assertTrue($this->proxy->exec('text'));

        $expectedCli = "Array
(
    [0] => $this->exec
    [1] => -p
    [2] => 23
    [3] => -i
    [4] => privateKeyFile
    [5] => -l
    [6] => user
    [7] => myhost
    [8] => text
)
";
        $this->assertEquals($expectedCli, $this->proxy->getLastOutput());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You first need to connect
     */
    public function testAssertConnect()
    {
        $this->proxy->authByPublicKey('user', 'public_key_file', 'privateKeyFile', 'pwd');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Not implemented
     */
    public function testConnectByPassword()
    {
        $this->proxy->authByPassword('username', 'pwd');
    }
}
