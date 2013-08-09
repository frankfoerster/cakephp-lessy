<?php
/**
 *
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the below copyright notice.
 *
 * @copyright     Copyright 2013, Frank Förster (http://frankfoerster.com)
 * @link          http://github.com/frankfoerster/cakephp-lessy
 * @package       Lessy
 * @subpackage    Lessy.Test.Case.Routing.Filter
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('CakeEvent', 'Event');
App::uses('CakePlugin', 'Core');
App::uses('CakeTestCase', 'TestSuite');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
App::uses('JsConcatFilter', 'Lessy.Routing/Filter');

/**
 * Class JsConcatTestFilter
 * provides to public wrappers for protected JsConcatFilter methods.
 */
class JsConcatTestFilter extends JsConcatFilter {

	public function normalizeLineEndings($string, $system = false) {
		return $this->_normalizeLineEndings($string, $system);
	}

	public function hasChanged($manifestFile, $files, $jsDir, $outputFile) {
		return $this->_hasChanged($manifestFile, $files, $jsDir, $outputFile);
	}

}

class JsConcatFilterTest extends CakeTestCase {

/**
 * Holds the webroot of the test_app
 *
 * @var string
 */
	protected $testAppWebroot;

/**
 * Asset dir of testApp
 *
 * @var string
 */
	protected $testAppAssets;

/**
 * True if the Lessy plugin had to be specifically loaded for testing.
 *
 * @var bool
 */
	protected $_forceLoad = false;

/**
 * Setup the webroot of the test_app.
 *
 * @return void
 */
	public function setUp() {
		if (!CakePlugin::loaded('Lessy')) {
			$this->_forceLoad = true;
			CakePlugin::load('Lessy');
		}
		$this->testAppWebroot = CakePlugin::path('Lessy') . 'Test' . DS . 'test_app' . DS . 'webroot' . DS;
		$this->testAppAssets = CakePlugin::path('Lessy') . 'Test' . DS . 'test_app' . DS . 'Assets' . DS;

		parent::setUp();
	}

/**
 * Delete css files/folders that have been created during the tests
 * and unload plugins.
 *
 * @return void
 */
	public function tearDown() {
		$jsFolder = new Folder($this->testAppWebroot . 'js');
		$jsFolder->delete();

		if (CakePlugin::loaded('TestPlugin')) {
			$jsFolder = new Folder(CakePlugin::path('TestPlugin') . 'webroot' . DS . 'js' . DS);
			$jsFolder->delete();

			CakePlugin::unload('TestPlugin');
		}

		if ($this->_forceLoad) {
			CakePlugin::unload('Lessy');
		}

		parent::tearDown();
	}

/**
 * Test the functionality of LessMinFilter::processLessFiles
 *
 * @covers JsConcatFilter::processJsFiles
 * @return void
 */
	public function testProcessJsFiles() {
		$filter = new JsConcatFilter();
		$jsFolder = $this->testAppWebroot . 'js' . DS;

		// js folder is not present
		$this->assertFalse(is_dir($jsFolder));

		// process js files
		$filter->processJsFiles(new Folder($this->testAppAssets . 'js' . DS, false), $this->testAppWebroot);

		// js folder should be present
		$this->assertTrue(is_dir($jsFolder));

		// and the concatenated js file should be present
		$jsFile = $jsFolder . 'app.js';
		$this->assertTrue(file_exists($jsFile));

		// check that the manifest file is correctly parsed and the contents are concatenated
		$expected = "/** Custom Lib */" . PHP_EOL . PHP_EOL . "/** Second Lib */" . PHP_EOL . PHP_EOL;
		$result = file_get_contents($jsFile);
		$this->assertEqual($expected, $result);
	}

/**
 * Test js manifest processing for plugins.
 *
 * @covers JsConcatFilter::beforeDispatch
 * @return void
 */
	public function testPluginCompilation() {
		$filter = new JsConcatFilter();
		$request = new CakeRequest('/');
		$response = $this->getMock('CakeResponse');
		App::build(array(
			'Plugin' => array(CakePlugin::path('Lessy') . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS),
		), APP::RESET);

		CakePlugin::load('TestPlugin');
		$pluginWebroot = CakePlugin::path('TestPlugin') . 'webroot' . DS;
		$jsFolder = $pluginWebroot . 'js' . DS;

		// css folder does not exist first hand
		$this->assertFalse(is_dir($jsFolder));

		$event = new CakeEvent('DispatcherTest', $this, compact('request', 'response'));
		$filter->beforeDispatch($event);

		// css folder should be there now
		$this->assertTrue(is_dir($jsFolder));

		// and the compiled css file should be present
		$jsFile = $jsFolder . 'test-plugin.js';
		$this->assertTrue(file_exists($jsFile));

		// check that the manifest file is correctly parsed and the contents are concatenated
		$expected = "/** Custom Lib */" . PHP_EOL . PHP_EOL . "/** Second Lib */" . PHP_EOL . PHP_EOL;
		$result = file_get_contents($jsFile);
		$this->assertEqual($expected, $result);
	}

/**
 * Test 'Lessy.SKIP_ON_PRODUCTION' setting
 *
 * @covers JsConcatFilter::beforeDispatch
 * @return void
 */
	public function testSkipOnProduction() {
		$filter = new JsConcatFilter();
		$request = new CakeRequest('/');
		$response = $this->getMock('CakeResponse');

		// check 'SKIP_ON_PRODUCTION' setting
		$oldDebugLvl = Configure::read('debug');
		Configure::write('Lessy.SKIP_ON_PRODUCTION', true);
		Configure::write('debug', 0);
		$event = new CakeEvent('DispatcherTest', $this, compact('request', 'response'));
		$this->assertEqual('skipped', $filter->beforeDispatch($event));

		Configure::write('debug', 2);
		$event = new CakeEvent('DispatcherTest', $this, compact('request', 'response'));
		$this->assertNull($filter->beforeDispatch($event));

		Configure::write('debug', $oldDebugLvl);
	}

/**
 * Test normalization of line endings.
 *
 * @covers JsConcatFilter::_normalizeLineEndings
 * @return void
 */
	public function testNormalizeLineEndings() {
		$filter = new JsConcatTestFilter();
		$string = "a\rb\r\nc\nd\n\n\ne";
		$expected = "a\nb\nc\nd\n\ne";
		$result = $filter->normalizeLineEndings($string, false);
		$this->assertEqual($expected, $result);

		$expected = "a" . PHP_EOL . "b" . PHP_EOL . "c" . PHP_EOL . "d" . PHP_EOL . PHP_EOL . "e";
		$result = $filter->normalizeLineEndings($string, true);
		$this->assertEqual($expected, $result);
	}

/**
 * Test correct comparison of last modified file times
 * returns false if the compiled file time is greater
 * than the time of source files.
 *
 * @return void
 */
	public function testHasChanged1() {
		$filter = new JsConcatTestFilter();
		$jsDir = new Folder($this->testAppAssets . 'js', false);
		$outputFile = new File($this->testAppWebroot . 'js' . 'test.js', true, 0755);
		$files = array(
			'second-lib' . DS . 'second-lib.js'
		);
		$manifestFile = new File($this->testAppAssets . 'js' . DS . 'app.js', false);
		$this->assertFalse($filter->hasChanged($manifestFile, $files, $jsDir, $outputFile));
	}

/**
 * Test correct comparison of last modified file times
 * returns true if last modified time of a manifest file is greater
 * than the time of the compiled file.
 *
 * @return void
 */
	public function testHasChanged2() {
		$filter = new JsConcatTestFilter();
		$jsDir = new Folder($this->testAppAssets . 'js', false);
		$outputFile = new File($this->testAppWebroot . 'js' . 'test.js', true, 0755);
		$files = array();
		touch($outputFile->path, 0);
		$manifestFile = new File($this->testAppAssets . 'js' . DS . 'app.js', false);
		$this->assertTrue($filter->hasChanged($manifestFile, $files, $jsDir, $outputFile));
		$outputFile->delete();
	}

/**
 * Test correct comparison of last modified file times
 * returns true if last modified time of source files is greater
 * than the time of the compiled file.
 *
 * @return void
 */
	public function testHasChanged3() {
		$filter = new JsConcatTestFilter();
		$jsDir = new Folder($this->testAppAssets . 'js', false);
		$outputFile = new File($this->testAppWebroot . 'js' . 'test.js', true, 0755);
		$testFile = new File($this->testAppAssets . 'js' . DS . 'test.js', true, 0755);
		$files = array(
			'test.js'
		);
		touch($testFile->path);
		touch($outputFile->path, 1);
		$manifestFile = new File($this->testAppAssets . 'js' . DS . 'blub.js', true, 0755);
		touch($manifestFile->path, 0);
		$this->assertTrue($filter->hasChanged($manifestFile, $files, $jsDir, $outputFile));
		$testFile->delete();
		$manifestFile->delete();
		$outputFile->delete();
	}
}
