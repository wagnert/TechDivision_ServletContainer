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
    '/index.php',
    '/test/index.php',
    '/magento-1.8.1.0/index.php/testcategory.html',
    '/magento-1.8.1.0/skin/frontend/default/default/css/print.css',
    '/magento-1.8.1.0/js/prototype/validation.js',
    '/magento-1.8.1.0/js/lib/ccard.js',
    '/magento-1.8.1.0/js/prototype/prototype.js',
    '/magento-1.8.1.0/js/scriptaculous/builder.js',
    '/magento-1.8.1.0/skin/frontend/base/default/css/widgets.css',
    '/magento-1.8.1.0/skin/frontend/default/default/css/styles.css',
    '/magento-1.8.1.0/js/scriptaculous/effects.js',
    '/magento-1.8.1.0/js/scriptaculous/dragdrop.js',
    '/magento-1.8.1.0/js/scriptaculous/slider.js',
    '/magento-1.8.1.0/js/scriptaculous/controls.js',
    '/magento-1.8.1.0/js/varien/form.js',
    '/magento-1.8.1.0/js/varien/js.js',
    '/magento-1.8.1.0/js/varien/menu.js',
    '/magento-1.8.1.0/js/mage/cookies.js',
    '/magento-1.8.1.0/js/mage/translate.js',
    '/magento-1.8.1.0/skin/frontend/default/default/images/logo.gif',
    '/magento-1.8.1.0/media/catalog/product/cache/1/small_image/135x/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg',
    '/magento-1.8.1.0/skin/frontend/default/default/images/i_asc_arrow.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/media/col_left_callout.jpg',
    '/magento-1.8.1.0/skin/frontend/default/default/images/media/col_right_callout.jpg',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_header.jpg',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_form-search.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_body.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/btn_search.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_nav0.jpg',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_main1.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_main2.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_toolbar.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_grid.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_pipe1.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/i_block-subscribe.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_block-title.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/i_block-cart.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/i_block-list.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/i_block-poll.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_block-actions.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/images/bkg_pipe2.gif',
    '/magento-1.8.1.0/media/images/bkg_pipe2.gif',
    '/magento-1.8.1.0/skin/frontend/default/default/favicon.ico',
    '/example/components/require.css'
);

$servlets = array(
    '/js/*/*.js' => '\TechDivision\ServletContainer\Servlets\StaticResourceServlet',
    '/js/*/*.js' => '\TechDivision\ServletContainer\Servlets\StaticResourceServlet',
    '/media/*/*.(gif|png|jpg|svg)' => '\TechDivision\ServletContainer\Servlets\StaticResourceServlet', // doesn't work
    '/index.php/*' => '\TechDivision\ServletContainer\Servlets\Legacy\MagentoServlet',
    '*.(js|css|gif|png|jpg|jpeg)' => '\TechDivision\ServletContainer\Servlets\StaticResourceServlet', // doesn't work
    '*.jpg' => '\TechDivision\ServletContainer\Servlets\StaticResourceServlet',
    '*.png' => '\TechDivision\ServletContainer\Servlets\StaticResourceServlet',
    '*.gif' => '\TechDivision\ServletContainer\Servlets\StaticResourceServlet',
    '*.php' => '\TechDivision\ServletContainer\Servlets\PhpServlet'
);

foreach ($urls as $url) {
    foreach ($servlets as $urlPattern => $className) {
        if (fnmatch($urlPattern, $url)) {
            echo "SUCCESS: $url:$urlPattern => $className\n";
        }
    }
}
