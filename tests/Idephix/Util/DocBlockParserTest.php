<?php
namespace Idephix\Util\Test;

use Idephix\Util\DocBlockParser;

class DocBlockParserTest extends \PHPUnit_Framework_TestCase
{

    public function testParse()
    {
        $fn =
        /**
         * Anonymous function description
         * splitted in two lines
         *
         * @param bool $param1 parameter1 description
         * @param string $param2 parameter2 description
         * @param string $param3
         * @return string nothing interesting
         */
        function ($param1 = true, $param2 = 'bho')
        {
            // function body
        };

        $reflector = new \ReflectionFunction($fn);
        $comment = $reflector->getDocComment();
        $parser = new DocBlockParser($comment);

        $this->assertEquals(
            'Anonym function description splitted in two lines',
            $parser->getDescription());

        $this->assertFalse($parser->hasParam('param4'));
        $this->assertTrue($parser->hasParam('param1'));
        $param1 = $parser->getParam('param1');
        $this->assertEquals('param1', $param1['name']);
        $this->assertEquals('bool', $param1['type']);
        $this->assertEquals('parameter1 description', $param1['description']);
        $this->assertEquals('parameter1 description', $parser->getParamDescription('param1'));
        $this->assertEquals('', $parser->getParamDescription('param4'));

        $params = $parser->getParams();
        $this->assertTrue(is_array($params));
        $this->assertEquals(3, count($params));
        $this->assertEquals('param1', $params['param1']['name']);
        $this->assertEquals('bool', $params['param1']['type']);
        $this->assertEquals('parameter1 description', $params['param1']['description']);
        $this->assertEquals('param3', $params['param3']['name']);
        $this->assertEquals('string', $params['param3']['type']);
        $this->assertEquals('', $params['param3']['description']);
    }
}