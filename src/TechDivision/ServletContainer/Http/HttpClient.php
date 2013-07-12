<?php

/**
 * TechDivision\ServletContainer\Server
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer;

use TechDivision\Socket\Client;
use TechDivision\ServletContainer\Http\Request;


/**
 * A http client
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <pd@techdivision.com>
 */
class HttpClient extends Client {

    /**
     *
     * @param string $address
     * @param int $port
     * @see Client
     */
    public function __construct($address = '0.0.0.0', $port = 0){
        parent::__construct($address,$port);
    }

    /**
     * Receive a Stream from Socket an check it is valid
     * @todo implement a isComplete Method to evaluate if a HTTP-Stream is Bigger than LineLenght (eg. file-upload)
     * @return mixed
     */

    public function receive(){
        $buffer = '';
        while ($buffer .= $this->read($this->getLineLength())) {
            $req = Request::parse($buffer);
            if($req->isValid()){
                return $req;
            }
        }
    }

    /**
     * @deprec receive method will do all logic
     * @return mixed|string
     */
    public function readLine() {
        return $this->receive();
    }



}