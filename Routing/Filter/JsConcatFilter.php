<?php
/**
 * JsConcatFilter scans the ./Assets/js directory for js files for
 * //= require ...
 * statements and concatenates those into a single file.
 * The concatenated file is then written to the webroot js folder.
 *
 * PHP 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the below copyright notice.
 *
 * @copyright     Copyright 2013, Frank Förster (http://frankfoerster.com)
 * @link          http://github.com/frankfoerster/cakephp-lessy
 * @package       Lessy
 * @subpackage    Lessy.Routing.Filter
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('CakePlugin', 'Core');
App::uses('DispatcherFilter', 'Routing');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');

class JsConcatFilter extends DispatcherFilter {

/**
 * Priority value
 *
 * @var int
 * @see http://book.cakephp.org/2.0/en/development/dispatch-filters.html
 */
	public $priority = 9;

/**
 * beforeDispatch middleware entry point
 *
 * @param CakeEvent $event
 * @return bool|CakeResponse|void
 */
	public function beforeDispatch(CakeEvent $event) {
		// check DEBUG Level and SKIP_ON_PRODUCTION setting
		if (Configure::read('debug') === 0 && Configure::read('Lessy.SKIP_ON_PRODUCTION') === true) {
			return 'skipped';
		}
		// process ./Assets/js/*.js files of all plugins
		$plugins = CakePlugin::loaded();
		foreach ($plugins as $plugin) {
			$webroot = CakePlugin::path($plugin) . 'webroot' . DS;
			$assetDir = CakePlugin::path($plugin) . 'Assets' . DS;
			$jsDir = new Folder($assetDir . 'js', false);
			$this->processJsFiles($jsDir, $webroot);
		}
		// process ./Assets/js/*.js files of the app itself
		$webroot = APP . WEBROOT_DIR . DS;
		$assetDir = APP . 'Assets' . DS;
		$jsDir = new Folder($assetDir . 'js');
		$this->processJsFiles($jsDir, $webroot);
		return null;
	}

/**
 * Process all *.js manifest files in $jsDir, parse the require statements and
 * write the concatenated output to the $webroot js directory
 *
 * @param Folder $jsDir folder holding the *.js manifest files to be processed
 * @param string $webroot absolute path to webroot with trailing DS
 * @return void
 */
	public function processJsFiles(Folder $jsDir, $webroot) {
		foreach ($jsDir->find('.*\.js') as $file) {
			$manifestFile = new File($jsDir->path . DS . $file, false);
			$manifestFileContents = $this->_normalizeLineEndings($manifestFile->read());

			$matches = array();
			$filesToConcat = array();

			if (preg_match_all("/^\/\/=\srequire\s(.*)\.js$/im", $manifestFileContents, $matches) > 0) {
				foreach ($matches[1] as $m) {
					$filesToConcat[] = $m . '.js';
				}
			}

			$manifestFile->close();

			$content = array();
			foreach ($filesToConcat as $jsFilePath) {
				$jsFile = new File($jsDir->path . DS . $jsFilePath, false);
				if ($jsFile->exists()) {
					$content[] = $jsFile->read();
				}
				$jsFile->close();
			}
			$content = join("\n", $content) . "\n";
			$content = $this->_normalizeLineEndings($content);

			$outputFile = new File($webroot . 'js' . DS . $file, true, 0775);
			$outputFile->write($content);
			$outputFile->close();
		}
	}

/**
 * Normalize line endings to LF
 *
 * @param string $string
 * @return string
 */
	private function _normalizeLineEndings($string) {
		$string = str_replace("\r\n", "\n", $string);
		$string = str_replace("\r", "\n", $string);
		$string = preg_replace("/\n{2,}/", "\n\n", $string);

		return $string;
	}

}
