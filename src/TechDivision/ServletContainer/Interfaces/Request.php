<?php

/**
 * TechDivision\ServletContainer\Interfaces\Request
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Interfaces;

/**
 * Interface for the servlet request.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Markus Stockbauer <ms@techdivision.com>
 *         Johann Zelger <jz@techdivision.com>
 */
interface Request
{

    /**
     * POST request method string.
     * 
     * @var string
     */
    const POST = 'POST';

    /**
     * GET request method string.
     * 
     * @var string
     */
    const GET = 'GET';

    /**
     * HEAD request method string.
     * 
     * @var string
     */
    const HEAD = 'HEAD';

    /**
     * PUT request method string.
     * 
     * @var string
     */
    const PUT = 'PUT';

    /**
     * DELETE request method string.
     * 
     * @var string
     */
    const DELETE = 'DELETE';

    /**
     * OPTIONS request method string.
     * 
     * @var string
     */
    const OPTIONS = 'OPTIONS';

    /**
     * TRACE request method string.
     * 
     * @var string
     */
    const TRACE = 'TRACE';

    /**
     * CONNECT request method string.
     * 
     * @var string
     */
    const CONNECT = 'CONNECT';

    /**
     * Parse request content
     *
     * @param string $content            
     * @return void
     */
    public function parse($content);

    /**
     * validate actual InputStream
     *
     * @param string $buffer
     *            InputStream
     * @return void
     */
    public function initFromRawHeader($buffer);
}
