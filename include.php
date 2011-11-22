<?php
/**
 * NOTE: this file should be replaced by an autoloader
 *
 * @copyright	Copyright 2011, Prescreen, Inc. http://www.prescreen.com
 * @owner	John Smart <smart@prescreen.com>
 */

// these dependencies should be removed in a future version
require 'external/lithium/core/Object.php';
require 'external/lithium/core/StaticObject.php';
require 'external/lithium/util/Collection.php';
require 'external/lithium/util/collection/Filters.php';
require 'external/lithium/util/Set.php';
require 'external/lithium/util/Validator.php';

require 'prescreen/exceptions/SchemaException.php';
require 'prescreen/exceptions/ConsistencyException.php';
require 'prescreen/exceptions/InvalidFileException.php';
require 'prescreen/exceptions/InvalidSchemaException.php';
require 'prescreen/exceptions/NewSchemaException.php';
require 'prescreen/exceptions/RequiredSchemaException.php';
require 'prescreen/exceptions/SanitizeException.php';
require 'prescreen/exceptions/SchemaParseException.php';
require 'prescreen/exceptions/TypedSchemaException.php';

require 'prescreen/utils/SchemaMapper.php';
require 'prescreen/utils/SchemaQuery.php';
require 'prescreen/utils/SchemaWarden.php';