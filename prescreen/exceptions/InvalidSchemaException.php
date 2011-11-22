<?php
/**
 * A file containing Prescreen exception classes
 *
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\exceptions;

/**
 * Thrown when schema fails validation
 */
class InvalidSchemaException extends SchemaException {
	
	public $validationName;
	public $schemaPath;
	
	public function __construct($schemaPath, $validationName) {
		$this->validationName = $validationName;
		$this->schemaPath = $schemaPath;
		parent::__construct(sprintf('Schema "%s" failed validation fn "%s"', $schemaPath, $validationName));
	}

}
