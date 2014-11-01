<?php
use Vtk13\LibXdebugTrace\FileUtil\File;
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
        $this->assertInstanceOf(File::class, $res['/home/vtk/ws-joomla/joomla.vtk/administrator/index.php']);
    }

    public function testFileCoverage()
    {
        $res = self::$trace->fileCoverage(new File('/home/vtk/ws-joomla/joomla.vtk/administrator/index.php'));
        $this->assertEquals(21, count($res));
    }

    public function testFileHierarchy()
    {
        $root = self::$trace->fileHierarchy();
        $this->assertEquals(
            '/home/vtk/ws-joomla/joomla.vtk/administrator/index.php',
            $root->subItems['home']
                ->subItems['vtk']
                ->subItems['ws-joomla']
                ->subItems['joomla.vtk']
                ->subItems['administrator']
                ->subItems['index.php']->getFullName()
        );
    }
}
