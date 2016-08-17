<?php
namespace Idephix;

use Idephix\SSH\SshClient;

class ConfigTest extends \PHPUnit_Framework_TestCase
{


    /** @test */
    public function it_should_create_from_file()
    {
        if (!ini_get('allow_url_include')) {
            $this->markTestSkipped('allow_url_include must be 1');
        }

        $configFileContent =<<<'EOD'
<?php

use \Idephix\SSH\SshClient;

$targets = array('foo' => 'bar');
return \Idephix\Config::fromArray(array(\Idephix\Config::ENVS => $targets, 'sshClient' => new SshClient()));

EOD;

        $configFile = 'data://text/plain;base64,'.base64_encode($configFileContent);

        $config = Config::parseFile($configFile);
        $this->assertEquals(array('foo' => 'bar'), $config[\Idephix\Config::ENVS]);
        $this->assertEquals(array('foo' => 'bar'), $config->environments());
    }

    /** @test */
    public function it_should_create_from_null_file()
    {
        $this->assertEquals(Config::dry(), Config::parseFile(null));
    }

    /**
     * @test
     * @expectedException \Idephix\Exception\InvalidConfigurationException
     */
    public function it_should_throw_exception_for_invalid_config()
    {
        if (!ini_get('allow_url_include')) {
            $this->markTestSkipped('allow_url_include must be 1');
        }

        $configFileContent =<<<'EOD'
<?php

use \Idephix\SSH\SshClient;

$targets = array('foo' => 'bar');
return array('targets' => $targets, 'sshClient' => new SshClient());

EOD;

        $configFile = 'data://text/plain;base64,'.base64_encode($configFileContent);
        Config::parseFile($configFile);
    }

    /**
     * @test
     * @expectedException \Idephix\Exception\InvalidConfigurationException
     */
    public function it_should_throw_exception_for_invalid_file()
    {
        $configFile = '/tmp/foo-non-existing-file';
        Config::parseFile($configFile);
    }
}
