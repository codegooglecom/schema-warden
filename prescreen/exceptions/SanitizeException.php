<?php
/**
 * A file containing Prescreen exception classes
 *
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\exceptions;

/**
 * Thrown when sanitization fails
 */
class SanitizeException extends \Exception {
	public function __construct($propertyName, $fnName) {
		parent::__construct(sprintf('Failed to sanitize "%s" using "%s"."', $propertyName, $fnName));
	}
}
