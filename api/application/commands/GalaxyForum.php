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
		
		// artifically add the author name for validation purposes
		$data['author_name'] = $data['author']['name'];
		
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
			$message->setAuthor($data['author']);
			$message->setTopic($context->more);
			
			$last_message   = $message->last_message_snapshot();
			$message        = $message->data();
			$status_message = $channel->insert($message, true);
			
			$channel->update(array('_id'=>$context->more), array('$set'=>array('last_message'=>$last_message)));
			$channel->update(array('_id'=>$context->more), array('$inc'=>array('replies'=>1)));

		
			$data = array('status' => $status_message['ok'] ? true : false,
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
		               'author'            => true);
		
		$input = array_intersect_key($_POST, $valid);
		if(count($input))
		{
			if(isset($input['author']))
			{
				// AMForm doesn't understand nested data yet, so we surface it if we need it.
				if(isset($input['author']['name'])) $input['author_name'] =& $input['author']['name'];
				if(isset($input['author']['avatar_url'])) $input['author_avatar_url'] =& $input['author']['avatar_url'];
			}
			
			$form_context = array(AMForm::kDataKey => $input);
			$form = AMForm::formWithContext($form_context);
			
			
			$fields = array();
			foreach($input as $key=>$value)
			{
				// AMFrom should figure out a way to validate nested keys
				if($key != 'author_avatar_url' && $key != 'author_name'){
					$fields[$key] = $value;
				}
				
				// author_avatar_url can be set to null
				if($key != 'author_avatar_url' && !is_array($value))
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
				
				return GalaxyResponse::responseWithData();
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
		
		// get the message, so we can get the topic, so we can see if this message was the origin message, and update the topic accordingly
		$message = $channel->findOne(array('_id' => $context->more));
		$channel->remove(array('_id' => $context->more));
		$channel->update(array('_id' => $message['topic']), array('$inc'=>array('replies' => -1)));
		
		if($message['topic_origin'])
		{
			// if the message was the topic origin, we need to update the topic with the next message
			// and set that next message to the origin
			
			$next_message = $channel->find(array('topic'=>$message['topic']));
			$next_message = $next_message->sort(array('created'=> -1));
			$next_message = $next_message->limit(1);
			
			if($next_message->count())
			{
				$next_message = $next_message->getNext();
			
				// make the next message the origin and update the topic accordingly
				$channel->update(array('_id' => $next_message['_id']), array('$set'=>array('topic_origin' => true)));
				$channel->update(array('_id' => $message['topic']), array('$set'=>array('title'              => $next_message['title'],
				                                                                        'author'             => $next_message['author'],
				                                                                        'origin_message_id'  => $next_message['_id'],
				                                                                        'source'             => $next_message['source']
				                                                                        )));
			}
			else
			{
				$context->more = $message['topic'];
				$this->topics_delete($context);
			}
		}
		
		return GalaxyResponse::responseWithData();
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
		$messages       = $messages->sort(array('created'=> 1));
		$data = array();
		
		foreach($messages as $message)
		{
			$data[] = array('id'                 => $message['_id'],
			                'title'              => $message['title'],
			                'body'               => $message['body'],
			                'author'             => $message['author'],
			                'source'             => $message['source'],
			                'created'            => $message['created'],
			                //'requests'           => $message['requests'],
			                'type'               => $message['type']);
		}
		
		return GalaxyResponse::responseWithData($data);
	}
	
	public function topics_put(GalaxyContext $context)
	{
		// anything incoming as a PUT will be coming in as JSON
		$data = json_decode(file_get_contents('php://input'), true);
		
		// artifically add the author name for validation purposes
		$data['author_name'] = $data['author']['name'];
		
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
			$topic->setAuthor($data['author']);
			$topic = $topic->data();
			$status_topic = $channel->insert($topic, true);

			if($status_topic['ok'])
			{
				$message = GalaxyForumMessage::messageWithContext($context);
				$message->setTitle($data['title']);
				$message->setBody($data['body']);
				$message->setAuthor($data['author']);
				$message->setTopicOrigin(true);
				$message->setTopic($topic['_id']);
		
				$last_message   = $message->last_message_snapshot();
				$message        = $message->data();
				$status_message = $channel->insert($message, true);
				
				
				if($status_message['ok'])
				{
					// we can get the message id, and omit this update, but then if the topic set fails we will be left with an orphaned document
					$channel->update(array('_id' => $topic['_id']), array('$set'=>array('origin_message_id' => $message['_id'],
					                                                                    'last_message'      => $last_message)));
				}
			}
			
			
			$data = array('topic'   => array('status' => $status_topic['ok'] ? true : false,
					                         'id' => $topic['_id']),

					      'message' => array('status' => $status_message['ok'] ? true : false,
					                         'id' => $message['_id']));
			
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
		
		return GalaxyResponse::responseWithData();
		
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
			                'requests'           => $topic['requests'],
			                'replies'            => $topic['replies'],
			                'title'              => $topic['title'],
			                'author'             => $topic['author'],
			                'source'             => $topic['source'],
			                'last_message'       => $topic['last_message'],
			                'created'            => $topic['created'],
			                'type'               => $topic['type']
			                );
		}
		
		return GalaxyResponse::responseWithData($data);
	}
}
?>