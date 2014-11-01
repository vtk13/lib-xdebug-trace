<?php
namespace Vtk13\LibXdebugTrace;

class File
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getFullName()
    {
        return $this->name;
    }

    public function getBaseName()
    {
        return pathinfo($this->name, PATHINFO_BASENAME);
    }

    public function getSize()
    {
        return filesize($this->name);
    }

    public function getMTime($format = null)
    {
        if ($format) {
            return date($format, filemtime($this->name));
        } else {
            return filemtime($this->name);
        }
    }

    public function isRelativeTo($absolutePath)
    {
        return substr($absolutePath, -strlen($this->name)) == $this->name;
    }
}
