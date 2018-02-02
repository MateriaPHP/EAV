<?php

namespace Materia\EAV;

/**
 * Value interface
 *
 * @package	Materia.EAV
 * @author	Filippo Bovo
 * @link	https://lab.alchemica.io/projects/materia/
 **/

use \Materia\Content\MVC\Views\HTML\Tag as Tag;

interface Value {

	/**
	 * Constructor
	 *
	 * @param	array	$params
	 **/
	public function __construct( array $params = [] );

	/**
	 **/
	public function __toString() : string;

	/**
	 * Set value
	 *
	 * @param	mixed	$value
	 * @return	self
	 **/
	public function setValue( $value ) : self;

	/**
	 * Get value
	 *
	 * @return	mixed
	 **/
	public function getValue();

	/**
	 * Check if the value is valid or not
	 *
	 * @return	bool
	 **/
	public function isValid() : bool;

	/**
	 * Returns the proper form element for the value
	 *
	 * @return	Tag
	 **/
	public function getFormElement() : Tag;

}
