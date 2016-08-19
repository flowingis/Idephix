<?php
namespace Idephix;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    /** @test */
    public function it_should_have_defaults_for_config()
    {
        $config = Config::dry();
        $this->assertInstanceOf('Idephix\SSH\SshClient', $config['ssh_client']);
        $this->assertEquals(array(), $config['envs']);
        $this->assertEquals(array(), $config['extensions']);
    }

    /**
     * @test
     * @expectedException \Idephix\Exception\InvalidConfigurationException
     */
    public function it_should_accept_only_ssh_client_instances()
    {
        $config = Config::fromArray(array('ssh_client' => 'foo'));
    }

    /**
     * @test
     * @expectedException \Idephix\Exception\InvalidConfigurationException
     */
    public function it_should_only_accept_array_as_envs()
    {
        $config = Config::fromArray(array('envs' => 'foo'));
    }

    /**
     * @test
     * @expectedException \Idephix\Exception\InvalidConfigurationException
     */
    public function it_should_only_accept_array_as_extensions()
    {
        $config = Config::fromArray(array('extensions' => 'foo'));
    }

    /**
     * @test
     * @expectedException \Idephix\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /.*foo.*(does not exist)/
     */
    public function it_should_throw_exceptions_for_invalid_key()
    {
        $config = Config::fromArray(array('foo' => 'bar'));
    }

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
return \Idephix\Config::fromArray(array(\Idephix\Config::ENVS => $targets, \Idephix\Config::SSHCLIENT => new SshClient()));

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
return array('envs' => $targets, \Idephix\Config::SSHCLIENT => new SshClient());

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
