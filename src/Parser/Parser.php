<?php
namespace Vtk13\LibXdebugTrace\Parser;

use Exception;
use Vtk13\LibXdebugTrace\FileUtil\File;
use Vtk13\LibXdebugTrace\Logger;
use Vtk13\LibXdebugTrace\Trace\Node;
use Vtk13\LibXdebugTrace\Trace\Trace;

class Parser
{
    /**
     * @var Entry
     */
    protected $current;

    /**
     * @var Trace
     */
    protected $trace;

    protected $log;

    static $cache;

    public function __construct()
    {
        $this->log = new Logger();
    }

    /**
     * @param File $traceFile
     * @return Trace
     * @throws \Exception
     */
    public function parse(File $traceFile)
    {
        if (empty(self::$cache[$traceFile->getFullName()])) {
            $this->current = $root = new Entry(0, 0, 0, 0, 0, 0, 0, 0, 0);

            $file = fopen($traceFile->getFullName(), 'r');
            fgetcsv($file, null, "\t"); // $version
            $format = fgetcsv($file, null, "\t");
            fgetcsv($file, null, "\t"); // $traceStart
            if ($format[0] != 'File format: ' . XDEBUG_TRACE_COMPUTERIZED) {
//                Version: 2.3.0dev write XDEBUG_TRACE_COMPUTERIZED with "File format: 4" o_0
//                throw new Exception('Invalid trace format #' . $format[0]);
            }
            while (($data = fgetcsv($file, null, "\t")) !== false) {
                if (isset($data[2])) {
                    switch ($data[2]) {
                        case '0':
                            $this->processEntry(new Entry($data[0], $data[1], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], array_slice($data, 11)));
                            break;
                        case '1':
                            $this->processEntry(new ExitEntry($data[0], $data[1], $data[3], $data[4]));
                            break;
                        case 'R':
                            $this->processEntry(new ReturnEntry($data[0], $data[1], $data[5]));
                            break;
                    }
                }
            }

            while ($this->current != $root) {
                $this->goOut(new ExitEntry($this->current->level, $this->current->callId, 0, 0));
            }
            fclose($file);

            self::$cache[$traceFile->getFullName()] = new Trace($this->createNodeFromEntry($root));
        }

        return self::$cache[$traceFile->getFullName()];
    }

    protected function createNodeFromEntry(Entry $entry, ExitEntry $exitEntry = null)
    {
        $node = new Node(
            $entry->level,
            $entry->callId,
            $entry->time,
            $exitEntry ? $exitEntry->time : 0,
            $entry->function,
            $entry->includeFile,
            $entry->file,
            $entry->line,
            $entry->parameters
        );
        $node->children = $entry->children;
        return $node;
    }

    protected function goInto(Entry $entry)
    {
        $this->current->children[$entry->callId] = $entry;
        $entry->parent = $this->current;
        $this->current = $entry;
    }

    protected function goOut(ExitEntry $entry)
    {
        if ($this->current->level == $entry->level) {
            // replace current trace Entry with Node
            $this->current->parent->children[$this->current->callId] = $this->createNodeFromEntry($this->current, $entry);
            $this->current = $this->current->parent;
        } else {
            $msg = "Invalid exit entry level #{$entry->level}, current entry level is #{$this->current->level}. Ignoring.";
            $this->log->warning($msg);
        }
    }

    public function processEntry(Entry $entry)
    {
        if ($entry instanceof ReturnEntry) {
            if (isset($this->current->children[$entry->callId])) {
                $this->current->children[$entry->callId]->returnValue = $entry->value;
            } else {
                $this->log->warning('Invalid return entry #' . $entry->callId);
            }
        } elseif ($entry instanceof ExitEntry) {
            $this->goOut($entry);
        } else {
            switch (true) {
                case $entry->level > $this->current->level + 1:
                    // actually this may be needed only for first entry
                    while ($entry->level > $this->current->level + 1) {
                        $this->goInto(new Entry($this->current->level + 1, 0, 0, 0, 0, 0, 0, 0, 0));
                    }
                    $this->goInto($entry);
                    break;
                case $entry->level == $this->current->level + 1:
                    $this->goInto($entry);
                    break;
                case $entry->level < $this->current->level + 1:
                    $msg = "Invalid entry level #{$entry->level}, current entry level is #{$this->current->level}. Ignoring.";
                    $this->log->warning($msg);
            }
        }
    }
}
