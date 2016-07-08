<?php
namespace Idephix;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_give_array_access()
    {
        $context = Config::fromArray(array('foo' => 'bar'));
        $this->assertEquals('bar', $context['foo']);

        $context = Config::fromArray(array());
        $context['foo'] = 'bar';
        $this->assertEquals('bar', $context['foo']);
    }

    /**
     * @test
     */
    public function it_should_allow_default_value()
    {
        $context = Config::fromArray(array('foo' => 'bar', 'targets' => array('host' => 'localhost')));
        $this->assertEquals('i-am-default', $context->get('not-present', 'i-am-default'));
        $this->assertEquals('localhost', $context->get('targets.host', 'i-am-default'));
    }

    /**
     * @test
     */
    public function it_should_allow_to_retrieve_data_using_dot_notation()
    {
        $context = Config::fromArray(
            array(
                'targets' => array(
                    'prod' => array(
                        'release_dir' => '/var/www'
                    )
                ),
            )
        );

        $this->assertEquals(array('release_dir' => '/var/www'), $context['targets.prod']);
        $this->assertEquals('/var/www', $context['targets.prod.release_dir']);
    }

    /**
     * @test
     */
    public function it_should_allow_to_set_data_using_dot_notation()
    {
        $context = Config::fromArray(array());
        $context['targets'] = array('prod' => array('release_dir' => '/var/www'));

        $this->assertEquals('/var/www', $context['targets.prod.release_dir']);
    }

    /**
     * @test
     */
    public function it_should_return_null_for_non_existing_key()
    {
        $context = Config::fromArray(array('targets' => array('prod' => array('release_dir' => '/var/www'))));

        $this->assertNull($context['sshClient']);
    }

    /**
     * @test
     */
    public function it_should_allow_to_resolve_callable_elements()
    {
        $spy = new LazyConfigSpy();
        $context = Config::fromArray(
            array(
                'foo' => function () use ($spy) {
                    $spy->resolved = true;
                    return 'bar';
                }
            )
        );

        $this->assertFalse($spy->resolved);
        $this->assertEquals('bar', $context['foo']);
        $this->assertTrue($spy->resolved);
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
return \Idephix\Config::fromArray(array('targets' => $targets, 'sshClient' => new SshClient()));

EOD;

        $configFile = 'data://text/plain;base64,'.base64_encode($configFileContent);

        $config = Config::parseFile($configFile);
        $this->assertEquals(array('foo' => 'bar'), $config['targets']);
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

class LazyConfigSpy
{
    public $resolved = false;
}
