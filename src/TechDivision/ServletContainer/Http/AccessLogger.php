<?php

/**
 * TechDivision\ServletContainer\Http\AccessLogger
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
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;

/**
 * A http access logger to log apache compatible access log types
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Http
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class AccessLogger
{

    /**
     * Path to log file
     *
     * @var string
     */
    const LOG_FILEPATH = 'var/log/appserver-access.log';

    /**
     * Log format like apache default combined type
     *
     * @var string
     */
    const LOG_FORMAT_COMBINED = '%s - - [%s] "%s %s %s" %s %s "%s" "%s"';

    /**
     * Log format like apache default common type
     *
     * @var string
     */
    const LOG_FORMAT_COMMON = '%s - - [%s] "%s %s %s" %s %s';

    /**
     * Defines log type common
     *
     * @var string
     */
    const LOG_TYPE_COMMON = 'common';

    /**
     * Defines log type combined
     *
     * @var string
     */
    const LOG_TYPE_COMBINED = 'combined';

    /**
     * Writes to access log file in given type format
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $request  A request object
     * @param \TechDivision\ServletContainer\Interfaces\Response $response A response object
     * @param string                                             $type     The log type
     *
     * @return void
     */
    public function log(Request $request, Response $response, $type = self::LOG_TYPE_COMBINED)
    {
        // init datetime instance with current time and timezone
        $datetime = new \DateTime('now');

        if ($type == self::LOG_TYPE_COMBINED) {
            /**
             * This logs in apaches default combined format
             * LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
             */
            error_log(
                sprintf(
                    self::LOG_FORMAT_COMBINED . PHP_EOL,
                    $request->getClientIp(),
                    $datetime->format('d/M/Y:H:i:s O'),
                    $request->getMethod(),
                    $request->getUri(),
                    $request->getVersion(),
                    $response->getCode(),
                    $response->getHeader(Header::HEADER_NAME_CONTENT_LENGTH),
                    $request->getHeader(Header::HEADER_NAME_REFERER) ? $request->getHeader(Header::HEADER_NAME_REFERER) : '-',
                    $request->getHeader(Header::HEADER_NAME_USER_AGENT) ? $request->getHeader(Header::HEADER_NAME_USER_AGENT) : '-'
                ),
                3,
                self::LOG_FILEPATH
            );
        }

        if ($type == self::LOG_TYPE_COMMON) {
            /**
             * This logs in apaches default common format
             * LogFormat "%h %l %u %t \"%r\" %>s %b" common
             */
            error_log(
                sprintf(
                    self::LOG_FORMAT_COMMON . PHP_EOL,
                    $request->getClientIp(),
                    $datetime->format('d/M/Y:H:i:s O'),
                    $request->getMethod(),
                    $request->getUri(),
                    $request->getVersion(),
                    $response->getCode(),
                    $response->getHeader(Header::HEADER_NAME_CONTENT_LENGTH)
                ),
                3,
                self::LOG_FILEPATH
            );
        }
    }
}
