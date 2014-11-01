<?php
namespace Vtk13\LibXdebugTrace\Trace;

use Exception;
use Vtk13\LibXdebugTrace\FileUtil\Directory;
use Vtk13\LibXdebugTrace\FileUtil\File;

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

    /**
     * @return File[]
     */
    public function files()
    {
        $res = array();
        $this->traverse(function(Node $node) use (&$res) {
            $res[$node->file] = new File($node->file);
        });

        ksort($res);
        return $res;
    }

    public function fileHierarchy()
    {
        $root = new Directory('/');
        foreach ($this->files() as $file) {
            $current = &$root;
            $chunks = explode('/', trim($file->getFullName(), '/'));
            $last = count($chunks) - 1;
            foreach ($chunks as $i => $chunk) {
                if (isset($current->subItems[$chunk])) {
                    $current = &$current->subItems[$chunk];
                } elseif ($i == $last) {
                    $current->subItems[$chunk] = $file;
                } else {
                    $current->subItems[$chunk] = new Directory($chunk);
                    $current = &$current->subItems[$chunk];
                }
            }
        }
        return $root;
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
