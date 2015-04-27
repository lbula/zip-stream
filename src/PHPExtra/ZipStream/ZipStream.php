<?php

namespace PHPExtra\ZipStream;

use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\StreamInterface;
use Symfony\Component\Process\ProcessBuilder;
use Psr\Log\LoggerInterface;

/**
 * The ZipStream class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ZipStream implements StreamInterface
{
    /**
     * @var array
     */
    private $files;

    /**
     * @var array
     */
    private $pipes;

    /**
     * @var resource
     */
    private $process;

    /**
     * @var StreamInterface
     */
    private $stream = null;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @param array $files
     * @param LoggerInterface $logger
     */
    function __construct(array $files, LoggerInterface $logger)
    {
        $this->setLogger($logger);

        if (empty($files)) {
            $failedMessage = 'Empty input file list';
            $this->getLogger()->error(__METHOD__ . ': ' . $failedMessage);
            throw new \RuntimeException($failedMessage);
        }

        $this->files = $files;
    }

    /**
     * @return StreamInterface
     */
    protected function getStream()
    {
        if($this->stream === null){
            $descriptors = array(
                0 => array('pipe', 'r'),    // stdin
                1 => array('pipe', 'w'),    // stdout
                2 => array('pipe', 'a')     // stderr
            );

            $command = $this->createCommand();
            $this->process = proc_open($command, $descriptors, $this->pipes);

            if($this->process === false){
                $failedMessage = sprintf('Unable to create process: %s', $command);
                $this->getLogger()->error(__METHOD__ . ': ' . $failedMessage);
                throw new \RuntimeException($failedMessage);
            }

            $this->stream = Stream::factory($this->pipes[1]);
        }

        return $this->stream;
    }

    /**
     * @return string
     */
    protected function createCommand()
    {
        $absoluteFilenames = array();

        foreach($this->files as $file){
            $realFile = realpath($file);

            if($realFile === false){
                $failedMessage = sprintf('File does not exist: %s', $file);
                $this->getLogger()->error(__METHOD__ . ': ' . $failedMessage);
                throw new \RuntimeException($failedMessage);
            }
            $absoluteFilenames[] = $realFile;
        }

        $builder = new ProcessBuilder();
        $builder->setArguments($absoluteFilenames);
        $filesToZipList = $builder->getProcess()->getCommandLine();

        return sprintf('zip -0 -j -q -r - %s', $filesToZipList);
    }

    /**
     * Create new ZipStream
     *
     * @param array $files
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public static function create(array $files, LoggerInterface $logger)
    {
        return new self($files, $logger);
    }

    /**
     * @return void
     */
    public function close()
    {
        $this->getStream()->close();
        if($this->process){
            proc_close($this->process);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->getStream()->isSeekable();
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return $this->getStream()->isWritable();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getStream()->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return $this->getStream()->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function attach($stream)
    {
        $this->getStream()->attach($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->getStream()->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return $this->getStream()->tell();
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return $this->getStream()->eof();
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->getStream()->seek($offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        return $this->getStream()->write($string);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return $this->getStream()->isReadable();
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        return $this->getStream()->read($length);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return $this->getStream()->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        return $this->getStream()->getMetadata();
    }

    /**
     * @param LoggerInterface $logger
     */
    private function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger()
    {
        return $this->logger;
    }
}