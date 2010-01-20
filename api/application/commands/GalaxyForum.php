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
 *    @subpackage commands
 *    @author Adam Venturella <aventurella@gmail.com>
 *    @copyright Copyright (C) 2010 Adam Venturella
 *    @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 **/
require 'GalaxyApplication.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/models/forum/GalaxyForumMessage.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/models/forum/GalaxyForumTopic.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/lib/axismundi/forms/AMForm.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/lib/axismundi/forms/validators/AMInputValidator.php';

class GalaxyForum extends GalaxyApplication
{
	public function messages_put($context)
	{

		// anything incoming as a PUT will be coming in as JSON
		$data = json_decode(file_get_contents('php://input'), true);
		
		$form_context = array(AMForm::kDataKey => $data);
		$form = AMForm::formWithContext($form_context);
		$form->addValidator(new AMInputValidator('author_name', AMValidator::kRequired, 1, null, 'author name required'));
		$form->addValidator(new AMInputValidator('title', AMValidator::kRequired, 1, null, 'title required'));
		$form->addValidator(new AMInputValidator('body', AMValidator::kRequired, 1, null, 'body required'));
		
		if($form->isValid)
		{
		
			$options = array('default' => GalaxyAPI::databaseForId($context->application));
			$channel = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);

			$message = GalaxyForumMessage::messageWithContext($context);
			$message->setTitle($data['title']);
			$message->setBody($data['body']);
			$message->setAuthorName($data['author_name']);
			$message->setAuthorAvatarUrl($data['author_avatar_url']);
			$message->setTopic($context->more);
		
			$message = $message->data();
			$status_message = $channel->insert($message, true);

		
			$data = array('ok' => $status_message['ok'] ? true : false,
			              'id' => $message['_id']);
		
			return GalaxyResponse::responseWithData($data);
		}
		else
		{
			GalaxyResponse::errorResponseWithForm($form);
		}
	}
	
	public function messages_post($context)
	{
		$valid = array('title'             => true,
		               'body'              => true,
		               'author_name'       => true,
		               'author_avatar_url' => true);
		
		$input = array_intersect_key($_POST, $valid);
		if(count($input))
		{
			$form_context = array(AMForm::kDataKey => $input);
			$form = AMForm::formWithContext($form_context);
			
			
			$fields = array();
			foreach($input as $key=>$value)
			{
				$fields[$key] = $value;
				
				// author_avatar_url can be set to null
				if($key != 'author_avatar_url')
				{
					$label = str_replace('_', ' ', $key);
					$form->addValidator(new AMInputValidator($key, AMValidator::kRequired, 1, null, $label.' required'));
				}
				
			}
			
			if($form->isValid)
			{
				$application    = GalaxyAPI::applicationIdForChannelId($context->channel);
				$options        = array('default' => GalaxyAPI::databaseForId($application));
				$channel        = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
				
				$action = array('$set' => $fields);
				$channel->update(array('_id' => $context->more), $action);
				
				return GalaxyResponse::responseWithData(array('ok' => true));
			}
			else
			{
				return GalaxyResponse::errorResponseWithForm($form);
			}
		}
		else
		{
			return GalaxyResponse::responseWithError(GalaxyError::errorWithString('no data present'));
		}
	}
	
	public function messages_delete($context)
	{
		$application    = GalaxyAPI::applicationIdForChannelId($context->channel);
		$options        = array('default' => GalaxyAPI::databaseForId($application));
		$channel        = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
		$channel->remove(array('_id' => $context->more));
		return GalaxyResponse::responseWithData(array('ok'=>true));
	}
	
	public function message_details_get($context)
	{
		if($context->more && $context->channel)
		{
			$application    = GalaxyAPI::applicationIdForChannelId($context->channel);
			$options        = array('default' => GalaxyAPI::databaseForId($application));
			$channel        = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
			$data = $channel->findOne(array('_id' => $context->more));
			
			return GalaxyResponse::responseWithData($data);
		}
		else
		{
			return GalaxyResponse::responseWithError(GalaxyError::errorWithString('invalid message'));
		}
	}
	
	public function messages_get($context)
	{
		$options        = array('default' => GalaxyAPI::databaseForId($context->application));
		$channel        = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
		$messages       = $channel->find(array('topic'=> $context->more));
		
		$data = array();
		
		foreach($messages as $message)
		{
			$data[] = array('id'                 => $message['_id'],
			                'title'              => $message['title'],
			                'body'               => $message['body'],
			                'author_name'        => $message['author_name'],
			                'author_avatar_url'  => $message['author_avatar_url'],
			                'origin'             => $message['origin'],
			                'origin_description' => $message['origin_description'],
			                'origin_domain'      => $message['origin_domain'],
			                'created'            => $message['created'],
			                'requests'           => $message['requests'],
			                'type'               => $message['type']);
		}
		
		return GalaxyResponse::responseWithData($data);
	}
	
	public function topics_put(GalaxyContext $context)
	{
		// anything incoming as a PUT will be coming in as JSON
		$data = json_decode(file_get_contents('php://input'), true);
		
		$form_context = array(AMForm::kDataKey => $data);
		$form = AMForm::formWithContext($form_context);
		$form->addValidator(new AMInputValidator('author_name', AMValidator::kRequired, 1, null, 'author name required'));
		$form->addValidator(new AMInputValidator('title', AMValidator::kRequired, 1, null, 'title required'));
		$form->addValidator(new AMInputValidator('body', AMValidator::kRequired, 1, null, 'body required'));
	
		if($form->isValid)
		{
			$status_topic   = false;
			$status_message = false;
			$options        = array('default' => GalaxyAPI::databaseForId($context->application));
			$channel        = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
			
			$topic          = GalaxyForumTopic::topicWithContext($context);
			$topic->setTitle($data['title']);
			$topic->setAuthorName($data['author_name']);
			$topic->setAuthorAvatarUrl($data['author_avatar_url']);
			$topic = $topic->data();
			$status_topic = $channel->insert($topic, true);

			if($status_topic['ok'])
			{
				$message = GalaxyForumMessage::messageWithContext($context);
				$message->setTitle($data['title']);
				$message->setBody($data['body']);
				$message->setAuthorName($data['author_name']);
				$message->setAuthorAvatarUrl($data['author_avatar_url']);
				$message->setTopic($topic['_id']);
		
				$message = $message->data();
				$status_message = $channel->insert($message, true);
			}
			
			
			$data = array('topic'   => array('ok' => $status_topic['ok'] ? true : false,
					                         'id' => $topic['_id']),

					      'message' => array('ok' => $status_message['ok'] ? true : false,
					                         'id' => $message['_id']));
			
			print_r($data);
			return GalaxyResponse::responseWithData($data);
		}
		else
		{
			return GalaxyResponse::errorResponseWithForm($form);
		}
	}
	
	public function topics_delete(GalaxyContext $context)
	{
		$options  = array('default' => GalaxyAPI::databaseForId($context->application));
		$channel  = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
		
		// delete all messages
		$channel->remove(array('_id' => $context->more));
		$channel->remove(array('topic' => $context->more));
	}
	
	public function topics_get(GalaxyContext $context)
	{
		$options  = array('default' => GalaxyAPI::databaseForId($context->application));
		$channel  = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
		
		$result   = $channel->find(array('type' => GalaxyAPIConstants::kTypeForumTopic));
		$data     = array();
		
		foreach($result as $topic)
		{
			$data[] = array('id'                 => $topic['_id'],
			                'type'               => $topic['type'],
			                'title'              => $topic['title'],
			                'author_name'        => $topic['author_name'],
			                'author_avatar_url'  => $topic['author_avatar_url'],
			                'created'            => $topic['created'],
			                'origin'             => $topic['origin'],
			                'origin_description' => $topic['origin_description'],
			                'origin_domain'      => $topic['origin_domain'],
			                'requests'           => $topic['requests']);
		}
		
		return GalaxyResponse::responseWithData($data);
	}
}
?>