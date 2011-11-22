<?php
/**
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\exceptions;

/**
 * Thrown when a schema is missing required paths
 */
class RequiredSchemaException extends SchemaException {

	public function __construct($path, $name = '') {
		parent::__construct(sprintf('Missing required path: "%s" in "%s".', $path, $name));
	}
}