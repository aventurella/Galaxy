package galaxy.commands
{
	public class GalaxyChannels extends GalaxyCommand
	{
		override protected function prepareCommand():void
		{
			method   = GalaxyCommand.GALAXY_METHOD_GET;
			endpoint = 'channels';
		}
	}
}