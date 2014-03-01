<?php

/**
 * TechDivision\ServletContainer\Service\Locator\PhpResourceLocator
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
 * @subpackage Service
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Service\Locator;

use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Exceptions\FileNotFoundException;
use TechDivision\ServletContainer\Exceptions\FileNotReadableException;
use TechDivision\ServletContainer\Exceptions\FoundDirInsteadOfFileException;

/**
 * The static resource locator implementation, e. g. to locate files like pictures.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Service
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class PhpResourceLocator extends StaticResourceLocator
{
    
    /**
     * Array with allowed index files.
     * 
     * @var array
     */
    protected $directoryIndex = array('index.php', 'index.phtml');
    
    /**
     * Returns array with the possible index files.
     *
     * @return array The array with the possible index files
     */
    protected function getDirectoryIndex()
    {
        return $this->directoryIndex;
    }

    /**
     * Tries to locate the file specified in the passed request instance.
     * 
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return \SplFileInfo The located file information
     * @throws \TechDivision\ServletContainer\Exceptions\FoundDirInsteadOfFileException Is thrown if the requested file is a directory
     * @throws \TechDivision\ServletContainer\Exceptions\FileNotFoundException Is thrown if the requested file has not been found or is not readable
     * @throws \TechDivision\ServletContainer\Exceptions\FileNotReadableException Is thrown if the requested file is not readable
     */
    public function locate(ServletRequest $servletRequest)
    {
        
        // load the request URI
        $uri = $servletRequest->getUri();
        
        // initialize the path information and the directory to start with
        $pathInfo = $uri;
        $directoryName = $uri;
        
        // initialize the webapp path (the document root)
        $documentRoot = $servletRequest->getServerVar('DOCUMENT_ROOT');
        
        // load the available directory index files
        $directoryIndex = $this->getDirectoryIndex();
        
        do { // descent the directory structure down to find a excecutable PHP file
                
            do { // iterate over the possible index files if a directory has been passed as URI
        
                try {

                    // initialize the path information with the directory name
                    $pathInfo = $directoryName;
                    
                    // check if an index file has been specified
                    if (isset($indexFile)) {
                        $pathInfo = rtrim($directoryName, '/') . DIRECTORY_SEPARATOR . $indexFile;
                    }
                    
                    // initialize the file information
                    $fileInfo = new \SplFileInfo($documentRoot . $pathInfo);
        
                    // check if we have a directory
                    if ($fileInfo->isDir()) {
                        throw new FoundDirInsteadOfFileException(sprintf("Requested file %s is a directory", $path));
                    }
        
                    // check if we have a real file (not a symlink for example)
                    if ($fileInfo->isFile() === false) {
                        throw new FileNotFoundException(sprintf('File %s not not found', $path));
                    }
                    
                    // check if the file is readable
                    if ($fileInfo->isReadable() === false) {
                        throw new FileNotReadableException(sprintf('File %s is not readable', $path));
                    }

                    // initialize the server variables
                    $servletRequest->setServerVar('PHP_SELF', $uri);
                    $servletRequest->setServerVar('SCRIPT_NAME', $pathInfo);
                    $servletRequest->setServerVar('SCRIPT_FILENAME', $fileInfo->getPathname());
                    
                    // set the script file information in the server variables
                    $servletRequest->setServerVar(
                        'PATH_INFO',
                        str_replace(
                            $servletRequest->getServerVar('SCRIPT_NAME'),
                            '',
                            $servletRequest->getServerVar('REQUEST_URI')
                        )
                    );
                    
                    // return the file information
                    return $fileInfo;

                } catch (\Exception $e) {
                    // do nothing, try with the next directory index file instead
                }
                
            } while (list($key, $indexFile) = each($directoryIndex)); // load the next directory index file

            // descendent  the directory tree
            $directoryName = dirname($directoryName);
            
            // reset the directory index files
            reset($directoryIndex);
            
        } while ($directoryName !== '/'); // stop until we reached the root of the URI
    }
}
