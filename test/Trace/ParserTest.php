<?php
use Vtk13\LibXdebugTrace\File;
use Vtk13\LibXdebugTrace\Parser\Parser;
use Vtk13\LibXdebugTrace\Trace\Trace;

class ParserTestClass extends PHPUnit_Framework_TestCase
{
    /**
     * @var Trace
     */
    static $trace;

    public static function setUpBeforeClass()
    {
        $fileName = dirname(__DIR__) . '/joomla.xt';
        $parser = new Parser();
        self::$trace = $parser->parse(new File($fileName));
    }

    public function testCount()
    {
        $count = 0;
        self::$trace->traverse(function() use (&$count) {
            $count++;
        });
        $this->assertEquals(25956, $count);
    }

    public function testFiles()
    {
        $res = self::$trace->files();
        $this->assertEquals(133, count($res));
        $this->assertTrue(in_array('/home/vtk/ws-joomla/joomla.vtk/administrator/index.php', $res));
    }

    public function testFileCoverage()
    {
        $res = self::$trace->fileCoverage(new File('/home/vtk/ws-joomla/joomla.vtk/administrator/index.php'));
        $this->assertEquals(21, count($res));
    }
}
