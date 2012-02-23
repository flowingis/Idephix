<?php
namespace Ideato\Deploy\Test;

require_once __DIR__.'/../src/lib/SshClient.php';
require_once __DIR__.'/../src/lib/FakeSsh2Proxy.php';

use Ideato\Deploy\SshClient;
use Ideato\Deploy\FakeSsh2Proxy;

class SshClientTest extends \PHPUnit_Framework_TestCase
{

  public function testConnect()
  {
    $params = array(
                    'ssh_port' => 22,
                    'user' => 'test',
                    'public_key_file' => 'pubkey',
                    'private_key_file' => 'privatekey',
                    'private_key_file_pwd' => 'pwd');

    $sshClient = new SshClient(new FakeSsh2Proxy($this));
    $sshClient->setParams($params);
    $sshClient->connect();

    $this->assertEquals('text', $sshClient->exec('text'));
  }

}