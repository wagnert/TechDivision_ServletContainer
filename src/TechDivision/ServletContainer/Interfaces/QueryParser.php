<?php
/**
 * TechDivision\ServletContainer\Interfaces\QueryParser
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * A query parser interface
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface QueryParser
{

    /**
     * Returns parsed result array
     *
     * @return array
     */
    public function getResult();

    /**
     * Parses the given queryStr and returns result array
     *
     * @param string $queryStr The query string
     *
     * @return array The parsed result as array
     */
    public function parseStr($queryStr);

    /**
     * Parses key value and returns result array
     *
     * @param string $param The param to be parsed
     * @param string $value The value to be set
     *
     * @return void
     */
    public function parseKeyValue($param, $value);
}
