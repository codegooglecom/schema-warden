# Introduction #

Validates schemas.  This covers:

  * sanitization (e.g. homogenization, transformation) of passed data into a common format
  * validation of data sanity

The atomic operations are as follows:

  * test a required path - Tokenize and validate a set of paths that are required to be set
  * test undefined path - Test to see if there is a new path that is set but is not defined by the schema
  * detect type mismatch - Test for type mismatches at a specific path
  * validate a path against a closure function
  * sanitize a path using the return value from a closure function

# Details #

Example of a required path:

```
$schema	= array(
	'do.foo'	=> array('required' => true),
	're'		=> array('required' => true)
);
$data	= array('re' => false);
$required	= SchemaWarden::determineRequiredPaths($schema);

$warden		= new SchemaWarden($data);

// throws exception on validation error
$warden->testRequired($required);
```

Results:

```
array(2) {
  [0]=>
  string(6) "do.foo"
  [1]=>
  string(2) "re"
}
```