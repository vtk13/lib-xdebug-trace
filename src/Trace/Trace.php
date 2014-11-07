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
        $this->fixParents($this->root);
    }

    protected function fixParents(Node $node)
    {
        foreach ($node->children as $child) {
            $child->parent = $node;
            $this->fixParents($child);
        }
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
            if (!isset($res[$node->file])) {
                $res[$node->file] = new File($node->file);
                $res[$node->file]->hits = 1;
            } else {
                $res[$node->file]->hits++;
            }
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
            $leftPath = '';
            $last = count($chunks) - 1;
            foreach ($chunks as $i => $chunk) {
                $leftPath .= '/' . $chunk;
                if (isset($current->subItems[$chunk])) {
                    $current->subItems[$chunk]->hits += $file->hits;
                } elseif ($i == $last) {
                    $current->subItems[$chunk] = $file;
                } else {
                    $current->subItems[$chunk] = new Directory($leftPath);
                    $current->subItems[$chunk]->hits = $file->hits;
                }
                $current = &$current->subItems[$chunk];
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

    public function stackTraces(Line $line)
    {
        /* @var $res StackTrace[] */
        $res = array();
        $this->traverse(function(Node $node) use ($line, &$res) {
            if ($node->file == $line->file && $node->line == $line->line) {
                $stackTrace = new StackTrace($node);
                if (isset($res[$stackTrace->getId()])) {
                    $res[$stackTrace->getId()]->hit();
                } else {
                    $res[$stackTrace->getId()] = $stackTrace;
                }
            }
        });
        return $res;
    }

    public function callTree(Line $line)
    {
        return array_reduce($this->stackTraces($line), function(&$acc, StackTrace $trace) {
            $current = &$acc;
            foreach ($trace->getStraightTrace() as $node) {
                if (!isset($current[$node->getId()])) {
                    $current[$node->getId()] = array(
                        'file'      => $node->file,
                        'line'      => $node->line,
                        'function'  => $node->parent ? $node->parent->function : '{main}',
                        'children'  => array(),
                    );
                }
                $current = &$current[$node->getId()]['children'];
            }
            return $acc;
        }, array());
    }
}
