<?php
abstract class GalaxyApplication
{
	public function channels_get(GalaxyContext $context)
	{
		$options  = array('default' => GalaxyAPI::databaseForId($context->application));
		$channels = GalaxyAPI::database(GalaxyAPIConstants::kDatabaseMongoDB, GalaxyAPIConstants::kDatabaseChannels, $options);
		$result   = $channels->find();
		$data     = array();
		
		foreach($result as $channel)
		{
			$data[] = array('id'          => $channel['_id'],
			                'type'        => $channel['type'],
			                'description' => $channel['description']);
		}
		
		return GalaxyResponse::responseWithData($data);
	}
}
?>