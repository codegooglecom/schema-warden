<?php
/**
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

namespace prescreen\utils;

use \lithium\util\Validator;

use prescreen\exceptions\SchemaException;
use prescreen\exceptions\SchemaParseException;
use prescreen\exceptions\NewSchemaException;
use prescreen\exceptions\TypedSchemaException;
use prescreen\exceptions\InvalidSchemaException;
use prescreen\exceptions\SanitizeException;
use prescreen\exceptions\RequiredSchemaException;

use prescreen\utils\SchemaQuery;
use \prescreen\utils\SchemaMapper;

/**
 * Validates schemas.
 *
 * This covers:
 * 		* sanitization (e.g. homogenization) of passed data into a common format
 * 		* validation of data sanity
 *
 * @throws SchemaException		Thrown when schema checks fail on passed data
 */
class SchemaWarden {

	protected $_queries	= array();

	/**
	 * A helpful name to describe the schema. Used for debugging only.
	 * @var string
	 */
	protected $_name;

	/**
	 * The data to ward over
	 * @var array&
	 */
	protected $_data;

	/**
	 * Contruct a SchemaWarden in order to validate a single schema
	 * @param array& $data		The data to warden.
	 * @param string $name		Optional. A helpful name to describe the schema. Used for debugging only.
	 */
	public function __construct(array& $data, $name = '') {
		$this->_data	=& $data;
		$this->_name	= $name;
	}

	/**
	 * Test that all $requiredPaths are defined in data
	 *
	 * @throws prescreen\exceptions\SchemaParseException
	 * 
	 * @static
	 * @param array $requiredPaths		An array of string paths to data in $data
	 */
	public function testRequired(array $requiredPaths) {
		foreach ($requiredPaths as $path) {
			$query	= $this->_getQuery($path);
			if (!$query->hasHitAll()) {
				$missedPaths	= $query->getMissedPaths();
				foreach ($missedPaths as $missedPath) {
					if (in_array($missedPath, $requiredPaths, true)) {
						throw new RequiredSchemaException($missedPath, $this->_name);
					}
				}
			}
		}
	}

	/**
	 * Test that there are new unexepected data paths
	 *
	 * @throws \prescreen\exceptions\NewSchemaException
	 *
	 * @param array $definedPaths
	 * @return void
	 */
	public function testNewSchema(array $definedPaths) {
		$actualPaths	= SchemaMapper::exec($this->_data, $this->_name);
		foreach ($actualPaths as $path) {
			if (!in_array($path, $definedPaths, true)) {
				throw new NewSchemaException($this->_name, $path);
			}
		}
	}

	/**
	 * Type-check data
	 * 
	 *
	 * @param array $typeSchema			The type schema
	 * @param bool $castNumerics		Set false to disable automatically casting numeric strings to integers.
	 *
	 * @throws \prescreen\exceptions\SchemaParseException|\prescreen\exceptions\TypedSchemaException
	 */
	public function testTypes(array $typeSchema, $castNumerics = true) {
		foreach ($typeSchema as $path => $type) {
			if (!Validator::isDescriptorSchemaType($type)) {
				// not a valid schema type
				throw new SchemaParseException($path, $this->_name);
			}

			$query		= $this->_getQuery($path);
			if ($query->hasTypeMissMatch()) {
				throw new SchemaParseException($path, $this->_name);
			}

			$results	= $query->getResults();
			
			foreach ($results as &$value) {
				if (is_null($value)) {
					// null could be any type
					continue;
				}

				if ($castNumerics) {
					if ($type === 'int' || $type === 'integer') {
						if (is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false) {
							// cast to int
							$value	= intval($value);
						} else if ($value === '') {
							$value	= null;
						}
					} else if ($type === 'float' || $type === 'double') {
						if (is_string($value) && filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
							// cast to float
							$value	= floatval($value);
						} else if ($value === '') {
							$value	= null;
						}
					} else if ($type === 'bool' || $type === 'boolean') {
						if (is_int($value)) {
							// cast to float
							$value	= !!$value;
						} else if (is_string($value) && filter_var($value, FILTER_VALIDATE_BOOLEAN) !== false) {
							// cast to float
							$value	= !!$value;
						} else if ($value === '') {
							$value	= false;
						}
					} else if ($type === 'string') {
						if (is_scalar($value)) {
							$value	= strval($value);
						}
					}
				}

				if (!is_null($value) && !Validator::isMongoType($value, $type)) {
					throw new TypedSchemaException("{$this->_name}.{$path}", $type, $value);
				}
			} unset($value);
		}
	}

