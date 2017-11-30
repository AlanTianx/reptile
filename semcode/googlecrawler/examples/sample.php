<?php
/**
 * Author: howie
 * CreateTime: 24/06/2017 08:42
 * Description: get a search result by using PhpGoogle
 */

require_once '../vendor/autoload.php';

use \howie6879\PhpGoogle\MagicGoogle;

# Or new MagicGoogle()
$magicGoogle = new MagicGoogle();

$content = file_get_contents("./kw.txt");

$kw_arr = explode("\r\n", $content);

$cnt = count($kw_arr);
$i = 0;
while ( $i < $cnt) {
	$value = $kw_arr[$i];
	var_dump($value);
	$data = $magicGoogle->search_page($value);
	var_dump($data);
	$i++;
	// if($data){
	// }
//	sleep(mt_rand(15,20));
}

// foreach ($kw_arr as $key => $value) {
// 	// $value = "VisaHQ promo code";

// 	//exit;
// }



# The first page of results


// sleep(5);

// $data = $magicGoogle->search_page('hotdeals');

// var_dump($data);


# Get url
// $data = $magicGoogle->search_url('hotdeals');

// foreach ($data as $value) {
//     var_dump($value);
// }


/** Output
 * string(23) "https://www.python.org/"
 * string(33) "https://www.python.org/downloads/"
 * string(35) "https://docs.python.org/3/tutorial/"
 * string(44) "https://www.python.org/about/gettingstarted/"
 * string(43) "https://wiki.python.org/moin/BeginnersGuide"
 * string(41) "https://www.python.org/downloads/windows/"
 * string(24) "https://docs.python.org/"
 * string(59) "https://en.wikipedia.org/wiki/Python_(programming_language)"
 * string(39) "https://www.codecademy.com/learn/python"
 * string(25) "https://github.com/python"
 * string(38) "https://www.tutorialspoint.com/python/"
 * string(28) "https://www.learnpython.org/"
 * string(44) "https://www.programiz.com/python-programming"
 */

// sleep(3);

# Get {'title','url','text'}
// $data = $magicGoogle->search('hotdeals', 'en', '1');

// foreach ($data as $value) {
//     var_dump($value);
// }

/** Output
 * array(3) {
 * ["title"]=>
 * string(21) "Welcome to Python.org"
 * ["url"]=>
 * string(23) "https://www.python.org/"
 * ["text"]=>
 * string(54) "The official home of the Python Programming Language. "
 * }
 */

