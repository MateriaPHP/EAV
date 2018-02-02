<?php

namespace Materia\EAV\Records;

/**
 * Abstract Attribute class
 *
 * @package	Materia.EAV
 * @author	Filippo Bovo
 * @link	https://lab.alchemica.io/projects/materia/
 **/

class Attribute extends \Materia\Data\Record {

	const NAME        = 'eav_attribute';
	const TABLE       = 'eav_attributes';
	const PREFIX      = 'eav_attribute_';
	const PRIMARY_KEY = 'id';

	protected static $_properties = [
		'params' => 'Params',
	];

	/**
	 * Get attribute params
	 *
	 * @param	bool	$decode		if TRUE, returns the object decoded from JSON string
	 * @param	bool	$array
	 * @return	mixed
	 **/
	public function getParams( bool $decode = FALSE, bool $array = FALSE ) {

		if ( isset( $this->_data['params'] ) ) {

			return $decode ? json_decode( $this->_data['params'], $array ) : $this->_data['params'];

		}

	}

	/**
	 * Set attribute params
	 *
	 * @param	mixed	$params
	 **/
	public function setParams( $params ) {

		if ( is_string( $params ) ) {

			$this->_data['params'] = $params;

		}
		else if ( is_array( $params ) || is_object( $params ) ) {

			$this->_data['params'] = json_encode( $params );

		}

	}

}
