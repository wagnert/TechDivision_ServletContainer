<?php
/**
 * TechDivision\ServletContainer\Service\Locator\StaticResourceLocator
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Service
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
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
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Service
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
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
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet The servlet instance
     *
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
     * Return's the application itself.
     * 
     * @return \TechDivision\ServletContainer\Application The application itself
     */
    public function getApplication()
    {
        return $this->getServlet()->getServletConfig()->getApplication();
    }

    /**
     * Tries to locate the file specified in the passed request instance.
     *
     * @param Request $request The request instance
     *
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
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request instance
     *
     * @return string
     */
    public function getFilePath(Request $request)
    {
        
        // load the document root
        $documentRoot = $request->getServerVar('DOCUMENT_ROOT');
        
        // prepare and return the static file name to load
        $relativeFilePath = str_replace('/', DIRECTORY_SEPARATOR, parse_url($request->getUri(), PHP_URL_PATH));
        return $documentRoot . $relativeFilePath;
    }
}
