<?php

/**
 * TechDivision\ServletContainer\Modules\DirectoryModule
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
 * @subpackage Modules
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Modules;

use TechDivision\ApplicationServer\Interfaces\ContainerInterface;
use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Exceptions\FileNotFoundException;
use TechDivision\ServletContainer\Exceptions\FileNotReadableException;
use TechDivision\ServletContainer\Exceptions\FoundDirInsteadOfFileException;

/**
 * The module that handles directory configuration.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Modules
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 * @link       http://httpd.apache.org/docs/2.4/mod/mod_dir.html
 */
class DirectoryModule extends AbstractModule
{
    
    /**
     * Array with allowed index files.
     * 
     * @var array
     */
    protected $directoryIndex = array('index.html', 'index.php');
    
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
     * Initializes the module.
     * 
     * @return void
     * @see \TechDivision\ServletContainer\Modules\Module::init()
     */
    public function init()
    {
    }
    
    /**
     * Handles the passed request .
     * 
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client   The http client
     * @param \TechDivision\ServletContainer\Interfaces\Request             $request  The request to be handled
     * @param \TechDivision\ServletContainer\Interfaces\Response            $response The response instance
     * 
     * @return void
     * @see \TechDivision\ServletContainer\Modules\Module::handle()
     */
    public function handle(HttpClientInterface $client, Request $request, Response $response)
    {
        
        // load the request URI
        $uri = $request->getUri();
        
        // initialize the webapp path (the document root)
        $documentRoot = $request->getServerVar('DOCUMENT_ROOT');
        
        // create a file info object to check if a directory is requested
        $fileInfo = new \SplFileInfo($documentRoot . $uri);
        
        // check if a directory/webapp was been called without ending slash
        if ($fileInfo->isDir() && strrpos($uri, '/') !== strlen($uri) - 1) {
            
            // redirect to path with ending slash
            $response->addHeader(Header::HEADER_NAME_LOCATION, $uri . '/');
            $response->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 301 OK');
            $response->setContent(PHP_EOL);
            
            $request->setDispatched();
            
            return;
        }
        
        // initialize the path information and the directory to start with
        $pathInfo = $uri;
        
        // load the available directory index files
        $directoryIndex = $this->getDirectoryIndex();
        
        reset($directoryIndex);
                
        do { // iterate over the possible index files if a directory has been passed as URI
    
            try {
                
                // check if an index file has been specified
                if (isset($indexFile)) {
                    $pathInfo = rtrim($uri, '/') . DIRECTORY_SEPARATOR . $indexFile;
                }
                
                // initialize the file information
                $fileInfo = new \SplFileInfo($documentRoot . $pathInfo);
    
                // check if we have a directory
                if ($fileInfo->isDir()) {
                    throw new FoundDirInsteadOfFileException(sprintf("Requested file %s is a directory", $fileInfo));
                }
    
                // check if we have a real file (not a symlink for example)
                if ($fileInfo->isFile() === false) {
                    throw new FileNotFoundException(sprintf('File %s not not found', $fileInfo));
                }
                
                // check if the file is readable
                if ($fileInfo->isReadable() === false) {
                    throw new FileNotReadableException(sprintf('File %s is not readable', $fileInfo));
                }

                // initialize the server variables
                $request->setUri($pathInfo);
                
                return;

            } catch (\Exception $e) {
                // do nothing, try with the next directory index file instead
            }
            
        } while (list($key, $indexFile) = each($directoryIndex)); // load the next directory index file
    }
}
