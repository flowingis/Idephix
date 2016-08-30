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

    /**
     * @test
     * @expectedException \Idephix\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /Each env must be an array/
     */
    public function an_env_must_be_an_array_of_data()
    {
        $config = Config::fromArray(array('envs' => array('prod' => 'bar')));
    }

    /** @test */
    public function an_env_should_have_default_values()
    {
        $defaultEnvData = array(
            'hosts' => array(null),
            'ssh_params' => array(
                'user' => '',
                'password' => '',
                'public_key_file' => '',
                'private_key_file' => '',
                'private_key_file_pwd' => '',
                'ssh_port' => '22'
            )
        );

        $config = Config::fromArray(array('envs' => array('prod' => array())));

        $this->assertEquals($defaultEnvData, $config['envs.prod']);

        $config = Config::fromArray(array('envs' => array('prod' => array('hosts' => array()))));

        $this->assertEquals($defaultEnvData, $config['envs.prod']);
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

return array(
    'envs' => array('foo' => 'bar'),
    'ssh_client' => new SshClient()
);

EOD;

        $configFile = 'data://text/plain;base64,'.base64_encode($configFileContent);

        $config = Config::parseFile($configFile);
        $defaultEnvData = array(
            'prod' => array(
                'hosts' => array(null),
                'ssh_params' => array(
                    'user' => '',
                    'password' => '',
                    'public_key_file' => '',
                    'private_key_file' => '',
                    'private_key_file_pwd' => '',
                    'ssh_port' => '22'
                )
            )
        );
        $this->assertEquals(
            $defaultEnvData,
            $config[\Idephix\Config::ENVS]
        );

        $this->assertEquals($defaultEnvData, $config->environments());
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

return new stdClass();

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

    /**
     * @test
     */
    public function it_should_allow_to_retrieve_value_as_path()
    {
        $conf = array(
            'envs' => array(
                'prod' => array(
                    'foo' => '/var/www/',
                    'bar' => '/var/www',
                )
            )
        );

        $config = Config::fromArray($conf);

        $this->assertEquals('/var/www/', $config->getAsPath('envs.prod.foo'));
        $this->assertEquals('/var/www/', $config->getAsPath('envs.prod.bar'));
    }
}
