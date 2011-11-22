<?php
/**
 * A file containing Prescreen exception classes
 *
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\exceptions;

/**
 * Thrown when new schema is introduced without it being described in the Descriptor::$_schema array
 */
class NewSchemaException extends SchemaException {
	
	public $propertyName = null;
	
	public function __construct($schemaName, $propertyName) {
		$this->propertyName = $propertyName;
		parent::__construct(sprintf('Schema "%s" does not define the path "%s"', $schemaName, $propertyName));
	}

}
