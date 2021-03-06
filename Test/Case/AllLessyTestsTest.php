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
 * @subpackage    Lessy.Test.Case
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class AllLessyTestsTest extends PHPUnit_Framework_TestSuite {

	public static function suite() {
		$suite = new CakeTestSuite('All Lessy Tests');

		$path = dirname(__FILE__) . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
