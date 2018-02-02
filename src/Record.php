<?php

namespace Materia\EAV;

/**
 * Abstract EAV Record class
 *
 * @package	Materia.EAV
 * @author	Filippo Bovo
 * @link	https://lab.alchemica.io/projects/materia/
 **/

use \Materia\Data\SQL\Connection as Connection;
use \Materia\EAV\Records\EntityAttribute as EntityAttribute;

abstract class Record extends \Materia\Data\Record {

	protected static $_attributes = [];

	/**
	 * @see	\Materia\Data\Record::__construct()
	 **/
	public function __construct( Connection $connection ) {

		parent::__construct( $connection );

		// Build and cache attributes
		if ( !isset( static::$_attributes[static::NAME] ) ) {

			static::$_attributes[static::NAME] = [];

			$records = $this->_connection
			                   ->select()
			                   ->from( 'eav_entities_attributes' )
			                   ->join( 'eav_attributes', 'eav_entity_attribute_attribute', '=', 'eav_attribute_id' )
			                   ->join( 'eav_entities', 'eav_entity_attribute_entity', '=', 'eav_entity_id' )
			                   ->where( 'eav_entity_type', '=', static::class );

			if ( $records->execute( EntityAttribute::class, [ $connection ] ) ) {

				foreach ( $records as $record ) {

					$attribute = $record->attribute();
					$method    = str_replace( '_', '', ucwords( $attribute->name, '_' ) );

					static::$_properties[$attribute->name]      = $method;
					static::$_attributes[static::NAME][$method] = $record;

				}

			}

		}

	}

	/**
	 *
	 **/
	public function __call( string $method, array $args ) {

		$action = substr( $method, 0, 3 );
		$method = substr( $method, 3 );

		// Check if the attribute exists
		if ( isset( static::$_attributes[static::NAME][$method] ) ) {

			$record    = static::$_attributes[static::NAME][$method];
			$attribute = $record->attribute();

			// Load or create the attribute
			if ( ! isset( $this->_data[$attribute->name] ) ) {

				$this->_data[$attribute->name] = $record->loadValues( $this );

			}

			// Set attribute value(s)
			if ( ( $action == 'set' ) && isset( $args[0] ) ) {

				// One to many relationship
				if ( $attribute->limit != 1 ) {

					// Assign a specific value
					if ( isset( $args[1] ) ) {

						// New value
						if ( ! isset( $this->_data[$attribute->name][$args[1]] ) ) {

							$this->_data[$attribute->name][$args[1]] = $record->getEmptyValue();

						}

						// Set value
						$this->_data[$attribute->name][$args[1]]->setValue( $args[0] );

					}
					// Assign all values
					else if ( is_array( $args[0] ) ) {

						// Truncate current values
						$this->_data[$attribute->name] = array_slice( $this->_data[$attribute->name], 0, count( $args[0] ) );

						$count = 0;

						foreach ( $args[0] as $value ) {

							// Already a Value instance
							if ( is_object( $value ) && ( $value instanceof Value ) ) {

								$this->__call( 'set' . $method, $value->getValue(), $count );

							}
							// Raw value
							else {

								$this->__call( 'set' . $method, $value, $count );

							}

							$count++;

						}

					}

				}
				// One to one relationship
				else {

					// Set the value
					$this->_data[$attribute->name]->setValue( $args[0] );

				}

				return $this;

			}
			// Get attribute value(s)
			else if ( $action == 'get' ) {

				// One to many relationship
				if ( $attribute->limit != 1 ) {

					// Return scalar
					if ( ! isset( $args[0] ) || ( $args[0] == FALSE ) ) {

						return array_map( function( $a ) {

							       return $a->getValue();

						       }, $this->_data[$attribute->name] );

					}
					// Return Value object
					else {

						return $this->_data[$attribute->name];

					}

				}
				// One to one relationship
				else {

					if ( ! isset( $args[0] ) || ( $args[0] == FALSE ) ) {

						return $this->_data[$attribute->name]->getValue();

					}
					else {

						return $this->_data[$attribute->name];

					}

				}

			}

		}

	}

	/**
	 **/
	public function save( bool $force = FALSE ) : bool {

		$data = [];

		// Iterate over attributes
		foreach ( static::$_attributes[static::NAME] as $record ) {

			$attribute = $record->attribute();

			if ( isset( $this->_data[$attribute->name] ) ) {

				$data[$attribute->name] = $this->_data[$attribute->name];

				// One to one relationship: replace Value object with its scalar value
				if ( ! $attribute->shared && ( $attribute->limit == 1 ) ) {

					$this->_data[$attribute->name] = $data[$attribute->name]->getValue();

				}
				// One to many relationship: remove the value
				else {

					unset( $this->_data[$attribute->name] );

				}

			}

		}

		$return = parent::save( $force );

		// Save attributes
		foreach ( static::$_attributes[static::NAME] as $record ) {

			$record->saveValues( $this, $data[$attribute->name] );

		}

		// Merge back
		$this->_data = array_merge( $this->_data, $data );
		// $this->_data = $data + $this->_data;

		return $return;

	}

}
