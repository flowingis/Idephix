<?php
namespace Ideato\Deploy\Test;

require_once __DIR__.'/../vendor/autoload.php';

use Ideato\Util\PhpFunctionParser;

class PhpFunctionParserTest extends \PHPUnit_Framework_TestCase
{

  public function testParser()
  {
// THIS CODE IS NOT PARSED CORRECTLY
//function Pluto(\$param1, \$param2, \$param3 = 'ciao', \$param4=42){
//  echo 'My function is great!';
//}

    $code =<<<EOF

function Pluto(\$param1,
\$param2, \$param3 = 'ciao', \$param4=42){
  echo 'Pluto is great!';
}

function
Pippo_Paperino21 (\$param)
  { echo "WUAO!"; }

      function    Pippo
() { }

EOF;

    $parser = new PhpFunctionParser($code);
    $functions = $parser->getFunctions();

    $this->assertTrue(is_array($functions));
    $this->assertEquals('Pluto', $functions[0]['name']);
    $this->assertEquals('Pippo_Paperino21', $functions[1]['name']);
    $this->assertEquals('Pippo', $functions[2]['name']);

    $this->assertEquals(array(
                          array('name' => '$param1', 'required' => true, 'default' => ''),
                          array('name' => '$param2', 'required' => true, 'default' => ''),
                          array('name' => '$param3', 'required' => false, 'default' => "'ciao'"),
                          array('name' => '$param4', 'required' => false, 'default' => 42)),
                        $functions[0]['params']);
  }

}