	/**
	 * Validate data according to rules in the passed schema
	 * 
	 * @param array $validationSchema		The validation rules, keyed by path.
	 *
	 * @throws \prescreen\exceptions\InvalidSchemaException
	 */
	public function validate(array $validationSchema) {
		foreach ($validationSchema as $path => $rules) {
			$query		= $this->_getQuery($path);
			$results	= $query->getResults();

			foreach ($results as $value) {
				foreach ($rules as $rule => $options) {
					$this->_validateField($path, $rule, $options, $value);
				}
			}
		}
	}

	/**
	 * Sanitize data according to rules in the passed schema
	 *
	 * NOTE: this will mutate $this->_data
	 *
	 * @param array $sanitizingSchema		The sanitize rules, keyed by path.
	 */
	public function sanitize(array $sanitizingSchema) {
		foreach ($sanitizingSchema as $path => $rules) {
			$query		= $this->_getQuery($path);
			$results	= $query->getResults();

			foreach ($results as &$value) {
				foreach ($rules as $rule => $options) {
					$this->_sanitizeField($path, $rule, $options, $value);
				}
			} unset($value);
		}
	}

	/**
	 * Create a SchemaQuery object or reuse one that has already executed.
	 *
	 * @param string $path		The path to query $data with
	 * 
	 * @return SchemaQuery
	 */
	protected function _getQuery($path) {
		if (isset($this->_queries[$path])) {
			return $this->_queries[$path];
		}

		return SchemaQuery::exec($path, $this->_data, $this->_name);
	}

	/**
	 * From a set of validation rules, determine which paths are required.
	 * 
	 * @static
	 * @param array $validationSchema		Validation schema, where keys are the paths and values are the rules to validate with.
	 * @return array		Array of paths which are required fields.
	 */
	public static function determineRequiredPaths(array $validationSchema) {
		$requiredPaths	= array();
		foreach ($validationSchema as $path => $rules) {
			if (isset($rules['required']) && $rules['required']) {
				$requiredPaths[]	= $path;
			}
		}
		return $requiredPaths;
	}

	/**
	 * Run validation of a single field.
	 * NOTE: does not handle the "required" rule
	 *
	 * @static
	 * @param string $path			The path of the field
	 * @param string $rule			The rule to execute. @see: Validator
	 * @param mixed $options		Validation options.
	 * @param mixed $value			The data to validate
	 *
	 * @throws \prescreen\exceptions\InvalidSchemaException
	 */
	protected function _validateField($path, $rule, $options, $value) {
		if ($rule === 'required') {
			// does not handle this rule
			return;
		}

		if ($options === true) {
			$options	= array();
		} else if ($options === false || is_null($options)) {
			// do not validate
			return;
		} else if (!is_array($options)) {
			$options	= array('format' => $options);
		}

		$format = isset($options['format']) ? $options['format'] : 'any';

		if (!Validator::rule($rule, $value, $format, $options)) {
			throw new InvalidSchemaException("{$this->_name}.{$path}", $rule);
		}
	}

	/**
	 * Run sanitizing routines on a single field.
	 *
	 * @param string $path			The path of the field
	 * @param string $rule			The rule to execute. @see: Sanitize
	 * @param mixed $options		Sanitize options.
	 * @param mixed $value			The data to validate
	 * 
	 * @throws \prescreen\exceptions\SanitizeException
	 */
	protected function _sanitizeField($path, $rule, $options, &$value) {
		$value	= Sanitize::toModelSafe($value);

		if ($options === false) {
			// skip if options set to false.
			return;
		}

		if (!method_exists('\prescreen\utils\Sanitize', $rule)) {
			throw new SanitizeException("{$this->_name}.$path", $rule);
		}

		if (!is_array($options)) {
			$options	= array();
		}

		$value	= Sanitize::$rule($value, $options);
	}
}

/**
 * Validates descriptor schema types
 */
Validator::add('descriptorSchemaType', '/^(string|int|integer|bool|boolean|float|double|date){1}$/i');

/**
 * Validates Mongo field names
 */
Validator::add('mongoPropertyName', '/^[a-z0-9_]{1,20}$/i');

/**
 * Validates Mongo field names
 */
Validator::add('mongoType', function($check, $format) {
	if (Validator::isType($check, $format)) {
		return true;
	}

	switch($format) {
		case 'date':
			return $check instanceof \MongoDate;
	}

	return false;
});