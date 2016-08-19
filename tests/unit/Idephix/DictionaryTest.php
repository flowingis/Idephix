<?php
namespace Idephix;

class DictionaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_give_array_access()
    {
        $context = Dictionary::fromArray(array('foo' => 'bar'));
        $this->assertEquals('bar', $context['foo']);

        $context = Dictionary::fromArray(array());
        $context['foo'] = 'bar';
        $this->assertEquals('bar', $context['foo']);
    }

    /**
     * @test
     */
    public function it_should_allow_default_value()
    {
        $context = Dictionary::fromArray(array('foo' => 'bar', 'envs' => array('host' => 'localhost')));
        $this->assertEquals('i-am-default', $context->get('not-present', 'i-am-default'));
        $this->assertEquals('localhost', $context->get('envs.host', 'i-am-default'));
    }

    /**
     * @test
     */
    public function it_should_allow_to_retrieve_data_using_dot_notation()
    {
        $context = Dictionary::fromArray(
            array(
                'envs' => array(
                    'prod' => array(
                        'release_dir' => '/var/www'
                    )
                ),
            )
        );

        $this->assertEquals(array('release_dir' => '/var/www'), $context['envs.prod']);
        $this->assertEquals('/var/www', $context['envs.prod.release_dir']);
    }

    /**
     * @test
     */
    public function it_should_allow_to_set_data_using_dot_notation()
    {
        $context = Dictionary::fromArray(array());
        $context['envs'] = array('prod' => array('release_dir' => '/var/www'));

        $this->assertEquals('/var/www', $context['envs.prod.release_dir']);
    }

    /**
     * @test
     */
    public function it_should_return_null_for_non_existing_key()
    {
        $context = Dictionary::fromArray(array('envs' => array('prod' => array('release_dir' => '/var/www'))));

        $this->assertNull($context['ssh_client']);
    }

    /**
     * @test
     */
    public function it_should_allow_to_resolve_callable_elements()
    {
        $spy = new LazyConfigSpy();
        $context = Dictionary::fromArray(
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
}


class LazyConfigSpy
{
    public $resolved = false;
}
