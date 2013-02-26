<?php
namespace Ideato\Deploy\Test;

require_once __DIR__.'/../vendor/autoload.php';

use Ideato\SSH\CLISshProxy;

class CLISshProxyTest extends \PHPUnit_Framework_TestCase
{

  public function testConnect()
  {
    $sshClient = new CLISshProxy();
    $sshClient->setExecutable('cmd');
    $sshClient->setParams($params);
    $this->assertTrue($sshClient->connect());

    $this->assertEquals('text', $sshClient->exec('text'));
  }

}