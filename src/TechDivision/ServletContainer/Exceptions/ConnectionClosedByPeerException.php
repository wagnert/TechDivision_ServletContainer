<?php
/**
 * TechDivision\ServletContainer\Exceptions\ConnectionClosedByPeerException
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Exceptions
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Exceptions;

/**
 * Is thrown if a connection has been closed by the client.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Exceptions
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class ConnectionClosedByPeerException extends \Exception
{
}
