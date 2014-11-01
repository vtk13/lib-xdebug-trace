<?php
namespace Vtk13\LibXdebugTrace\Trace;

use Exception;
use Vtk13\LibXdebugTrace\File;

class Trace
{
    /**
     * @var Node
     */
    protected $root;

    public function __construct(Node $root)
    {
        $this->root = $root;
    }

    public function traverse($callback, Node $node = null)
    {
        if (empty($node)) {
            $node = $this->root;
        }
        // all real node must have filename, so exclude fake nodes by this check
        if ($node->file) {
            $callback($node);
        }
        foreach ($node->children as $each) {
            $this->traverse($callback, $each);
        }
    }

    public function files($prefix = '')
    {
        $res = array();
        $this->traverse(function(Node $node) use (&$res, $prefix) {
            $res[str_replace($prefix, '', $node->file)] = 1;
        });

        $res = array_keys($res);
        sort($res);
        return $res;
    }

    public function fileHierarchy()
    {
        $res = array(
            'name'      => 'root',
            'file'      => 'root',
            'children'  => array(),
        );
        foreach ($this->files() as $file) {
            $current = &$res['children'];
            foreach (explode('/', trim($file, '/')) as $chunk) {
                if (empty($current[$chunk])) {
                    $current[$chunk] = array(
                        'name'      => $chunk,
                        'file'      => $file,
                        'children'  => array(),
                    );
                }
                $current = &$current[$chunk]['children'];
            }
        }
        return $res;
    }

    /**
     * @param File $file
     * @return Line[]
     */
    public function fileCoverage(File $file)
    {
        $fullFileName = null;
        $res = array();
        $this->traverse(function(Node $node) use (&$res, &$fullFileName, $file) {
            if ($file->isRelativeTo($node->file)) {
                if (empty($fullFileName)) {
                    $fullFileName = $node->file;
                } elseif ($fullFileName != $node->file) { // check given $file for ambiguous
                    throw new Exception('Given filename is ambiguous. Found ' . $node->file . ' and ' . $fullFileName);
                }
                // collect line numbers
                $res[$node->line] = new Line($fullFileName, $node->line);
            }
        });

        ksort($res);
        return $res;
    }
}
