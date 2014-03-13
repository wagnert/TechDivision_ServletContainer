<?php

/**
 * TechDivision\ServletContainer\Interfaces\Response
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
 * @subpackage Interfaces
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * Interface for the servlet response.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface Response
{

    /**
     * Return's the content
     *
     * @return string
     */
    public function getContent();

    /**
     * Set's the content
     *
     * @param string $content The content string
     *
     * @return void
     */
    public function setContent($content);
}
