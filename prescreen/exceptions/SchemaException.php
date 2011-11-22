<?php
/**
 * A file containing Prescreen exception classes
 *
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\exceptions;

/**
 * The exception thrown when the data layer experiences an unreconciled discrepancy between the
 * parameters passed to it and the rules inherit to the underlying schema.
 */
class SchemaException extends \Exception {
}
