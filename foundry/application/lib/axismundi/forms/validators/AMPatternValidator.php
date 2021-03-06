<?php
/**
 *    AxisMundi
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
 *    @package forms
 *    @subpackage validators
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2009 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/
class AMPatternValidator extends AMValidator
{
	public $pattern;
	
	public function __construct($key, $required=AMValidator::kOptional, $pattern, $message=null)
	{
		$this->isRequired    =  $required;
		$this->shouldRequire =  $required ? false : true;
		$this->pattern       =  $pattern;
		$this->key           =  $key;
		$this->message       =  $message;
	}
	
	public function validate()
	{
		$value = $this->form->{$this->key};
		$this->updateRequiredFlag($value);
		
		/*$this->isValid = filter_var($value, 
									FILTER_VALIDATE_REGEXP, 
									array("options"=>array("regexp"=>$this->pattern)));*/
		
		$this->isValid = (preg_match($this->pattern, $value) === 0) ? false : true;
	}
}
?>