<?php

namespace Materia\EAV\Records;

/**
 * EntityAttribute class
 *
 * @package	Materia.EAV
 * @author	Filippo  Bovo
 * @link	https://lab.alchemica.io/projects/materia/
 **/

use \Materia\EAV\Record as Record;

class EntityAttribute extends \Materia\Data\Record {

	const NAME             = 'eav_entity_attribute';
	const TABLE            = 'eav_entity_attributes';
	const PREFIX           = 'eav_entity_attribute_';
	const PREFIX_ENTITY    = 'eav_entity_';
	const PREFIX_ATTRIBUTE = 'eav_attribute_';
	const PRIMARY_KEY      = 'id';

	protected $_entity;
	protected $_attribute;

	protected static $_properties = [
		'attribute' => 'Attribute',
		'entity'    => 'Entity',
	];

	/**
	 * @see	\Materia\Data\Record::__construct()
	 **/
	public function __construct( \Materia\Data\SQL\Connection $connection ) {

		parent::__construct( $connection );

	}

	/**
	 * @see		\Materia\Data\Record::offsetSet()
	 **/
	public function offsetSet( $offset, $value ) {

		if ( strpos( $offset, static::PREFIX ) !== 0 ) {

			if ( strpos( $offset, static::PREFIX_ENTITY ) === 0 ) {

				return $this->entity( FALSE )->offsetSet( $offset, $value );

			}
			else if ( strpos( $offset, static::PREFIX_ATTRIBUTE ) === 0 ) {

				return $this->attribute( FALSE )->offsetSet( $offset, $value );

			}

		}

		return parent::offsetSet( $offset, $value );

	}

	/**
	 * Returns Entity as instance
	 *
	 * @param	boolean	$load	whatever load from database or not
	 * @return	Entity
	 **/
	public function &entity( bool $load = TRUE ) {

		// Initialize new object
		if ( ! isset( $this->_entity ) ) {

			$this->_entity = new Entity( $this->_connection );

		}

		// Load from database
		if ( $load && ! isset( $this->_entity->id ) && isset( $this->_data['entity'] ) ) {

			$this->_entity->load( $this->_data['entity'] );

		}

		return $this->_entity;

	}

	/**
	 * Get Entity ID
	 *
	 * @return	int
	 **/
	public function getEntity() {

		return $this->_data['entity'];

	}

	/**
	 * Set Entity
	 *
	 * @param	mixed	$entity		Entity instance or ID
	 **/
	public function setEntity( $entity ) {

		if ( $entity instanceof Entity ) {

			$this->_entity = $entity;
			$entity        = $entity->id;

		}

		$this->_data['entity'] = $entity;

	}

	/**
	 * Returns Attribute as instance
	 *
	 * @param	boolean	$load	whatever load from database or not
	 * @return	Attribute
	 **/
	public function &attribute( bool $load = TRUE ) {

		// Initialize new object
		if ( ! isset( $this->_attribute ) ) {

			$this->_attribute = new Attribute( $this->_connection );

		}

		// Load from database
		if ( $load && ! isset( $this->_attribute->id ) && isset( $this->_data['attribute'] ) ) {

			$this->_attribute->load( $this->_data['attribute'] );

		}

		return $this->_attribute;

	}

	/**
	 * Get Attribute ID
	 *
	 * @return	int
	 **/
	public function getAttribute() {

		return $this->_data['attribute'];

	}

	/**
	 * Set Attribute
	 *
	 * @param	mixed	$attribute	Attribute instance or ID
	 **/
	public function setAttribute( $attribute ) {

		if ( $attribute instanceof Attribute ) {

			$this->_attribute = $attribute;
			$attribute        = $attribute->id;

		}
		else if ( isset( $this->_attribute ) && ( $this->_attribute->id != $attribute ) ) {

			unset( $this->_attribute );

		}

		$this->_data['attribute'] = $attribute;

	}

	public function getEmptyValue() {

		$attribute = $this->attribute();

		return new $attribute->type( $attribute->getParams( TRUE, TRUE ) );

	}

	/**
	 * Load enity's values
	 *
	 * @param	Materia\EAV\Record	$record
	 * @return	Value
	 **/
	public function loadValues( Record $record ) {

		$attribute = $this->attribute();

		// Load from database
		if ( $attribute->shared || ( $attribute->limit != 1 ) ) {

			$values = $this->_connection
			               ->select()
			               ->from( 'eav_values_' . $this->hash )
			               ->where( 'eav_value_entity', '=', $record->id );

			// One to many relationship
			if ( $attribute->limit != 1 ) {

				$values->execute( Value::class, [ $this->_connection, $this ] );

				return iterator_to_array( $values->getIterator(), FALSE );

			}
			// One to one relationship
			else if ( $value = $values->first( Value::class, [ $this->_connection, $this ] ) ) {

				return $value;

			}

		}
		else {

			return $this->getEmptyValue();

		}

	}

	public function saveValues( Record $record, array $values = [] ) : bool {

		$attribute = $this->attribute();

		if ( $attribute->shared || ( $attribute->limit != 1 ) ) {

			// Clean database
			$this->_connection
			     ->delete( 'eav_values_' . $this->hash )
			     ->where( 'eav_value_entity', '=', $record->id )
			     ->execute();

			// TODO: atomic update
			foreach ( $values as &$value ) {

				// Already a record
				if ( $value instanceof Value ) {

					$value->save( TRUE );

				}
				// Value instance
				else if ( is_object( $value ) && ( get_class( $value ) == $attribute->type ) ) {

					$value = new Value( $this->_connection, $value, $this->hash );

					$value->save();

				}
				// Raw value ??
				else {

					$record = new Value( $this->_connection, $this );

					$record->setValue( $value );
					$record->save();

					$value = $record;

				}

			}

		}

		return TRUE;

	}

}
