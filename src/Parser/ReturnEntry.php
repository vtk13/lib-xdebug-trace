<?php
namespace Vtk13\LibXdebugTrace\Parser;

class ReturnEntry extends Entry
{
    public $value;

    public function __construct($level, $callId, $value)
    {
        parent::__construct($level, $callId, 0, 0, 0, 0, 0, 0, 0);
        $this->value = $value;
    }
}
