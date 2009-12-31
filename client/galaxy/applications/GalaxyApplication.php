<?php
abstract class GalaxyApplication
{
	protected $key;
	protected $secret;
	protected $format;
	protected $id;
	
	abstract protected function initialize();
	
	public function __construct($options)
	{
		$this->key    = $options['key'];
		$this->secret = $options['secret'];
		$this->format = $options['format'];
		$this->id     = $options['id'];
		
		$this->initialize();
	}
	
	public function channels()
	{
		$options    = $this->defaultCommandOptions();
		$connection = GalaxyConnection::initWithCommandAndOptions(new Channels(), $options);
		$response = $connection->start();
		return $response->result;
	}
	
	private function defaultCommandOptions()
	{
		// right now we are just using OAuth
		// at some point, we may consider OAuth WRAP
		// right now we just want to git-r-done and I don't
		// feel like reading a new specificaion and implementing it ;)
		return array('format'        => $this->format,
		             'id'            => $this->id,
		             'authorization' => array('type'   => Galaxy::kAuthorizationOAuth,
			                                  'key'    => $this->key,
		                                      'secret' => $this->secret));
	}
}
?>