<?php
/**
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\exceptions;

/**
 * Thrown when data is inconsistent beyond automatic reconciliation
 */
class ConsistencyException extends \Exception {
	public function __construct($modelName, $id, $message) {
		parent::__construct(sprintf('Consistency exeception in "%s" with id "%s": %s', $modelName, $id, $message));
	}
}
