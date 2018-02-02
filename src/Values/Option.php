<?php

namespace Materia\EAV\Values;

/**
 * Option value class
 *
 * @package	Materia.EAV
 * @author	Filippo Bovo
 * @link	https://lab.alchemica.io/projects/materia/
 **/

use \Materia\Content\MVC\Views\HTML\Tag as Tag;
use \Materia\Content\MVC\Views\HTML\Tags as Tags;

class Option implements \Materia\EAV\Value {

	protected $_options = [];
	protected $_null    = FALSE;
	protected $_value   = NULL;

	/**
	 * @see \Materia\EAV\Value::__construct()
	 **/
	public function __construct( array $params = [] ) {

		if ( isset( $params['options'] ) && is_array( $params['options'] ) ) {

			$this->_options = $params['options'];

		}

		//
		if ( isset( $params['null'] ) && is_bool( $params['null'] ) ) {

			$this->_null = $params['null'];

		}

		// Set default value
		if ( isset( $params['default'] ) ) {

			$this->setValue( $params['default'] );

		}

	}

	/**
	 * @see	\Materia\EAV\Value::__toString()
	 **/
	public function __toString() : string {

		return ( string ) $this->getValue();

	}

	/**
	 * @see	\Materia\EAV\Value::setValue()
	 **/
	public function setValue( $value ) : \Materia\EAV\Value {

		if ( ! is_scalar( $value ) && ( $value !== NULL ) ) {

			return $this;

		}

		if ( isset( $this->_options[$value] ) ) {

			$this->_value = $value;

		}
		else if ( FALSE !== ( $key = array_search( $value, $this->_options ) ) ) {

			$this->_value = $key;

		}

		return $this;

	}

	/**
	 * @see	\Materia\EAV\Value::getValue()
	 **/
	public function getValue() {

		return $this->_value;

	}

	/**
	 *
	 **/
	public function getLabel() {

		if ( $this->isValid() ) {

			return $this->_options[$this->_value];

		}

	}

	/**
	 * @see	\Materia\EAV\Value::isValid()
	 **/
	public function isValid() : bool {

		if ( ( $this->_value !== NULL ) && isset( $this->_options[$this->_value] ) ) {

			return TRUE;

		}

		return FALSE;

	}

	/**
	 * @see	\Materia\EAV\Value::getFormElement()
	 **/
	public function getFormElement() : Tag {

		$input = new Tags\Select();

		foreach ( $this->_options as $key => $value ) {

			$input->append( ( new Tags\Option() )
			      ->setAttribute( 'value', $key )
			      ->setContent( $value )
			);
		}

		return $input;

	}

}
