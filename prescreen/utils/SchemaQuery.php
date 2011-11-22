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

/**
 * Represents a query against a series of nested arrays using "Schema Syntax", a custom syntax (kinda of like XPATH)
 * for referencing data.
 *
 * An instance of SchemaQuery is immutable as it represents an already executed query.  Factory constructors provide
 * the query mechanism.
 * 
 * @throws prescreen\exceptions\SchemaParseException
 *
 */
class SchemaQuery {

	const ERR_WRONG_TYPE	= 'wrong_type';
	const ERR_UNDEFINED		= 'undefined';

	/**
	 * A helpful name for the query, used for debugging only.
	 * @var string
	 */
	protected $_name	= '';

	/**
	 * The query path
	 * @var string
	 */
	protected $_path	= '';

	/**
	 * The path symbols. Extracted during SchemaQuery->_tokenize()
	 * Example: array('www', 'google', 'com', 'docs')
	 * @var array
	 */
	protected $_pathSymbols	= array();

	/**
	 * Defines the type of each path symbol.  Extracted during SchemaQuery->_tokenize()
	 * Example: array('.', '.', '[].', '')
	 * @var array
	 */
	protected $_pathTypes	= array();

	/**
	 * The paths that were inspected.
	 *
	 * Structure:
	 * 		Keys: the paths that were inspected
	 * 		Values: mixed. true if found, or an ERR_* const if there was an error
	 *
	 * @var array
	 */
	protected $_pathInspection	= array();

	/**
	 * An array of references to any data found by the query
	 *
	 * Structure:
	 * 		Keys: the paths where data was found
	 * 		Values: either a reference to data found, or a null value if data at the path was undefined (miss)
	 *
	 * @var array
	 */
	protected $_results	= array();

	protected function __construct($name, $path) {
		$this->_name	= $name;
		$this->_path	= $path;
	}

	/**
	 * Execute a query, returning a SchemaQuery result
	 *
	 * @static
	 * @param string $path		The path to query $data with
	 * @param array& $data		A reference to the data to query, using $path
	 * @param string $name 		Optional. A helpful name for the query, used for debugging only.
	 * @return SchemaQuery
	 *
	 * @throws prescreen\exceptions\SchemaParseException
	 */
	public static function exec($path, array &$data, $name = '') {
		$q	= new SchemaQuery($name, $path);
		$q->_tokenize();
		$q->_mapPath($data);

		return $q;
	}

	/**
	 * Tokenize a path, testing it for validity without testing it against data.
	 *
	 * @static
	 * @param string $path		The path to query $data with
	 * @param string $name 		Optional. A helpful name for the query, used for debugging only.
	 * @throws prescreen\exceptions\SchemaParseException;
	 */
	public static function testPath($path, $name = '') {
		$q	= new SchemaQuery($name, $path);
		$q->_tokenize();
	}

