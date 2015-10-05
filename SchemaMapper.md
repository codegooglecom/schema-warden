# Introduction #

Maps out an array into a schema set.  This is an array where where a reference to each data member is turned into Schema Warden's dot-notation query syntax.

# Details #

Example:

```
$data		= array(
	'piece'	=> array(
		'name'		=> 'Mozarts 9th',
		'measures'	=> array(
			array(
				'notes'	=> array(1, 2, 3),
				'intensity'	=> 10
			),
			array(
				'notes'	=> array(1, 4, 5),
				'intensity'	=> 7
			),
			array(
				'notes'	=> array(2, 3, 4, 5),
				'intensity'	=> 5
			)
		)
	)
);

$schema	= SchemaMapper::exec($data);
var_dump($schema);
```

Results:

```
array(3) {
  [0]=>
  string(10) "piece.name"
  [1]=>
  string(24) "piece.measures[].notes[]"
  [2]=>
  string(26) "piece.measures[].intensity"
}
```