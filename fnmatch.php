<?php

/**
 * TechDivision\ServletContainer\fnmatch
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Markus Stockbauer <ms@techdivision.com>
 * @author    Tim Wagner <tw@techdivision.com>
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

$urls = array(
    '/index.do',
    '/index.do/index',
    '/index.do/login'
);

$servlets = array(
    '/index.do*' => '\TechDivision\ServletContainer\Servlets\IndexServlet',
    '/index.do' => '\TechDivision\ServletContainer\Servlets\IndexServlet',
    '/*' => '\TechDivision\ServletContainer\Servlets\IndexServlet',
    '/' => '\TechDivision\ServletContainer\Servlets\IndexServlet'
);

foreach ($urls as $url) {
    foreach ($servlets as $urlPattern => $className) {
        if (fnmatch($urlPattern, $url)) {
            echo "SUCCESS: $url:$urlPattern => $className\n";
            continue (2);
        }
    }
}
