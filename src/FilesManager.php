<?php
namespace Vtk13\LibXdebugTrace;

class FilesManager
{
    protected $directory;

    public function __construct($directory = null)
    {
        if (is_null($directory)) {
            $directory = ini_get('xdebug.trace_output_dir');
        }
        $directory = realpath($directory);
        $this->directory = rtrim($directory, '/');
    }

    public function getTraceFile($baseName)
    {
        $name = realpath($this->directory . '/' . $baseName);
        if (substr($name, 0, strlen($this->directory)) != $this->directory) {
            throw new \Exception('Invalid trace basename. "../" in name?');
        }
        return new File($name);
    }

    /**
     * @return File[]
     */
    public function listTraceFiles()
    {
        return array_map(
            function($fileName) {
                return new File($fileName);
            },
            glob($this->directory . '/*.xt')
        );
    }
}
