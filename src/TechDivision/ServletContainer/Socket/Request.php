<?php

/**
 * TechDivision\ServletContainer\Socket\Request
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Socket;

use TechDivision\ServletContainer\AbstractRequest;

/**
 * The request implementation.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Tim Wagner <tw@techdivision.com>
 */
class Request extends AbstractRequest
{

    /**
     * @see \TechDivision\ServletContainer\AbstractRequest::getHttpClientClass()
     */
    protected function getHttpClientClass()
    {
        return 'TechDivision\ServletContainer\Socket\HttpClient';
    }
}