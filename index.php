<?php

header('Content-Type: text/html; charset=utf-8');

// load Serpent files via Composer autoloader
include('vendor/autoload.php');

// beautify output
class HtmlReporterExt extends HtmlReporter {
	private $character_set;

	function paintHeader($test_name) {
		$this->sendNoCacheHeaders();

		print '<!DOCTYPE html>';
		print '<html>';
		print '<head><title>'.$test_name.'</title>';
		print '<meta http-equiv="Content-Type" content="text/html; charset="'.$this->character_set.'" />';
		print '<style type="text/css">';
		print $this->getCss();
		print "</style>";
		print "</head><body>";
		print "<h1>$test_name</h1>";
		flush();
	}

	function paintPass($message) {
		parent::paintPass($message);
		print "<span class=\"tag pass\">Pass</span> ";
		$breadcrumb = $this->getTestList();
		array_shift($breadcrumb);
		print implode(" &raquo; ", $breadcrumb) . "<br />";
	}

	function paintFail($message) {
		$reporter = $this;
		$message = preg_replace_callback('|\[(.+?)\]|s', function($match) use ($reporter) {
			return '<code>'.$reporter->htmlEntities($match[1]).'</code>';
		}, $message);
		$this->_fails++;
		print "<span class=\"tag fail\">Fail</span><br />";
		$breadcrumb = $this->getTestList();
		array_shift($breadcrumb);
		print '<b>'.implode(" &raquo; ", $breadcrumb).'</b>';
		print "<br />" . $message . "<br />";
	}

	function paintException($message) {
		$reporter = $this;
		$message = preg_replace_callback('|with message \'(.+?)\'|', function($match) use ($reporter) {
			return '<code>'.$reporter->htmlEntities($match[1]).'</code>';
		}, $message);
		$this->_fails++;
		print "<span class=\"tag exception\">Exception</span><br />";
		$breadcrumb = $this->getTestList();
		array_shift($breadcrumb);
		print '<b>'.implode(" &raquo; ", $breadcrumb).'</b>';
		print "<br />" . $message . "<br />";
	}

	public function htmlEntities($message) {
		return htmlentities($message, ENT_COMPAT, $this->character_set);
	}

	function getCss() {
		return parent::getCss() .
			'
			body { color: #333; background: #eee; font: normal 16px/20px "Times New Roman"; }
			code { background-color: #ccc; padding: 0 5px; }
			 .tag { display: inline-block; color: white; width: 80px; text-align: center; margin: 0 0 1px 0; }
			 .fail { background-color: red; }
			 .pass { background-color: green; }
			 .exception { background-color: red; }
			 ';
	}
}

// simplify debugging
function dump() {
	$args = func_get_args();
	
	foreach ($args as $key=>$arg) {
		echo '<pre>';
		$arg = str_replace(' ', '·', $arg);
		echo htmlspecialchars(print_r($arg, true));
		echo '</pre>';
	}
}

// measure execution time
$time_start = microtime(true);

$test = new TestSuite('Serpent Unit Tests');
$test->addFile('test.php');
$test->run(new HtmlReporterExt('utf-8'));

// measure execution time
$time_end = microtime(true);
$time = $time_end - $time_start;

// output execution time
echo '<div style="color: #999; padding: 8px;">';
echo 'done in '.round($time, 3).' seconds.';
echo '</div>';/*
Add language test
*/
