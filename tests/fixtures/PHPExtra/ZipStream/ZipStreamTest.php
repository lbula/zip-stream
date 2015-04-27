<?php

/**
 * The ZipStreamTest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ZipStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function testFilesProvider()
    {
        return array(
            array(include __DIR__ . '/../../../testfiles/testfiles.php')
        );
    }

    public function getLoggerMock()
    {
        return $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCreateZipStreamInstance()
    {
        /** @var Psr\Log\LoggerInterface $logger */
        $logger = $this->getLoggerMock();
        new \PHPExtra\ZipStream\ZipStream(array(), $logger);
    }

    /**
     * @dataProvider testFilesProvider
     *
     * @param array $files
     */
    public function testCreateNewZipStreamFromTestFiles(array $files)
    {
        /** @var Psr\Log\LoggerInterface $logger */
        $logger = $this->getLoggerMock();
        $stream = new \PHPExtra\ZipStream\ZipStream($files, $logger);
        $this->assertEquals(517, strlen($stream->getContents()), 'Stream has wrong length');
    }

    /**
     * @dataProvider testFilesProvider
     *
     * @param array $files
     */
    public function testCreateNewZipStreamFromTestFilesIncremental(array $files)
    {
        /** @var Psr\Log\LoggerInterface $logger */
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $stream = new \PHPExtra\ZipStream\ZipStream($files, $logger);
        $tmp = '';
        while (!$stream->eof()){
            $tmp .= $stream->read(100);
        }
        $stream->close();
        $this->assertEquals(517, strlen($tmp), 'Stream has wrong length');
    }

    /**
     * @dataProvider testFilesProvider
     *
     * @param array $files
     */
    public function testCreateZipStreamInstanceUsingFactoryMethod(array $files)
    {
        /** @var Psr\Log\LoggerInterface $logger */
        $logger = $this->getLoggerMock();
        $stream = \PHPExtra\ZipStream\ZipStream::create($files, $logger);
        $this->assertInstanceOf('\PHPExtra\ZipStream\ZipStream', $stream);
    }

    /**
     * @dataProvider testFilesProvider
     *
     * @param array $files
     */
    public function testZipStreamIsNotSeekable(array $files)
    {
        /** @var Psr\Log\LoggerInterface $logger */
        $logger = $this->getLoggerMock();
        $stream = \PHPExtra\ZipStream\ZipStream::create($files, $logger);
        $this->assertFalse($stream->isSeekable());
    }

    /**
     * @dataProvider testFilesProvider
     *
     * @param array $files
     */
    public function testZipStreamIsNotWritable(array $files)
    {
        /** @var Psr\Log\LoggerInterface $logger */
        $logger = $this->getLoggerMock();
        $stream = \PHPExtra\ZipStream\ZipStream::create($files, $logger);
        $this->assertFalse($stream->isWritable());
    }
}
