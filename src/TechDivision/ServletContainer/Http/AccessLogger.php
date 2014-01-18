<?php

/**
 * TechDivision\ServletContainer\Http\AccessLogger
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;

/**
 * A http access logger to log apache compatible access log types
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
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
                sprintf(self::LOG_FORMAT_COMBINED . PHP_EOL,
                    $request->getClientIp(),
                    $datetime->format('d/M/Y:H:i:s O'),
                    $request->getMethod(),
                    $request->getUri(),
                    $request->getVersion(),
                    $response->getCode(),
                    $response->getHeader('Content-Length'),
                    $request->getHeader('Referer') ? $request->getHeader('Referer') : '-',
                    $request->getHeader('User-Agent') ? $request->getHeader('User-Agent') : '-'
                ), 3, self::LOG_FILEPATH
            );
        }

        if ($type == self::LOG_TYPE_COMMON) {
            /**
             * This logs in apaches default common format
             * LogFormat "%h %l %u %t \"%r\" %>s %b" common
             */
            error_log(
                sprintf(self::LOG_FORMAT_COMMON . PHP_EOL,
                    $request->getClientIp(),
                    $datetime->format('d/M/Y:H:i:s O'),
                    $request->getMethod(),
                    $request->getUri(),
                    $request->getVersion(),
                    $response->getCode(),
                    $response->getHeader('Content-Length')
                ), 3, self::LOG_FILEPATH
            );
        }
    }
}