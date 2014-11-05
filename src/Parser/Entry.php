<?php
namespace Vtk13\LibXdebugTrace\Parser;

use Vtk13\LibXdebugTrace\Trace\Node;

class Entry
{
    public $level;
    public $callId;
    public $time;
    public $memory;
    public $function;
    public $userDefined;
    public $includeFile;
    public $file;
    public $line;
    public $parameters = array();

    /**
     * @var Entry
     */
    public $parent;

    /**
     * @var Entry|Node[]
     */
    public $children = array();

    public function __construct(
        $level,
        $callId,
        $time,
        $memory,
        $function,
        $userDefined,
        $includeFile,
        $file,
        $line,
        $parameters = array()
    ) {
        $this->level = $level;
        $this->callId = $callId;
        $this->time = $time;
        $this->memory = $memory;
        $this->function = $function;
        $this->userDefined = $userDefined;
        $this->includeFile = $includeFile;
        $this->file = $file;
        $this->line = $line;
        if (empty($parameters) && !empty($includeFile)) {
            $this->parameters = array($includeFile);
        } else {
            $this->parameters = $parameters;
        }
    }
}
