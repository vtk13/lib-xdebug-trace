<?php
namespace Vtk13\LibXdebugTrace\Parser;

class ExitEntry extends Entry
{
    public function __construct($level, $callId, $time, $memory)
    {
        parent::__construct($level, $callId, $time, $memory, 0, 0, 0, 0, 0);
    }
}
