<?php
/**
 * A file containing Prescreen exception classes
 *
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\exceptions;

/**
 * Thrown when a schema type-check fails
 */
class TypedSchemaException extends SchemaException {

	public function __construct($propertyName, $expectedType, $value) {
		$type	= is_object($value) ? get_class($value) : gettype($value);
		parent::__construct(sprintf('Failed type check: "%s". Expected: "%s". Actual "%s".', $propertyName, $expectedType, $type));
	}
}