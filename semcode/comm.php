<?php

// Add the library to the include path. This is not neccessary if you've already
// done so in your php.ini file.
#$path = dirname(__FILE__) . '/googleads-php-lib-5.5.2/src';
#$path = dirname(__FILE__) . '/adwords-examples-and-lib-6.2.0/lib';
#$path = dirname(__FILE__) . '/adwords-examples-and-lib-8.3.0/lib';
#$path = dirname(__FILE__) . '/adwords-examples-and-lib-13.0.0/lib';
#$path = dirname(__FILE__) . '/adwords-examples-and-lib-14.0.0/lib';
$path = dirname(__FILE__).'/adwords-examples-and-lib-19.0.0/lib';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

$path = dirname(__FILE__) . '/../../include';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
define('AUTH_CONFIG', dirname(__FILE__).'/account.ini');
define('LIB_PATH', 'Google/Api/Ads/AdWords/Lib');
define('UTIL_PATH', 'Google/Api/Ads/Common/Util');
define('ADWORDS_UTIL_PATH', 'Google/Api/Ads/AdWords/Util');
require_once 'Google/Api/Ads/AdWords/Lib/AdWordsUser.php';

require_once 'Zend/Config/Ini.php';


?>
