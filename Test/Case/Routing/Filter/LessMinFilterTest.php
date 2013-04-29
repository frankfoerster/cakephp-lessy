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

		parent::setUp();
	}

/**
 * Delete css files/folders that have been created during the tests.
 *
 * @return void
 */
	public function tearDown() {
		$css_folder = new Folder($this->testAppWebroot . 'css');
		$css_folder->delete();

		foreach (CakePlugin::loaded() as $p) {
			$css_folder = new Folder(CakePlugin::path($p) . 'webroot' . DS . 'css' . DS);
			$css_folder->delete();
		}

		CakePlugin::unload('TestPlugin');

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
		$css_folder = $this->testAppWebroot . 'css' . DS;

		// css folder does not exist first hand
		$this->assertFalse(is_dir($css_folder));

		// process less files
		$filter->processLessFiles(new Folder($this->testAppWebroot . 'less' . DS, false), $this->testAppWebroot);

		// css folder should be there now
		$this->assertTrue(is_dir($css_folder));

		// and the compiled css file should be present
		$css_file = $css_folder . 'style.css';
		$this->assertTrue(file_exists($css_file));

		// check that the less file is correctly compiled to css and minified
		$expected = 'body{color:#333;padding:0;margin:0}';
		$result = file_get_contents($css_file);
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
		$plugin_webroot = CakePlugin::path('TestPlugin') . 'webroot' . DS;
		$css_folder = $plugin_webroot . 'css' . DS;

		// css folder does not exist first hand
		$this->assertFalse(is_dir($css_folder));

		$event = new CakeEvent('DispatcherTest', $this, compact('request', 'response'));
		$filter->beforeDispatch($event);

		// css folder should be there now
		$this->assertTrue(is_dir($css_folder));

		// and the compiled css file should be present
		$css_file = $css_folder . 'style.css';
		$this->assertTrue(file_exists($css_file));

		// check that the less file is correctly compiled to css and minified
		$expected = 'body{color:#333;padding:0;margin:0}';
		$result = file_get_contents($css_file);
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
		$old_debug_lvl = Configure::read('debug');
		Configure::write('Lessy.SKIP_ON_PRODUCTION', true);
		Configure::write('debug', 0);
		$event = new CakeEvent('DispatcherTest', $this, compact('request', 'response'));
		$this->assertEqual('skipped', $filter->beforeDispatch($event));

		Configure::write('debug', 2);
		$event = new CakeEvent('DispatcherTest', $this, compact('request', 'response'));
		$this->assertNull($filter->beforeDispatch($event));

		Configure::write('debug', $old_debug_lvl);
	}

}
