<?php
/**
 *    Galaxy - Foundry
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
 *    @package foundry
 *    @subpackage forms
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2010 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/
require $_SERVER['DOCUMENT_ROOT'].'/application/forms/Form.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/forms/validators/PatternValidator.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/forms/validators/InversePatternValidator.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/forms/validators/EmailValidator.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/forms/validators/InputValidator.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/forms/validators/MatchValidator.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/forms/renegade/validators/UniqueEmailValidator.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/mail/MailEnvironment.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/mail/AuthorizeAccountMail.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/functions/SecurityFunctions.php';

$session = Renegade::session();
if(strlen($session->user) > 0)
{
	$kEmailUpdate    = 1;
	$kPasswordUpdate = 2;
	$context         = 0;
	$options         = array(Form::kDataKey => $_POST);
	$form            = Form::formWithContext($options);

	if(strlen($form->inputEmail) > 0)
	{
		$form->addValidator(new EmailValidator('inputEmail', true, 'Please provide a valid e-mail address'));
		$form->addValidator(new MatchValidator('inputEmail', 'inputEmailVerify', true, 'Email Addresses must match '));
		$form->addValidator(new UniqueEmailValidator('inputEmail', true, 'Email address is currently in use'));
		$context |= $kEmailUpdate;
	}

	if(strlen($form->inputPassword) > 0)
	{
		$form->addValidator(new MatchValidator('inputPassword', 'inputPasswordVerify', true, 'Password must match'));
		$form->addValidator(new InputValidator('inputPassword', true, 4, null, 'Password must be at least 4 characters long'));
		$form->addValidator(new InversePatternValidator('inputPassword', true, '/\s/', 'Password may not contain spaces'));
		$context |= $kPasswordUpdate;
	}

	if($context > 0)
	{
		$form->validate();
	
		if($form->isValid)
		{
			$user = Renegade::authorizedUser();
		
			if($context & $kEmailUpdate)
			{
				$user['email'] = $form->inputEmail;
			}
		
			if($context & $kPasswordUpdate)
			{
				$user['password'] = renegade_security_hash($form->inputPassword);
			}
		
			$db = Renegade::database();
			$db->put($user, $user['_id']);
		
			$response->ok = true;
		}
		else
		{
			$response->error      = 'invalid';
			$response->reason     = 'validators';
			$response->validators = array();
		
			foreach($form->validators as $validator)
			{
				if($validator->isValid == false)
				{
					$error                   = new stdClass();
					$error->key              = $validator->key;
					$error->reason           = $validator->message;
					$response->validators[] = $error;
				}
			}
		}
	
	}
	else
	{
		$response->error  = 'invalid';
		$response->reason = 'no_data';
	}

	echo json_encode($response);
}
else
{
	header('Location: /');
}

?>