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
App::uses('LessMinFilter', 'Lessy.Routing/Filter');

class LessMinFilterTest extends CakeTestCase {

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
		$cssFolder = new Folder($this->testAppWebroot . 'css');
		$cssFolder->delete();

		if (CakePlugin::loaded('TestPlugin')) {
			$cssFolder = new Folder(CakePlugin::path('TestPlugin') . 'webroot' . DS . 'css' . DS);
			$cssFolder->delete();

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
 * @covers LessMinFilter::processLessFiles
 * @return void
 */
	public function testProcessLessFiles() {
		$filter = new LessMinFilter();
		$cssFolder = $this->testAppWebroot . 'css' . DS;

		// css folder does not exist first hand
		$this->assertFalse(is_dir($cssFolder));

		// process less files
		$filter->processLessFiles(new Folder($this->testAppAssets . 'less' . DS, false), $this->testAppWebroot);

		// css folder should be there now
		$this->assertTrue(is_dir($cssFolder));

		// and the compiled css file should be present
		$cssFile = $cssFolder . 'style.css';
		$this->assertTrue(file_exists($cssFile));

		// check that the less file is correctly compiled to css and minified
		$expected = 'body{color:#333;padding:0;margin:0}';
		$result = file_get_contents($cssFile);
		$this->assertEqual($expected, $result);
	}

/**
 * Test Less compilation and minification for plugins.
 *
 * @covers LessMinFilter::beforeDispatch
 * @return void
 */
	public function testPluginCompilation() {
		$filter = new LessMinFilter();
		$request = new CakeRequest('/');
		$response = $this->getMock('CakeResponse');
		App::build(array(
			'Plugin' => array(CakePlugin::path('Lessy') . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS),
		), APP::RESET);

		CakePlugin::load('TestPlugin');
		$pluginWebroot = CakePlugin::path('TestPlugin') . 'webroot' . DS;
		$cssFolder = $pluginWebroot . 'css' . DS;

		// css folder does not exist first hand
		$this->assertFalse(is_dir($cssFolder));

		$event = new CakeEvent('DispatcherTest', $this, compact('request', 'response'));
		$filter->beforeDispatch($event);

		// css folder should be there now
		$this->assertTrue(is_dir($cssFolder));

		// and the compiled css file should be present
		$cssFile = $cssFolder . 'style.css';
		$this->assertTrue(file_exists($cssFile));

		// check that the less file is correctly compiled to css and minified
		$expected = 'body{color:#333;padding:0;margin:0}';
		$result = file_get_contents($cssFile);
		$this->assertEqual($expected, $result);
	}

/**
 * Test 'Lessy.SKIP_ON_PRODUCTION' setting
 *
 * @covers LessMinFilter::beforeDispatch
 * @return void
 */
	public function testSkipOnProduction() {
		$filter = new LessMinFilter();
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

}
