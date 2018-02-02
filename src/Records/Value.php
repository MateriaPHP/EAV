<?php

namespace Materia\EAV\Records;

/**
 * Value Record class
 *
 * @package	Materia.EAV
 * @author	Filippo Bovo
 * @link	https://lab.alchemica.io/projects/materia/
 **/

class Value extends \Materia\Data\Record {

	const NAME = 'eav_value';
	const TABLE = 'eav_values_';
	const PREFIX = 'eav_value_';
	const PRIMARY_KEY = 'id';

	protected $_value;
	protected $_reference;

	protected static $_properties = [
		'value' => 'Value',
	];

	/**
	 * Constructor
	 *
	 * @param	array	$params
	 **/
	public function __construct( \Materia\Data\SQL\Connection $connection, \Materia\EAV\Records\EntityAttribute $record ) {

		parent::__construct( $connection );

		$this->_value     = $record->getEmptyValue();
		$this->_reference = $record->hash;

	}

	/**
	 * Delegate to Value
	 **/
	public function __call( $method, $args ) {

		return call_user_func_array( [ $this->_value, $method ], $args );

	}

	/**
	 * Returns Value instance
	 *
	 * @return	\Materia\EAV\Value
	 **/
	public function value() {

		return $this->_value;

	}

	/**
	 * @see \Materia\Data\Record::save()
	 **/
	public function save( bool $force = FALSE ) : bool {

		$data = [
			static::PREFIX . 'value' => $this->_value->getValue(),
		];

		// Prepend prefix (foreach seems to be the fastest)
		foreach ( $this->_data as $key => $value ) {

			if ( $key != static::PREFIX ) {

				$key        = static::PREFIX . $key;
				$data[$key] = $value;

			}

		}

		// Insert
		if ( $force || ! $this->offsetExists( static::PRIMARY_KEY ) ) {

			if ( $this->offsetExists( static::PRIMARY_KEY ) ) {

				$id = $this->_connection
				           ->insert()
				           ->ignore()
				           ->into( static::TABLE . $this->_reference )
				           ->values( $data )
				           ->execute();

			}
			else {

				$id = $this->_connection
				           ->insert()
				           ->into( static::TABLE . $this->_reference )
				           ->values( $data )
				           ->execute();

			}

			if ( $id && ! $this->offsetExists( static::PRIMARY_KEY ) ) {

				$this->offsetSet( static::PRIMARY_KEY, $id );

				return TRUE;

			}

		}
		// Update
		else {

			$rows = $this->_connection
			             ->update( static::TABLE . $this->_reference )
			             ->values( $data )
			             ->where( static::PREFIX . static::PRIMARY_KEY, '=', $this->offsetGet( static::PRIMARY_KEY ) )
			             ->execute();

			if ( $rows !== FALSE ) {

				return TRUE;

			}

		}

		return FALSE;

	}

	/**
	 * @see \Materia\Data\Record::load()
	 **/
	public function load( $id ) : bool {

		$record = $this->_connection
		               ->select()
		               ->from( static::TABLE . $this->_reference )
		               ->where( static::PREFIX . static::PRIMARY_KEY, '=', $id )
		               ->first( static::class, [ $this->_connection ] );

		if ( $record ) {

			$this->_data = $record->_data;

			return TRUE;

		}

		return FALSE;

	}

	/**
	 * @see \Materia\Data\Record::remove()
	 **/
	public function remove() : bool {

		if ( $this->offsetExists( static::PRIMARY_KEY ) ) {

			return $this->_connection
			            ->delete()
			            ->from( static::TABLE . $this->_reference )
			            ->where( static::PREFIX . static::PRIMARY_KEY, '=', $this->offsetGet( static::PRIMARY_KEY ) )
			            ->execute() ? TRUE : FALSE;

		}

		return FALSE;

	}

}
