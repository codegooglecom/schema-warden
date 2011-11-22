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