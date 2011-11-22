<?php
/**
 * Thrown when a schema fails to parse
 *
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\exceptions;

/**
 * Thrown when a schema definition fails to parse
 */
class SchemaParseException extends SchemaException {

	public function __construct($path, $name) {
		parent::__construct(sprintf('Schema "%s" failed to parse. Path: "%s"', $name, $path));
	}
}
