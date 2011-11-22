<?php
/**
 * 
 *
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	Dan Rummel <dan@prescreen.com>
 * @created	8/23/11
 */

namespace prescreen\exceptions;

/**
 * Thrown when schema fails validation
 */
class InvalidFileException extends \Exception {

	public $validationName;
	public $schemaPath;

	public function __construct($schemaPath, $validationName, $message = '') {
		$this->validationName = $validationName;
		$this->schemaPath = $schemaPath;
		parent::__construct(sprintf('File input "%s" failed validation fn "%s" '.$message, $schemaPath, $validationName));
	}

}
