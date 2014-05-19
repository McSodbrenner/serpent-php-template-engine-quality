<?php

/*
Add language test
install via https://packagist.org/packages/mcsodbrenner/serpent
*/

class TestDefault extends UnitTestCase {
	public function setUp() {
		include_once('../trunk/McSodbrenner/Serpent/Autoloader.php');

		// init serpent
		$dir = dirname(__FILE__).'/';
		$this->serpent = new \McSodbrenner\Serpent\Serpent($dir.'templates_compiled/', 'utf-8', true);
		$this->serpent->addResource('file',
			new \McSodbrenner\Serpent\ResourceFile($dir.'templates/', '.htm', 'de')
		);
		$this->serpent->addMappings(array(
			'squareroot' => 'sqrt'
		));
	}
	
	public function tearDown() {
		unset($this->serpent);
	}
	
	public function testBlocks() {
		// test default value
		$result = $this->serpent->render('sub/index');
		$this->assertIdentical($result, "Second example");
	}

	public function testPassedValues() {
		$object = new stdClass;
		$data = array(
			'null'		=> null,
			'integer'	=> 3,
			'array'		=> array(1, '2'),
			'object'	=> $object,
			'boolean'	=> true
		);		
		$this->serpent->pass($data);
		$result = $this->serpent->render('values');
		
		ob_start();
		var_dump($data);
		$assertion = ob_get_clean();
		
		$this->assertIdentical($result, $assertion);		
	}
	
	public function testMappings() {
		$result = $this->serpent->render('mappings');
		$assertion = '4World!';
		$this->assertIdentical($result, $assertion);
	}

	public function testXss() {
		$data = array(
			'world'	=> 'World!',
		);

		// test include
		$this->serpent->pass($data);
		try {
			$result = $this->serpent->render('xss');
			$this->asserttrue(false);
		} catch (McSodbrenner\Serpent\SerpentSecurityException $e) {
			$this->asserttrue(true);
		}
	}

	public function testCompilerSerpent() {
		$data = array(
			'world'	=> 'World!',
			'html'	=> '<html>'
		);

		// test include
		$this->serpent->pass($data);
		$result = $this->serpent->render('master');
		$assertion = 'Test-Suite

use php1: php1 
use php2: '.$_SERVER['REQUEST_METHOD'].' 
vars:     Hello World! 
include:  Hello World! 
capture:  Hello World! 
escape:   &lt;html&gt; 
escaped tilde: ~ 
show also backslash: \~ 
World! 

:repeat
Hello 1Hello 1Hello 1Hello 1Hello 1Hello 1

:loop
Hello 1Hello 2Hello 3Hello 4Hello 5Hello 6

:capture
Hello World

Inheritance with branches:
First example 
World! example 
Third example';

		/*
		dump($result);
		dump($assertion);
		*/

		$this->assertIdentical($result, $assertion);
	}

	/*
	public static function _testMarkdown($content) {
		$markdown = new dflydev\markdown\MarkdownParser();
		return $markdown->transformMarkdown($content);
	}
	*/

	public function testMarkdown() {
		/*
		$this->serpent->addMappings(array(
			'markdown' => 'TestDefault::_testMarkdown'
		));
		*/

		$this->serpent->addMappings(array(
			'markdown' => function($content){
				$markdown = new dflydev\markdown\MarkdownParser();
				return $markdown->transformMarkdown($content);
			}
		));		

		$content = 'Headline
========

Subheadline
-----------

Paragraph';
		
		// test include
		$this->serpent->pass(array(
			'content'	=> $content,
		));
	
		$assertion = '<h1>Headline</h1>

<h2>Subheadline</h2>

<p>Paragraph</p>
';
		$result = $this->serpent->render('markdown');
		$this->assertIdentical($result, $assertion);
	}
}
