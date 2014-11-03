<?php
namespace Vtk13\LibXdebugTrace\Trace;

class Node
{
    public $level;
    public $callId;
    public $timeStart;
    public $timeEnd;
    public $function;
    public $includeFile;
    public $file;
    public $line;
    public $parameters = array();

    /**
     * @var Node
     */
    public $parent;

    /**
     * @var Node[]
     */
    public $children = array();

    public function __construct(
        $level,
        $callId,
        $timeStart,
        $timeEnd,
        $function,
        $includeFile,
        $file,
        $line,
        $parameters = array()
    ) {
        $this->level = $level;
        $this->callId = $callId;
        $this->timeStart = $timeStart;
        $this->timeEnd = $timeEnd;
        $this->function = $function;
        $this->includeFile = $includeFile;
        $this->file = $file;
        $this->line = $line;
        $this->parameters = $parameters;
    }

    public function getLine()
    {
        return new Line($this->file, $this->line);
    }

    public function getId()
    {
        // TODO not an unique in trace actually
        return $this->level . ':' . $this->file . ':' . $this->line;
    }
}
