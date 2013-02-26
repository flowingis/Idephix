<?php
namespace Ideato\Util\Test;

require_once __DIR__.'/../../vendor/autoload.php';

use Ideato\Util\ArgumentParser;

class ArgumentParserTest extends \PHPUnit_Framework_TestCase
{

    public function testParser()
    {
        $params = array('cippo', '-v12', '--pippo=tre', '-qwe', '--tallo', '--', 'pippo', 'pluto', '-pa', '--pe', 'ri no');

        $parser = new ArgumentParser();
        $parser->parse($params, array('q', 'w', 'e', 'p', 'a', 'pe'));

        $this->assertTrue($parser->getOption('q'));
        $this->assertTrue($parser->getOption('w'));
        $this->assertTrue($parser->getOption('e'));
        $this->assertTrue($parser->getOption('tallo'));
        $this->assertEquals('tre', $parser->getOption('pippo'));

        $this->assertNull($parser->getOption('p'));
        $this->assertNull($parser->getOption('a'));
        $this->assertNull($parser->getOption('z'));

// @tbd
//        $this->assertEquals('12', $parser->getOption('v'));
        $this->assertEquals(
            array('cippo', 'pippo', 'pluto', '-pa', '--pe', 'ri no'),
            $parser->getParams());
    }

}