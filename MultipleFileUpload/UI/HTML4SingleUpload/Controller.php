<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */


namespace MultipleFileUpload\UI\HTML4SingleUpload;

use Nette\Environment;
use MultipleFileUpload\MultipleFileUpload;


/**
 * Description of MFUUIHTML4SingleUpload
 *
 * @author Jan Kuchař
 */
class Controller extends \MultipleFileUpload\UI\AbstractInterface {

	/**
	 * Is this upload your upload? (upload from this interface)
	 */
	public function isThisYourUpload() {
		return !(Environment::getHttpRequest()->getHeader('user-agent') === 'Shockwave Flash');
	}


	/**
	 *
	 * @param array $files
	 * @param array $names Array of indexes of $files array representing current nesting level. E.g. if we are iterating over $files[k1][k2] then $names=[k1,k2]
	 */
	private function processFiles(array $files, array $names = [])
	{
		foreach ($files as $name => $controlValue) {
			$names[] = $name;

			// MFU vždy posílá soubory v této struktuře:
			//
			// array(
			//	"token" => "blablabla",
			//	"files" => array(
			//		0 => FileUpload(...),
			//		...
			//	)
			// )

			// expanded POST array with $names indexes
			$postArr = \Nette\Utils\Arrays::getRef($_POST, $names);
			$isFormMFU = (
				is_array($controlValue) and
					isset($controlValue["files"]) and
					isset($postArr['token'])
			);

			if($isFormMFU) {
				$token = $postArr["token"];
				foreach ($controlValue["files"] AS $file) {
					self::processFile($token, $file);
				}
			// support for nested Nette\Forms\Container
			} elseif (is_array($controlValue)) {
				$this->processFiles($controlValue, $names);
			}
			// soubory, které se netýkají MFU nezpracujeme -> zpracuje si je standardním způsobem formulář
		}
	}


	/**
	 * Handles uploaded files
	 * forwards it to model
	 */
	public function handleUploads() {
		// Iterujeme nad přijatými soubory
		$this->processFiles(Environment::getHttpRequest()->getFiles());
		return true; // Skip all next
	}

	/**
	 * Renders interface to <div>
	 */
	public function render(MultipleFileUpload $upload) {
		$template = $this->createTemplate(dirname(__FILE__) . "/html.latte");
		$template->maxFiles = $upload->maxFiles;
		$template->mfu = $upload;
		return $template->__toString(TRUE);
	}

	/**
	 * Renders JavaScript body of function.
	 */
	public function renderInitJavaScript(MultipleFileUpload $upload) {
		return $this->createTemplate(dirname(__FILE__) . "/initJS.latte")->__toString(TRUE);
	}

	/**
	 * Renders JavaScript body of function.
	 */
	public function renderDestructJavaScript(MultipleFileUpload $upload) {
		return true;
	}

	/**
	 * Renders set-up tags to <head> attribute
	 */
	public function renderHeadSection() {
		return "";
	}

}
