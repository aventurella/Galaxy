<?php
require 'GalaxyApplication.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/models/forum/GalaxyForumTopic.php';
require $_SERVER['DOCUMENT_ROOT'].'/application/models/forum/GalaxyForumMessage.php';

class GalaxyForum extends GalaxyApplication
{
	public function messages_post($context)
	{
		// at a minimum all messages and topics must have an author name
		// might want to think of a better response than Unauthorized though =)
		if(empty($_POST['author_name']))
		{
			GalaxyResponse::unauthorized();
		}
		
		$options        = array('default' => GalaxyAPI::databaseForId($context->application));
		$channel        = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);

			$message = GalaxyForumMessage::messageWithContext($context);
			$message->setTitle($_POST['title']);
			$message->setBody($_POST['body']);
			$message->setAuthorName($_POST['author_name']);
			$message->setAuthorAvatarUrl($_POST['author_avatar_url']);
			$message->setTopic($context->more);
			
			$message = $message->data();
			$status_message = $channel->insert($message, true);

		
		$data = array('ok' => $status_message['ok'] ? true : false,
		              'id' => $message['_id']);
		
		return GalaxyResponse::responseWithData($data);
	}
	
	public function messages_get($context)
	{
		$options        = array('default' => GalaxyAPI::databaseForId($context->application));
		$channel        = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
		$messages       = $channel->find(array('topic'=> $context->more, 'type' => GalaxyAPIConstants::kTypeForumMessage));
		
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
			                'created'            => $message['created'],
			                'type'               => $message['type']);
		}
		
		return GalaxyResponse::responseWithData($data);
	}
	
	public function topics_post(GalaxyContext $context)
	{
		// at a minimum all messages and topics must have an author name
		// might want to think of a better response than Unauthorized though =)
		if(empty($_POST['author_name']))
		{
			GalaxyResponse::unauthorized();
		}
		
		$status_topic   = false;
		$status_message = false;
		$options        = array('default' => GalaxyAPI::databaseForId($context->application));
		$channel        = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPI::databaseForId($context->channel), $options);
		
		$topic          = GalaxyForumTopic::topicWithContext($context);
		$topic->setTitle($_POST['title']);
		$topic->setAuthorName($_POST['author_name']);
		$topic->setAuthorAvatarUrl($_POST['author_avatar_url']);
		$topic = $topic->data();
		$status_topic = $channel->insert($topic, true);
		
		if($status_topic['ok'])
		{
			$message = GalaxyForumMessage::messageWithContext($context);
			$message->setTitle($_POST['title']);
			$message->setBody($_POST['body']);
			$message->setAuthorName($_POST['author_name']);
			$message->setAuthorAvatarUrl($_POST['author_avatar_url']);
			$message->setTopic($topic['_id']);
			
			$message = $message->data();
			$status_message = $channel->insert($message, true);
		}
		
		$data = array('topic'   => array('ok' => $status_topic['ok'] ? true : false,
		                                 'id' => $topic['_id']),

		              'message' => array('ok' => $status_message['ok'] ? true : false,
		                                 'id' => $message['_id']));
		
		return GalaxyResponse::responseWithData($data);
	}
	
	public function topics_delete(GalaxyContext $context)
	{
		
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
			                'origin_description' => $topic['origin_description']);
		}
		
		return GalaxyResponse::responseWithData($data);
	}
}
?>