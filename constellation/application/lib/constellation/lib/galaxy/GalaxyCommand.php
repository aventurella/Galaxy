<?php
/**
 *    Galaxy - Core
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
 *    @package galaxy
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2010 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/
abstract class GalaxyCommand
{
	const kMethodGet    = 'GET';
	const kMethodPost   = 'POST';
	const kMethodPut    = 'PUT';
	const kMethodDelete = 'DELETE';
	
	public $method;
	public $endpoint;
	public $content;
	public $content_type;
	public $scheme = 'http://';
	
	public function setContentType($value)
	{
		$this->content_type = $value;
	}
	
	public function setContent($value)
	{
		$this->content = $value;
	}
}

/* Global Commands For Any Application */
class Channels extends GalaxyCommand
{
	public $method   = GalaxyCommand::kMethodGet;
	public $endpoint = 'channels';
}
?>