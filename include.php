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

// NOTE: this file should be replaced by an autoloader

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