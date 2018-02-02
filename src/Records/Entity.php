<?php

namespace Materia\EAV\Records;

/**
 * Abstract Entity class
 *
 * @package	Materia.EAV
 * @author	Filippo Bovo
 * @link	https://lab.alchemica.io/projects/materia/
 **/

use \Materia\Data\SQL\Connection as Connection;

class Entity extends \Materia\Data\Record {

	const NAME = 'eav_entity';
	const TABLE = 'eav_entities';
	const PREFIX = 'eav_entity_';
	const PRIMARY_KEY = 'id';

}
