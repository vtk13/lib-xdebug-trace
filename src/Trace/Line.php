<?php
namespace Vtk13\LibXdebugTrace\Trace;

class Line
{
    public $file;
    public $line;

    public $hits = 1;

    public function __construct($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }

    public function getId()
    {
        return $this->file . ':' . $this->line;
    }
}
