<?php
use Vtk13\LibXdebugTrace\FileUtil\File;
use Vtk13\LibXdebugTrace\Parser\Parser;
use Vtk13\LibXdebugTrace\Trace\Line;
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

    public function testStackTrace()
    {
        $line = new Line('/home/vtk/ws-joomla/joomla.vtk/libraries/joomla/database/query.php', 66);
        $traces = self::$trace->stackTraces($line);
        $this->assertEquals(71, count($traces));
        $this->assertEquals(2, $traces['a66592a5bc3bfa51d59e16e38b52ed61']->getHits());
    }

    public function testStraightTrace()
    {
        $line = new Line('/home/vtk/ws-joomla/joomla.vtk/libraries/joomla/database/query.php', 66);
        $traces = self::$trace->stackTraces($line);
        $straight = $traces['a66592a5bc3bfa51d59e16e38b52ed61']->getStraightTrace();
        $this->assertEquals(15, count($straight));
    }

    public function testLineInfo()
    {
        $line = new Line('/home/vtk/ws-joomla/joomla.vtk/libraries/joomla/database/query.php', 66);
        $tree = self::$trace->callTree($line);
        // TODO
    }
}
