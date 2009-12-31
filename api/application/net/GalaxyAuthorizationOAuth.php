<?php
class GalaxyAuthorizationOAuth
{
	private  $application;
	private  $instance;
	private  $oauth;
	
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
			$secret                                = $certificate['secret'];
			$base_string                           = array();
			$base_string['oauth_consumer_key']     = $this->oauth->oauth_consumer_key;
			$base_string['oauth_nonce']            = $this->oauth->oauth_nonce;
			$base_string['oauth_signature_method'] = $this->oauth->oauth_signature_method;
			$base_string['oauth_timestamp']        = $this->oauth->oauth_timestamp;
			$base_string['oauth_token']            = '';
			$base_string['oauth_version']          = $this->oauth->oauth_version;
			
			$string    = strtoupper($_SERVER['REQUEST_METHOD'])."&http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&".http_build_query($base_string);
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
		if($key == 'application' || $key == 'instance')
		{
			$value = $this->{$key};
		}
		else
		{
			if(isset($this->oauth->{$key}))
			{
				$value = $this->oauth->{$key};
			}
		}
		return $value;
	}
	
}
?>