<?php
namespace Vtk13\LibXdebugTrace\Trace;

class StackTrace
{
    protected $nodes;

    protected $hits = 1;

    protected $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
        $this->nodes = array(
            $node->getId() => $node,
        );
        while ($node->parent) {
            $this->nodes = array_merge(
                array($node->getId() => $node),
                $this->nodes
            );
            $node = $node->parent;
        }
    }

    public function getId()
    {
        return md5(implode(':', array_keys($this->nodes)));
    }

    public function hit()
    {
        $this->hits++;
    }

    public function getHits()
    {
        return $this->hits;
    }

    public function getNode()
    {
        return $this->node;
    }

    public function getFrom()
    {
        return $this->node->parent;
    }

    /**
     * @return Node[]
     */
    public function getStraightTrace()
    {
        $res = array();
        $node = $this->node;
        while ($node->parent && $node->level > 0) {
            $res[$node->level] = $node;
            $node = $node->parent;
        }
        ksort($res);
        return $res;
    }
}
