<?php

/**
 * TechDivision\ServletContainer\Http\Part
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\Part;

/**
 * A http part implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 * 
 */
class HttpPart implements Part
{
    /**
     * Defines header name constances
     * 
     * @var unknown
     */
    const HEADER_NAME_CONTENT_TYPE = 'Content-Type';
	
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
	protected $size;

	/**
	 * Initiates a http form part object
	 * 
	 * @param string $streamWrapper The stream wrapper to use per default temp stream wrapper
	 * @param long $maxMemory MaxMemory in bytes per default to 5 MB.
	 * @throws \Exception
	 * @return void
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
	 * @param string $streamWrapper
	 * @param long $maxMemory
	 */
	public function getInstance($streamWrapper = self::STREAM_WRAPPER_TEMP, $maxMemory = 5242880)
	{
	    return new self($streamWrapper, $maxMemory);
	}
	
	/**
	 * Puts content to input stream.
	 * 
	 * @param string $content
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
		return $this->getHeader(self::HEADER_NAME_CONTENT_TYPE);
	}
	
	/**
	 * Sets the orig form filename
	 * 
	 * @param string $filename
	 */
	public function setFilename($filename)
	{
	    $this->filename = $filename;
	}
	
	/**
	 * Gets the orig firm filename
	 * 
	 * @return string
	 */
	public function getFilename()
	{
	    return $this->filename;
	}
	
	/**
	 * Sets the name of the part
	 * 
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
	
	/**
	 * Adds header information to the part
	 * 
	 * @param string $name
	 * @param string $value
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
	 * @return void
	*/
	public function write($fileName)
	{
		return file_put_contents($fileName, $this->getInputStream());
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
	 * @return array
	*/
	public function getHeaders($name = NULL)
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