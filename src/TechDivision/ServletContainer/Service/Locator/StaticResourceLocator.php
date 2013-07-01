<?php

/**
 * TechDivision\ServletContainer\Service\Locator\StaticResourceLocator
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Service\Locator;

use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Exceptions\FileNotFoundException;

/**
 * The static resource locator implementation, e. g. to locate files like pictures.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 */
class StaticResourceLocator extends AbstractResourceLocator {

    /**
     * @param ServletRequest $request
     * @throws \TechDivision\ServletContainer\Exceptions\FileNotFoundException
     * @throws \Exception Is thrown if the requested file has not been found or is not readable
     * @return \SplFileObject The located file
     */
    public function locate(ServletRequest $request) {

        // build the path from url part and base path
        $path = BP . DS . 'webapps' . urldecode($request->getRequestUrl());

        // make sure the requested file exists
        if (!file_exists($path)) {
            throw new FileNotFoundException(sprintf('404 - file %s does not exist.', $path));
        }

        $file = new \SplFileObject($path);

        if (!($file->isReadable() && $file->isFile())) {
            throw new \Exception(sprintf('File %s could not be opened.', $path));
        }

        return $file;
    }
}