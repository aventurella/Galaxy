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
 *    @subpackage net
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2010 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/
class GalaxyAuthorizationOAuth
{
	private $application;
	private $description;
	private $domain;
	private $instance;
	private $oauth;
	
	public function __construct($signature)
	{
		$this->oauth = new OAuthRequest($signature);
	}
	
	public function isAuthorized()
	{
		$result = false;
		
		$db_certificates = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseRedis, GalaxyAPIConstants::kDatabaseCertificates);
		$certificate     = json_decode($db_certificates->get(GalaxyAPIConstants::kTypeCertificate.':'.$this->oauth->oauth_consumer_key), true);
		
		if($certificate)
		{
			$this->application                     = $certificate['application'];
			$this->instance                        = $certificate['instance'];
			$this->description                     = $certificate['description'];
			$this->domain                          = $certificate['domain'];
			
			$secret                                = $certificate['secret'];
			$base_string                           = array();
			$base_string['oauth_consumer_key']     = $this->oauth->oauth_consumer_key;
			$base_string['oauth_nonce']            = $this->oauth->oauth_nonce;
			$base_string['oauth_signature_method'] = $this->oauth->oauth_signature_method;
			$base_string['oauth_timestamp']        = $this->oauth->oauth_timestamp;
			$base_string['oauth_token']            = '';
			$base_string['oauth_version']          = $this->oauth->oauth_version;
			
			
			
			if(count($_REQUEST))
			{
				$base_string = array_merge($base_string, $_REQUEST);
				ksort($base_string);
			}
		
			
			$string    = strtoupper($_SERVER['REQUEST_METHOD'])."&http://".$_SERVER['SERVER_NAME'].'/'.GalaxyAPI::endpoint()."&".http_build_query($base_string);
			$string    = urlencode($string);
			
			$signature = base64_encode(hash_hmac('sha1', $string, $secret, true));
			

			$sig1  = base64_decode(urldecode($this->oauth->oauth_signature));
			$sig2  = base64_decode($signature);
			
			$result = rawurlencode($sig1) == rawurlencode($sig2);
		}
		
		return $result;
	}
	
	public function __get($key)
	{
		$value = null;
		switch($key)
		{
			case 'application':
			case 'instance':
			case 'description':
			case 'domain':
				$value = $this->{$key};
				break;
			
			default:
				
				if(isset($this->oauth->{$key}))
				{
					$value = $this->oauth->{$key};
				}
				break;
		}
		
		return $value;
	}
	
}
?>