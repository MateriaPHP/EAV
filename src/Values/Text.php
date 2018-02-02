<?php

namespace Materia\EAV\Values;

/**
 * Text value class
 *
 * @package	Materia.EAV
 * @author	Filippo Bovo
 * @link	https://lab.alchemica.io/projects/materia/
 **/

use \Materia\Content\MVC\Views\HTML\Tag as Tag;
use \Materia\Content\MVC\Views\HTML\Tags as Tags;

class Text implements \Materia\EAV\Value {

	protected $_null  = FALSE;
	protected $_value = NULL;

	/**
	 * @see \Materia\EAV\Value::__construct()
	 **/
	public function __construct( array $params = [] ) {

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

		if ( ! is_string( $value ) && ( $value !== NULL ) ) {

			return $this;

		}

		$this->_value = $value;

		return $this;

	}

	/**
	 * @see	\Materia\EAV\Value::getValue()
	 **/
	public function getValue() {

		return $this->_value;

	}

	/**
	 * @see	\Materia\EAV\Value::isValid()
	 **/
	public function isValid() : bool {

		return TRUE;

	}

	/**
	 * @see	\Materia\EAV\Value::getFormElement()
	 **/
	public function getFormElement() : Tag {

		$input = new Tags\Text();

		return $input;

	}

}
