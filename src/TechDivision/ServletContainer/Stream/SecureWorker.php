<?php

/**
 * TechDivision\ServletContainer\Stream\SecureWorker
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Stream;

/**
 * The worker implementation that handles a HTTP request.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 */
class SecureWorker extends Worker
{

    /**
     * @see \TechDivision\ServletContainer\AbstractRequest::getHttpClientClass()
     */
    protected function getHttpClientClass()
    {
        return 'TechDivision\ServletContainer\Stream\SecureHttpClient';
    }
}