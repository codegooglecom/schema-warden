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
 * @author John Smart
 */
namespace prescreen\utils;

use prescreen\exceptions\SchemaException;
use prescreen\exceptions\SchemaParseException;
use prescreen\exceptions\NewSchemaException;
use prescreen\exceptions\TypedSchemaException;
use prescreen\exceptions\InvalidSchemaException;
use prescreen\exceptions\SanitizeException;

use \lithium\util\Validator;

/**
 * Maps out an array into a schema set
 * 
 * @throws prescreen\exceptions\SchemaParseException
 *
 */
class SchemaMapper {

	protected static $_allTypes	= array('string', 'int', 'bool', 'array', 'date', 'float');

	protected $_name;

	protected $_data;

	protected $_schema	= array();

	/**
	 * Constructor
	 * @param $name
	 * @param $data
	 */
	protected function __construct($name, &$data) {
		$this->_name	= $name;
		$this->_data	= &$data;
	}

	/**
	 * Build a schema (e.g. set of paths) from a data set
	 *
	 * @static
	 * @param array& data		A reference to the data to map into a set of paths
	 * @param string $name 		Optional. A helpful name for the data, used for debugging only.
	 * @return array
	 */
	public static function exec(array $data, $name = '') {
		$mapper	= new SchemaMapper($name, $data);
		$mapper->_mapDataToSchema($mapper->_data);
		return array_keys($mapper->_schema);
	}

	/**
	 * create a schema map from data, mapping out all possible paths into a set paths.
	 * 
	 * @param $data
	 */
	protected function _mapDataToSchema($data) {

		$fn	= function(&$dataCursor, $symbol, $type, $pathSoFar, &$schema) use (&$fn) {
			if (!is_array($dataCursor)) {
				// base case
				$schema["{$pathSoFar}{$type}{$symbol}"]		= true;
			} else if (SchemaMapper::isArrayOfPrimitives($dataCursor)) {
				// array of primitives, also a base case
				$schema["{$pathSoFar}{$type}{$symbol}[]"]		= true;
			} else if (SchemaMapper::isArrayOfObjects($dataCursor)) {
				$indSchema	= array();
				foreach ($dataCursor as $index => &$indCursor) {
					$fn($indCursor, '', '', '', $indSchema);
				} unset($indCursor);

				foreach ($indSchema as $schemaPostfix => $ignore) {
					$schema["{$pathSoFar}{$type}{$symbol}[].{$schemaPostfix}"]	= true;
				}

			} else {
				// loop through properties
				$indType	= empty($symbol) ? '' : '.';
				foreach ($dataCursor as $propertyName => &$indCursor) {
					$fn($indCursor, $propertyName, $indType, "{$pathSoFar}{$type}{$symbol}", $schema);
				} unset($indCursor);
			}
		};

		$fn($data, '', '', '', $this->_schema);
	}

	/**
	 * Detect if a data set is an array of primitive data-types
	 * 
	 * @static
	 * @param array $dataToInspect		The data to inspect
	 * @return bool
	 */
	public static function isArrayOfPrimitives($dataToInspect) {
		if (is_null($dataToInspect) || !is_array($dataToInspect)) {
			return false;
		}

		// all keys must be integers, no values can be arrays
		foreach ($dataToInspect as $key => &$value) {
			if (is_array($value) || !is_int($key)) {
				return false;
			}
		} unset($value);

		return true;
	}

	/**
	 * Detect if a data set is an array of arrays, resembling an array of objects
	 * 
	 * @static
	 * @param array $dataToInspect		The data to inspect
	 * @return bool
	 */
	public static function isArrayOfObjects($dataToInspect) {
		if (is_null($dataToInspect) || !is_array($dataToInspect)) {
			return false;
		}

		$allNull	= true;
		foreach ($dataToInspect as $key => &$value) {
			if (!is_int($key)) {
				return false;
			} else if (is_array($value)) {
				$allNull	= false;
			} else if (!is_null($value)) {
				return false;
			}
		} unset($value);

		if ($allNull) {
			return false;
		}

		return true;
	}
}