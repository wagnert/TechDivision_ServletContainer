<?php

/**
 * TechDivision\ServletContainer\Socket\Worker
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
 * @subpackage Socket
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Socket;

use TechDivision\ServletContainer\AbstractHttpWorker;

/**
 * The worker implementation that handles a HTTP request.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Socket
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class Worker extends AbstractHttpWorker
{

    /**
     * Returns the resource class used to receive data over the socket.
     *
     * @return string
     */
    protected function getResourceClass()
    {
        return 'TechDivision\Socket';
    }

    /**
     * Return's the http client to use for
     *
     * @return string
     */
    protected function getHttpClientClass()
    {
        return 'TechDivision\ServletContainer\Socket\HttpClient';
    }
}
