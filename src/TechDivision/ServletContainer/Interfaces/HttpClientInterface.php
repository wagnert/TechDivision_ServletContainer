<?php

/**
 * TechDivision\ServletContainer\Interfaces\HttpClientInterface
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * Interface for the Http clients that read's the data from the socket
 * and initialzes the Request instance.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
interface HttpClientInterface {

    /**
     * Returns the HttpRequest factory instance.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request The request factory instance
     */
    public function getHttpRequest();

    /**
     * Returns the Request instance initialized with request data
     * read from the socket.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request The initialized Request instance
     */
    public function receive();
}