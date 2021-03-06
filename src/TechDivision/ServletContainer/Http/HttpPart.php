<?php

/**
 * TechDivision\ServletContainer\Http\Part
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Http
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\Part;
use TechDivision\ServletContainer\Http\Header;

/**
 * A http part implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Http
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class HttpPart implements Part
{

    /**
     * Holds input stream file pointer
     *
     * @var resource a file pointer resource on success, or false on error.
     */
    protected $inputStream;

    /**
     * The name of the part
     *
     * @var string
     */
    protected $name;

    /**
     * Hold the orig filename given in multipart header
     *
     * @var string
     */
    protected $filename;

    /**
     * Holds the header information as array
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Holds  the number of bytes written to inputStream
     *
     * @var int
     */
    protected $size = 0;

    /**
     * Initiates a http form part object
     *
     * @param string  $streamWrapper The stream wrapper to use per default temp stream wrapper
     * @param integer $maxMemory     MaxMemory in bytes per default to 5 MB.
     *
     * @throws \Exception
     */
    public function __construct($streamWrapper = self::STREAM_WRAPPER_TEMP, $maxMemory = 5242880)
    {
        // init inputStream
        if (!$this->inputStream = fopen($streamWrapper . '/maxmemory:' . $maxMemory, 'r+')) {
            throw new \Exception();
        }
    }

    /**
     * Factory method to get a new instance of self
     *
     * @param string $streamWrapper The stream wrapper to init
     * @param int    $maxMemory     The memory limit for upload
     *
     * @return mixed
     */
    public function getInstance($streamWrapper = self::STREAM_WRAPPER_TEMP, $maxMemory = 5242880)
    {
        return new self($streamWrapper, $maxMemory);
    }

    /**
     * Puts content to input stream.
     *
     * @param string $content The content as string
     *
     * @return void
     */
    public function putContent($content)
    {
        // write to io stream
        $this->size = fwrite($this->inputStream, $content);
        // rewind file pointer
        rewind($this->inputStream);
    }

    /**
     * Gets the content of this part as an InputStream
     *
     * @return resource The content of this part as an InputStream
     */
    public function getInputStream()
    {
        return $this->inputStream;
    }

    /**
     * Gets the content type of this part.
     *
     * @return string The content type of this part.
    */
    public function getContentType()
    {
        return $this->getHeader(Header::HEADER_NAME_CONTENT_TYPE);
    }

    /**
     * Sets the orig form filename
     *
     * @param string $filename The file's name
     *
     * @return void
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Gets the orig firm filename
     *
     * @return string The file's name
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Sets the name of the part
     *
     * @param string $name The part's name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Adds header information to the part
     *
     * @param string $name  The header name
     * @param string $value The header value for given name
     *
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Gets the name of this part
     *
     * @return string The name of this part as a String
    */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the size of this file.
     *
     * @return int The size of this part, in bytes.
    */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * A convenience method to write this uploaded item to disk.
     *
     * @param string $fileName The name of the file to which the stream will be written.
     *
     * @return void
    */
    public function write($fileName)
    {
        return file_put_contents(
            $fileName,
            $this->getInputStream()
        );
    }

    /**
     * Deletes the underlying storage for a file item, including deleting any associated temporary disk file.
     *
     * @return void
    */
    public function delete()
    {
        fclose($this->inputStream);
    }

    /**
     * Returns the value of the specified mime header as a String.
     * If the Part did not include a header of the specified name, this method returns null.
     * If there are multiple headers with the same name, this method returns the first header in the part.
     * The header name is case insensitive. You can use this method with any request header.
     *
     * @param string $name a String specifying the header name
     *
     * @return string The headers value for given name
     */
    public function getHeader($name)
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }
    }

    /**
     * Gets the values of the Part header with the given name.
     *
     * @param string $name the header name whose values to return
     *
     * @return array
    */
    public function getHeaders($name = null)
    {
        if (is_null($name)) {
            return $this->headers;
        } else {
            return $this->getHeader($name);
        }
    }

    /**
     * Gets the header names of this Part.
     *
     * @return array
    */
    public function getHeaderNames()
    {
        return array_keys($this->headers);
    }
}
