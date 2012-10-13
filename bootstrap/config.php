<?php

$config = array(

	'environments' => array(
		'local'      => 'pickles.local',
		'production' => 'pickles.prod',
	),

	'php' => array(
		'local' => array(
			'date.timezone'          => 'America/New_York',
			'display_errors'         => true,
			'error_reporting'        => -1,
			'session.gc_maxlifetime' => Time::DAY,
		),
		'production' => array(
			'date.timezone'          => 'America/New_York',
			'display_errors'         => false,
			'error_reporting'        => -1,
			'session.gc_maxlifetime' => Time::DAY,
		),
	),

	'pickles' => array(
		'disabled'        => false,
		'session'         => 'files',
		'template'        => 'index',
		'module'          => 'home',
		//'404'             => 'error/404',
		'datasource'      => 'mysql',
		'cache'           => 'memcached',
		'profiler'        => array(
			'local'      => false,
			'production' => false,
		),
		'logging'        => array(
			'local'      => true,
			'production' => false,
		),
		'minify' => array(
			'local'      => true,
			'production' => false,
		),
	),

	'site' => array(
		'name'      => 'PICKLES Bootstrap',
		'analytics' => 'UA-########-#',
	),
);

?>
