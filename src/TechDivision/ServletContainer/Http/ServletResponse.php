<?php

/**
 * TechDivision\ServletContainer\Http\ServletResponse
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
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Http;

/**
 * A servlet request implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Http
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface ServletResponse
{

    /**
     * Prepares the headers for final processing
     *
     * @return void
     */
    public function prepareHeaders();

    /**
     * Prepares the content to be ready for sending to the client
     *
     * @return void
     */
    public function prepareContent();
}
