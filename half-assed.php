<?php
/**
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

require 'include.php';

use \prescreen\utils\SchemaMapper;
use \prescreen\utils\SchemaQuery;
use \prescreen\utils\SchemaWarden;

$data           = array(
		'piece' => array(
				'name'          => 'Mozarts 9th',
				'measures'      => array(
						array(
								'notes' => array(1, 2, 3),
								'intensity'     => 10
						),
						array(
								'notes' => array(1, 4, 5),
								'intensity'     => 7
						),
						array(
								'notes' => array(2, 3, 4, 5),
								'intensity'     => 5
						)
				)
		)
);

$schema = SchemaMapper::exec($data);
var_dump($schema);

$data   = array(
        'nullThing'             => null,
        'nullThings'    => array(null, null),
        'goodThings'    => 'puppies',
        'grayThings'    => array('red lights', 'cash'),
        'evilThings'    => array(
                'humanoid'      => array('goblins', 'trolls', 'orcs'),
                'spirit'                => array('shades', 'ghosts', 'demons'),
                'magic'         => array(
                        array('type' => 'blood', 'risk' => 'high', 'karma' => -1),
                        array('type' => 'poison', 'garbage' => 'bleh')
                )
        )
);

$q      = SchemaQuery::exec('evilThings.magic[]', $data);
var_dump($q->getResults());

$q      = SchemaQuery::exec('evilThings.magic[].risk', $data);
var_dump($q->getResults());

$schema = array(
        'do.foo'        => array('required' => true),
        're'            => array('required' => true)
);
$data   = array('re' => false);
$required       = SchemaWarden::determineRequiredPaths($schema);

$warden         = new SchemaWarden($data);

// throws exception on validation error
$warden->testRequired($required);

var_dump($required);