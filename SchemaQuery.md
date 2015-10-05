# Introduction #

Represents a query against a series of nested arrays using "Schema Syntax", a custom syntax (kinda of like XPATH) for referencing data.

An instance of SchemaQuery is immutable as it represents an already executed query.  Factory constructors provide the query mechanism.


# Details #

Example:

```
$data 	= array(
	'nullThing'		=> null,
	'nullThings'	=> array(null, null),
	'goodThings'	=> 'puppies',
	'grayThings'	=> array('red lights', 'cash'),
	'evilThings'	=> array(
		'humanoid'	=> array('goblins', 'trolls', 'orcs'),
		'spirit'		=> array('shades', 'ghosts', 'demons'),
		'magic'		=> array(
			array('type' => 'blood', 'risk' => 'high', 'karma' => -1),
			array('type' => 'poison', 'garbage' => 'bleh')
		)
	)
);

$q	= SchemaQuery::exec('evilThings.magic[]', $data);
var_dump($q->getResults());

$q	= SchemaQuery::exec('evilThings.magic[].risk', $data);
var_dump($q->getResults());
```

Outputs:

```
array(2) {
  [0]=>
  array(3) {
    ["type"]=>
    string(5) "blood"
    ["risk"]=>
    string(4) "high"
    ["karma"]=>
    int(-1)
  }
  [1]=>
  array(2) {
    ["type"]=>
    string(6) "poison"
    ["garbage"]=>
    string(4) "bleh"
  }
}
array(2) {
  [0]=>
  string(4) "high"
  [1]=>
  NULL
}
```