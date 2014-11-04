<?php
namespace Vtk13\LibXdebugTrace\FileUtil;

class FilesManager
{
    public $directory;

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
        $fullName = $this->directory . '/' . $baseName;
        if (is_file($fullName)) {
            if (is_readable($fullName)) {
                $fullName = realpath($fullName);
                if (substr($fullName, 0, strlen($this->directory)) != $this->directory) {
                    throw new \Exception('Invalid trace basename. "../" in name?');
                } else {
                    return new File($fullName);
                }
            } else {
                throw new \Exception("Trace file {$baseName} is not readable");
            }
        } else {
            return null;
        }
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
