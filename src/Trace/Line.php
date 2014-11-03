<?php
namespace Vtk13\LibXdebugTrace\Trace;

class Line
{
    public $file;
    public $line;

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
