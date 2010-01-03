<?php
// Please note that the API end of things also holds a copy of these, so if you update here, please update there.

class RenegadeConstants
{
	const kDatabaseCouchDB       = 1;
	const kDatabaseTokyoCabinet  = 2;
	const kDatabaseRedis         = 3;
	const kDatabaseMongoDB       = 4;
	
	const kDatabaseUsers         = 0;
	const kDatabaseSessions      = 1;
	const kDatabaseVerification  = 2;
	const kDatabaseCertificates  = 3;
	const kDatabaseApplications  = 'applications';
	const kDatabaseChannels      = 'channels';
	const kDatabaseSubscriptions = 'subscriptions';
	const kDatabaseDefault       = 'galaxy';
	
	// trying to keep the prefixes all in one place
	// might make more sense to store them in their individual objects though.
	// for ease of configuration though they are here for now.
	const kTypeVerification     = 'verify';
	const kTypeSession          = 'session';
	const kTypeUser             = 'user';
	const kTypeChannel          = 'channel';
	const kTypeCertificate      = 'cert';
	const kTypeApplication      = 'application';
	const kTypeForum            = 'forum';
	
	const kSessionKey           = 'Renegade';
	const kChannelPrefix        = 'renegade/channels';
	
	const kPermissionRead       = 1;
	const kPermissionWrite      = 2;
	const kPermissionDelete     = 4;
	
	const kSchemeGalaxy         = 'glxy://';
}
?>