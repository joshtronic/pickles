<?php

$config = array(
	// Define your envrionments, name => hostname or IP
	'environments' => array(
		'local'       => '127.0.0.1',
		'staging'     => 'dev.mysite.com',
		'production'  => 'www.mysite.com',
	),

	// PHP configuration options
	'php' => array(
		'display_error'   => true,
		'error_reporting' => -1,
		'date.timezone'   => 'America/New_York',
	),
	
	// PICKLES configuration options
	'pickles' => array(
		// Disable with a "site down" message
		'disabled'        => false,
		// Use sessions
		'session'         => true,
		// Name of the parent template
		'template'        => 'index',
		// Name of the default module
		'module'          => 'home',
		// Name of the module to serve on 404 errors
		'404'             => 404,
		// Internal class overides
		'classes'         => array('Form' => 'CustomForm'),
		// Default datasource
		'datasource'      => 'mysql',
		// Whether or not you want to use the profiler
		'profiler'        => array(
			'local'      => true,
			'staging'    => false,
			'production' => false,
		),
	),

	// Datasources, keys are what's referenced in your models
	'datasources' => array(
		'mysql' => array(
			'type'     => 'MySQL',
			'hostname' => 'localhost',
			'username' => 'root',
			'password' => '',
			'database' => 'test'
		),
	),

	// Anything can be defined
	'stuff' => array(
		'foo'  => 'bar',
		'spam' => 'eggs',
		// and can be broken out by environment
		'bacon' => array(
			'local'      => true,
			'staging'    => true,
			'production' => false
		)
	)
);

?>
