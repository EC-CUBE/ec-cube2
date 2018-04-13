<?php
$HOME = realpath(dirname(__FILE__)) . "/../../../..";
require_once($HOME . "/tests/class/Common_TestCase.php");

/**
 * Test for Cache_Lite_NestedOutput
 *
 * @package Cache_Lite
 * @category Caching
 * @version $Id$
 * @author Markus Tacker <tacker@php.net>
 */

//require_once __DIR__ . '/../Cache/Lite/NestedOutput.php';

class NestedOutputTest extends Common_TestCase
{
	/**
	 * "Test" used for documenting the nested output buffering feature of php
	 */
	public function testPhpObNesting()
	{
		$outsideText = "This is the outside";
		$insideText = "This is the inside";
		ob_start();
		echo $outsideText;
		ob_start();
		echo $insideText;
		$innerContents = ob_get_contents();
		ob_end_clean();
		$outerContents = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( $insideText, $innerContents );
		$this->assertEquals( $outsideText, $outerContents );
	}

	/**
	 * Test for Cache_Lite_NestedOutput
	 */
	public function testCacheLiteOutputNesting()
	{
		$outsideText = "This is the outside";
		$insideText = "This is the inside";

		$options = array(
		    'caching' => true,
		    'cacheDir' => '/tmp/',
		    'lifeTime' => 10
		);
		$cache = new Cache_Lite_NestedOutput($options);
		$this->assertFalse($cache->start('foo', 'a'));
		echo $outsideText;
		$this->assertFalse($cache->start('bar', 'b'));
   		echo $insideText;
      	$inside = $cache->end();
		$outside = $cache->end();
		$this->assertEquals($outsideText, $outside, 'Validate outside');
		$this->assertEquals($insideText, $inside, 'Validate inside');

		$cache = new Cache_Lite_NestedOutput($options);
		$this->assertEquals($outsideText, $cache->start('foo', 'a'), 'Validate outside');
		$this->assertEquals($insideText, $cache->start('bar', 'b'), 'Validate inside');
	}
}
