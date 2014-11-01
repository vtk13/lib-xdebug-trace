<?php
namespace Vtk13\LibXdebugTrace;

class Logger
{
    public function warning($message)
    {
        error_log($message . "\n");
    }
}