	/**
	 * If the query hit ANY values, returns true
	 * @return boolean
	 */
	public function hasHit() {
		if (empty($this->_pathInspection)) {
			return false;
		}

		foreach ($this->_pathInspection as $path => $isHit) {
			if ($isHit === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Some queries may reference multiple paths.  This will return true if ALL paths were hits.
	 * @return bool
	 */
	public function hasHitAll() {
		if (empty($this->_pathInspection)) {
			return false;
		}

		foreach ($this->_pathInspection as $path => $isHit) {
			if ($isHit !== true) {
				return false;
			}
		}

		return true;
	}

	/**
	 * If there is a miss-match between the queried path and the data set, then this method will return true.
	 * This is important for situations where arrays are expected, but another type is found.
	 * @return bool
	 */
	public function hasTypeMissMatch() {
		foreach ($this->_pathInspection as $path => $status) {
			if ($status === self::ERR_WRONG_TYPE) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return all paths that were missed
	 * @return array
	 */
	public function getMissedPaths() {
		$missedPaths	= array();
		foreach ($this->_pathInspection as $path => $isHit) {
			if ($isHit !== true) {
				$missedPaths[]	= $path;
			}
		}

		return $missedPaths;
	}

	/**
	 * Get the reason code for the path miss
	 * @param string $path		A path from SchemaQuery->getMissedPaths()
	 * @return string|null		A SchemaQuery::ERR_* const explaining how the path was missed.
	 */
	public function getMissReason($path) {
		if (!isset($this->_pathInspection[$path])) {
			return null;
		}

		return $this->_pathInspection[$path];
	}

	/**
	 * Get all paths that were hits
	 * @return array
	 */
	public function getResultPaths() {
		$hitPaths	= array();
		foreach ($this->_pathInspection as $path => $isHit) {
			if ($isHit === true) {
				$hitPaths[]	= $path;
			}
		}

		return $hitPaths;
	}

	/**
	 * Get a list of the results of the query, contained in an array passed-by-value.
	 *
	 * NOTE: even if there is only only one result, it will be contained within an array for consistency.
	 *
	 * NOTE: members of the result will always be passed-by-reference unless path requested the members of an array.
	 * 			In the array-members case, only the members will be passed by reference
	 * 			Example: if path is "groceries[]", result will be array(array(string& "milk", string& "eggs"))
	 * 			Example: if path is "groceries", result will be array(array&("milk, "eggs"))
	 *
	 * @return array
	 */
	public function getResults() {
		return $this->_results;
	}

	/**
	 * Tokenize a path
	 *
	 * @static
	 * @throws prescreen\exceptions\SchemaParseException;
	 */
	protected function _tokenize() {
		if ($this->_path === '') {
			$this->_pathSymbols	= array();
			$this->_pathTypes	= array();
			return;
		}

		preg_match_all('/([a-zA-Z0-9_]+)(\.|\[\]\.|\[\])?/', $this->_path, $matches);
		$symboles	= $matches[1];
		$types		= $matches[2];

		if (empty($symboles) && empty($types)) {
			throw new SchemaParseException($this->_path, $this->_name);
		} else if (count($symboles) != count($types)) {
			// each symbol must have an implied type
			throw new SchemaParseException($this->_path, $this->_name);
		}

		// check for invalid types
		for ($i = 0; $i < count($types); ++$i) {
			$type	= $types[$i];
			if ($i < count($types) - 1) {
				if ($type === '[]' || $type === '') {
					throw new SchemaParseException($this->_path, $this->_name);
				}
			} else {
				if ($type === '[].' || $type === '.') {
					throw new SchemaParseException($this->_path, $this->_name);
				}
			}
		}

		$this->_pathSymbols	= $symboles;
		$this->_pathTypes	= $types;
	}

	/**
	 * Map a path to a data reference, throwing an exception if path is not found.
	 *
	 * @static
	 * @param array& $data		A reference to the data to query
	 *
	 * @return array
	 *
	 * @throws prescreen\exceptions\SchemaParseException;
	 */
	protected function _mapPath(array& $data) {
		$this->_tokenize();
		if (empty($this->_pathSymbols)) {
			// root
			$this->_pathInspection['']	= true;
			$this->_results[]			= &$data;
			return;
		}

		// setup closure references
		$result				= array();
		$pathInspection		= array();
		$name				= $this->_name;

		// a function to move through each node, according to the structure that the $path defines
		$fn		= function(&$dataCursor, array $types, array $symbols, array $pathSoFar, $incUndefined = false) use (&$fn, &$result, &$pathInspection, &$name) {
			$symbol			= array_shift($symbols);
			$type			= array_shift($types);
			$pathSoFar[]	= $symbol;

			// check for symbol existence
			if (!is_array($dataCursor) || !array_key_exists($symbol, $dataCursor)) {
				// there is no data at the requested path...
				$pathStr	= implode('', $pathSoFar);
				$pathInspection[$pathStr]	= SchemaQuery::ERR_UNDEFINED;
				if ($incUndefined) {
					$result[]					= null;
				}
				return;
			}

			$dataCursor	= &$dataCursor[$symbol];

			if (is_null($dataCursor)) {
				// path is defined, but is null

				$pathStr		= implode('', $pathSoFar) . $type;
				$pathInspection[$pathStr]	= true;
				$result[]					=& $dataCursor;
				return;

			}

			// analyze based on node's $type

			if ($type === '') {
				// base-case, a single value

				$pathStr		= implode('', $pathSoFar);
				$pathInspection[$pathStr]	= true;
				$result[]					=& $dataCursor;

				return;

			} else if ($type === '[]') {
				// base-case, a flat array

				if (!is_array($dataCursor) || !SchemaQuery::_isArrayNumericallyIndexed($dataCursor)) {
					// expected an array, but found something else...
					$pathStr		= implode('', $pathSoFar) . '[]';
					throw new TypedSchemaException($pathStr, 'numerical-array', $dataCursor);
//					$pathStr		= implode('', $pathSoFar) . '[]';
//					$pathInspection[$pathStr]	= SchemaQuery::ERR_WRONG_TYPE;
//					$result[]					= null;
//					return;

				}

				// inspect each array member
				for ($i = 0; $i < count($dataCursor); ++$i) {
					$pathStr	= implode('', $pathSoFar) . "[{$i}]";
					$pathInspection[$pathStr]	= true;
					$result[]					=& $dataCursor[$i];
				}

				return;

			} else if ($type === '[].') {
				// an array of objects, requires recursion

				if (!is_array($dataCursor) || !SchemaQuery::_isArrayNumericallyIndexed($dataCursor)) {
					// expected an array of objects, but found something else...

					$pathStr		= implode('', $pathSoFar) . '[].';
					$pathInspection[$pathStr]	= SchemaQuery::ERR_WRONG_TYPE;
					$result[]					= null;
					return;

				}

				// induction step -- inspect each member of the array (members are objects, requiring recursion)
				for ($i = 0; $i < count($dataCursor); ++$i) {
					$indCursor		= &$dataCursor[$i];
					if (is_null($indCursor)) {
						// skip null objects
						$pathStr	= implode('', $pathSoFar);
						$pathInspection[$pathStr]	= true;
						continue;
					}
					$fn($indCursor, $types, $symbols, array_merge($pathSoFar, array("[{$i}].")), true);
				}
				return;

			} else if ($type === '.') {
				// an object, requires recursion

				$pathSoFar[]	= '.';
				$fn($dataCursor, $types, $symbols, $pathSoFar, $incUndefined);

				return;
			}

			$pathStr		= implode('', $pathSoFar) . $type;
			throw new SchemaParseException($pathStr, $name);
//			$pathStr		= implode('', $pathSoFar) . $type;
//			$pathInspection[$pathStr]	= SchemaQuery::ERR_WRONG_TYPE;
//			$result[]					= null;
		};

		// start the madness
		$dataCursor			= &$data;
		$symbols			= $this->_pathSymbols;
		$types				= $this->_pathTypes;

		$fn($dataCursor, $types, $symbols, array());

		$this->_results			= $result;
		$this->_pathInspection	= $pathInspection;
	}

	/**
	 * Check all indexes of an array to make sure that it is 100% indexed by integers.
	 * Otherwise, the query routine will hange the httpd process!
	 * 
	 * @static
	 * @param array $array
	 * @return bool			True if numerically indexed.
	 */
	public static function _isArrayNumericallyIndexed(array $array) {
		for ($i = 0; $i < count($array); ++$i) {
			if (!array_key_exists($i, $array)) {
				return false;
			}
		}

		return true;
	}
}