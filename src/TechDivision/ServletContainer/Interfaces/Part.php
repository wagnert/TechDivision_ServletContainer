<?php

/**
 * TechDivision\ServletContainer\Interfaces\Part
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * A part interface.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */
interface Part
{
	
	/**
	 * Defines the stream wrapper
	 * 
	 * @var string
	 */
	CONST STREAM_WRAPPER_TEMP = 'php://temp';
	CONST STREAM_WRAPPER_MEMORY = 'php://memory';
	
	/**
	 * Gets the content of this part as an InputStream
	 * 
	 * @return \SplFileObject The content of this part as an InputStream
	 */
	public function getInputStream();
	
	/**
	 * Gets the content type of this part.
	 * 
	 * @return string The content type of this part.
	 */
	public function getContentType();
	
	/**
	 * Gets the name of this part
	 * 
	 * @return string The name of this part as a String
	 */
	public function getName();
	
	/**
	 * Returns the size of this file.
	 * 
	 * @return int The size of this part, in bytes.
	 */
	public function getSize();
	
	/**
	 * A convenience method to write this uploaded item to disk.
	 * 
	 * @param string $fileName The name of the file to which the stream will be written.
	 * @return void
	 */
	public function write($fileName);
	
	/**
	 * Deletes the underlying storage for a file item, including deleting any associated temporary disk file.
	 * 
	 * @return void
	 */
	public function delete();
	
	/**
	 * Returns the value of the specified mime header as a String.
	 * If the Part did not include a header of the specified name, this method returns null.
	 * If there are multiple headers with the same name, this method returns the first header in the part.
	 * The header name is case insensitive. You can use this method with any request header.
	 * 
	 * @param string $name a String specifying the header name
	 */
	public function getHeader($name);
	
	/**
	 * Gets the values of the Part header with the given name.
	 * 
	 * @param string $name the header name whose values to return
	 * @return array
	 */
	public function getHeaders($name = NULL);
	
	/**
	 * Gets the header names of this Part.
	 * 
	 * @return array
	 */
	public function getHeaderNames();
	
}