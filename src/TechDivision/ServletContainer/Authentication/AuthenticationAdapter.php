<?php
/**
 * TechDivision\ServletContainer\ServletManager
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Authentication
 * @author     Florian Sydekum <fs@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Authentication;

use TechDivision\ServletContainer\Interfaces\Servlet;

/**
 * Abstract class for authentication adapters.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Authentication
 * @author     Florian Sydekum <fs@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
abstract class AuthenticationAdapter
{
    /**
     * @var array $options Necessary options for specific adapter.
     */
    protected $options;

    /**
     * @var \TechDivision\ServletContainer\Interfaces\Servlet $servlet Current servlet which needs authentication.
     */
    protected $servlet;

    /**
     * @var string $filename The filename of the htdigest file.
     */
    protected $filename;

    /**
     * Instantiates an authentication adapter
     *
     * @param array                                             $options Necessary options for specific adapter.
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet A servlet instance
     */
    public function __construct($options, Servlet $servlet)
    {
        $this->options = $options;
        $this->servlet = $servlet;

        $this->setFilename($options['file']);
    }

    /**
     * Initializes the adapter.
     *
     * @return void
     */
    abstract public function init();

    /**
     * Return's Servlet object
     *
     * @return Servlet
     */
    public function getServlet()
    {
        return $this->servlet;
    }

    /**
     * Set's htdigest Filename
     *
     * @param string $filename The filename
     *
     * @return void
     */
    protected function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Return's htdigest Filename
     *
     * @return string
     */
    protected function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set authentication options
     *
     * @param array $options The options
     *
     * @return void
     */
    protected function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Return's auhtenication options
     *
     * @return array
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Set's Servlet Object
     *
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet A servlet instance
     *
     * @return void
     */
    protected function setServlet(Servlet $servlet)
    {
        $this->servlet = $servlet;
    }
}
