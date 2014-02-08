<?php
/**
 * TechDivision\ServletContainer\Interfaces\HttpClientInterface
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * Interface for the Http clients that read's the data from the socket
 * and initialzes the Request instance.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface HttpClientInterface
{

    /**
     * Returns the HttpRequest factory instance.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request The request factory instance
     */
    public function getHttpRequest();
    
    /**
     * Returns the HttpPart factory instance.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Part The part as factory instance
     */
    public function getHttpPart();

    /**
     * Returns the Request instance initialized with request data read from the socket.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request The initialized Request instance
     */
    public function receive();
}
