<?php

ob_start();

require_once '.composer/autoload.php';

$root = org\bovigo\vfs\vfsStream::setup('site');
define('SITE_PATH', org\bovigo\vfs\vfsStream::url('site/'));

require_once 'classes/Convert.php';
require_once 'classes/Date.php';
require_once 'classes/Time.php';
require_once 'classes/String.php';

require_once 'classes/Object.php';
require_once 'classes/Config.php';
require_once 'classes/Display.php';
require_once 'classes/File.php';

require_once 'classes/API/Common.php';
require_once 'classes/API/Gravatar.php';

require_once 'pickles.php';

?>
