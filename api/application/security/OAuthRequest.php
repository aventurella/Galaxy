<?php
/**
 *    Galaxy - API
 * 
 *    Copyright (C) 2010 Adam Venturella
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
 *    @package api
 *    @subpackage security
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2010 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/
class OAuthRequest
{
	public $oauth_consumer_key;
	public $oauth_nonce;
	public $oauth_signature_method;
	public $oauth_signature;
	public $oauth_timestamp;
	public $oauth_token;
	public $oauth_version;
	public $realm;
	
	public function __construct($signature)
	{
		$signature = substr($signature, strpos($signature, ' ')+1);
		$parts     = explode(',', $signature);
		array_map(array($this,'process_key_value_pair'), $parts);
	}
	
	// key="value"
	private function process_key_value_pair($string)
	{
		$string            = trim($string);
		list($key, $value) = explode('=', $string);
		$this->{$key}      = substr($value, 1, -1);
	}
}
?>