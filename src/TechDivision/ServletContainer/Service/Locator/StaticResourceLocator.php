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

use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Exceptions\FileNotFoundException;
use TechDivision\ServletContainer\Exceptions\FoundDirInsteadOfFileException;

/**
 * The static resource locator implementation, e.
 * g. to locate files like pictures.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Markus Stockbauer <ms@techdivision.com>
 */
class StaticResourceLocator extends AbstractResourceLocator
{

    /**
     * The servlet that called the locator.
     *
     * @var \TechDivision\ServletContainer\Interfaces\Servlet
     */
    protected $servlet;

    /**
     * Initializes the locator with the calling servlet.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet
     *            The servlet instance
     * @return \TechDivision\ServletContainer\Service\Locator\StaticResourceLocator
     */
    public function __construct(Servlet $servlet)
    {
        $this->servlet = $servlet;
        return $this;
    }

    /**
     * Returns the calling servlet instance.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Servlet $servlet The servlet instance
     */
    public function getServlet()
    {
        return $this->servlet;
    }

    /**
     * Tries to locate the file specified in the passed request instance.
     *
     * @param Request $request
     *            The request instance
     * @throws \TechDivision\ServletContainer\Exceptions\FoundDirInsteadOfFileException Is thrown if the requested file is a directory
     * @throws \TechDivision\ServletContainer\Exceptions\FileNotFoundException Is thrown if the requested file has not been found or is not readable
     * @throws \TechDivision\ServletContainer\Exceptions\FileNotReadableException Is thrown if the requested file is not readable
     * @return \SplFileObject The located file
     */
    public function locate(Request $request)
    {

        // build the path from url part and base path
        $path = $this->getFilePath($request);
        
        // load file information and return the file object if possible
        $fileInfo = new \SplFileInfo($path);
        if ($fileInfo->isDir()) {
            throw new FoundDirInsteadOfFileException(sprintf("Requested file %s is a directory", $path));
        }
        if ($fileInfo->isFile() === false) {
            throw new FileNotFoundException(sprintf('File %s not not found', $path));
        }
        if ($fileInfo->isReadable() === false) {
            throw new FileNotReadableException(sprintf('File %s is not readable', $path));
        }
        return $fileInfo->openFile();
    }

    /**
     * Returns the path to file without uri params
     *
     * @param Request $request
     * @return string
     */
    public function getFilePath(Request $request)
    {
        return $request->getServerVar('DOCUMENT_ROOT') . parse_url($request->getUri(), PHP_URL_PATH);
    }
}