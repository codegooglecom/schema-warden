<?php
/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Dan Rummel
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
