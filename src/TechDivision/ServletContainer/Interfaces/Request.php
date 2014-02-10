<?php
/**
 * TechDivision\ServletContainer\Interfaces\Request
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * Interface for the servlet request.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
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
     * @param string $content The raw request header
     *
     * @return void
     */
    public function parse($content);

    /**
     * validate actual InputStream
     *
     * @param string $buffer InputStream
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request
     */
    public function initFromRawHeader($buffer);
}
