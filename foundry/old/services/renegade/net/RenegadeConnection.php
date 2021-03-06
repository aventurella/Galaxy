<?php
/**
 *    Project Renegade
 * 
 *    Copyright (C) 2009 Adam Venturella
 *
 *    LICENSE:
 *
 *    Licensed under the Apache License, Version 2.0 (the "License"); you may not
 *    use this file except in compliance with the License.  You may obtain a copy
 *    of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 *    This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
 *    without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
 *    PURPOSE. See the License for the specific language governing permissions and
 *    limitations under the License.
 *
 *    Author: Adam Venturella - aventurella@gmail.com
 *
 *    @package Renegade
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2009 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/

/**
 * Responsble for handling the connections to the CouchDB Server
 * and returning the CouchDBResponse
 *
 * @package Net
 * @author Adam Venturella
 */
class RenegadeConnection
{
	const kApiLocation              = '/api';
	const kHost                     = 'renegade';
	
	private $_database;
	private $_host;
	private $_port;
	private $_transport;
	private $_timeout;
	
	/**
	 * undocumented function
	 *
	 * @param array $options 
	 * @author Adam Venturella
	 */
	public function __construct($options=null)
	{
		$this->_database  = isset($options['database'])  ? $options['database']  : null;
		$this->_host      = isset($options['host'])      ? $options['host']      : 'renegade';
		$this->_port      = isset($options['port'])      ? $options['port']      : '80';
		$this->_timeout   = isset($options['timeout'])   ? $options['timeout']   : 10;
		$this->_transport = isset($options['transport']) ? $options['transport'] : 'tcp://';
	}
	
	/**
	 * undocumented function
	 *
	 * @param RenegadeCommand $command 
	 * @return RenegadeResponse
	 * @author Adam Venturella
	 */
	public function execute(RenegadeCommand $command, $key, $secret)
	{
		$auth = RenegadeAuthorization::authorizationForCommandWithKeyAndSecret($command, $key, $secret);
		$command->setAuthorization($auth);
		//echo $command->request();exit;
		$data = $this->connect($command->request());
		$response = RenegadeResponse::responseWithData($data);
		
		if($response->error)
		{
			throw new Exception('RenegadeCommand('.$command.') Failed with error '.$response->error['error'].': '.$response->error['reason']);
		}
		else
		{
			return $response;
		}
	}
	
	/**
	 * undocumented function
	 *
	 * @param string $request 
	 * @return void
	 * @author Adam Venturella
	 */
	private function connect($request)
	{
		$errno    = null;
		$errstr   = null;
		$response = null;

		$socket = $this->_transport.$this->_host.':'.$this->_port;

		$stream = stream_socket_client($socket, $errno, $errstr, $this->_timeout);

		if(!$stream)
		{
			throw new Exception('Renegade unable to connect to host '.$socket.' : '.$errno.', '.$errstr);
			return;
		}
		else
		{
			fwrite($stream, $request);
			$response = stream_get_contents($stream);
			fclose($stream);
			return $response;
		}

		fclose($stream);
	}
}
?>