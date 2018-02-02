<?php

namespace Materia\EAV;

/**
 * Test Enity class
 *
 * @package Materia.Test
 * @author  Filippo "Pirosauro" Bovo
 * @link    http://lab.alchemica.org/projects/materia/
 **/

use \Materia\Data\SQL\MySQL as MySQL;
use \Materia\Development\Data\SQL\Dummy\Fields as Fields;

class Content extends Record {

	const NAME = 'content';
	const TABLE = 'contents';
	const PREFIX = 'content_';
	const PRIMARY_KEY = 'id';

}

class EntityTest extends \PHPUnit\Framework\TestCase {

	const ROW_COUNT = 10;

    protected $_connection;
    protected $_fields;

    protected $_timer = [];

	/**
	 * @see	\PHPUnit_Framework_TestCase::setUp()
	 **/
	public function setUp() {

		// Setting up the connection
		$this->_connection = new MySQL\Connection( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWORD'], [], 'test_' );
		//
		$this->_fields = [
			'int'      => new Fields\Integer( 11, 1, 100 ),
			'char'     => new Fields\Char( 128, Fields\Char::GENERATOR_LOREMIPSUM ),
			'datetime' => new Fields\DateTime(),
		];

		$this->_connection->exec( 'SET FOREIGN_KEY_CHECKS = 0;' );
		$this->_connection->exec( 'CREATE TABLE IF NOT EXISTS `test_contents` (
		                              `content_id` int(11) NOT NULL AUTO_INCREMENT,
		                              `content_created` timestamp NOT NULL DEFAULT \'0000-00-00 00:00:00\',
		                              `content_title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
		                              `content_status` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
		                              PRIMARY KEY (`content_id`),
		                              KEY `content_created` (`content_created`),
		                              FULLTEXT KEY `content_title` (`content_title`)
		                           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;' );
		$this->_connection->exec( 'CREATE TABLE IF NOT EXISTS `test_eav_entities` (
		                              `eav_entity_id` int(11) NOT NULL AUTO_INCREMENT,
		                              `eav_entity_type` varchar(255) NOT NULL,
		                              PRIMARY KEY (`eav_entity_id`)
		                           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;' );
		$this->_connection->exec( 'CREATE TABLE IF NOT EXISTS `test_eav_attributes` (
		                              `eav_attribute_id` int(11) NOT NULL AUTO_INCREMENT,
		                              `eav_attribute_name` varchar(32) NOT NULL,
		                              `eav_attribute_label` varchar(32) NOT NULL,
		                              `eav_attribute_type` varchar(64) NOT NULL,
		                              `eav_attribute_shared` tinyint(1) unsigned DEFAULT 0,
		                              `eav_attribute_limit` smallint(4) unsigned DEFAULT 1,
		                              `eav_attribute_params` varchar(255) NOT NULL,
		                              PRIMARY KEY (`eav_attribute_id`)
		                           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;' );
		$this->_connection->exec( 'CREATE TABLE IF NOT EXISTS `test_eav_entities_attributes` (
		                              `eav_entity_attribute_hash` varchar(8) NOT NULL,
		                              `eav_entity_attribute_entity` int(11) NOT NULL,
		                              `eav_entity_attribute_attribute` int(11) NOT NULL,
		                              PRIMARY KEY (`eav_entity_attribute_hash`),
		                              UNIQUE KEY `eav_entity_attribute_entity` (`eav_entity_attribute_entity`,`eav_entity_attribute_attribute`),
		                              CONSTRAINT `test_eav_entities_attributes_ibfk_1` FOREIGN KEY (`eav_entity_attribute_entity`) REFERENCES `test_eav_entities` (`eav_entity_id`)
		                           ) ENGINE=InnoDB DEFAULT CHARSET=utf8;' );
		$this->_connection->exec( 'CREATE TABLE IF NOT EXISTS `test_eav_values_00000000` (
		                              `eav_value_id` int(11) NOT NULL AUTO_INCREMENT,
		                              `eav_value_entity` int(11) NOT NULL,
		                              `eav_value_value` varchar(128) NOT NULL,
		                              PRIMARY KEY (`eav_value_id`),
		                              KEY `eav_value_entity_attribute` (`eav_value_entity`),
		                              CONSTRAINT `test_eav_values_00000000_ibfk_1` FOREIGN KEY (`eav_value_entity`) REFERENCES `test_contents` (`content_id`)
		                           ) ENGINE=InnoDB DEFAULT CHARSET=utf8;' );

		$this->_connection->exec( 'INSERT INTO `test_eav_entities` (`eav_entity_id`, `eav_entity_type`) VALUES (1, \'Materia\\\EAV\\\Content\');' );
		$this->_connection->exec( 'INSERT INTO `test_eav_attributes` (`eav_attribute_id`, `eav_attribute_name`, `eav_attribute_label`, `eav_attribute_type`, `eav_attribute_shared`, `eav_attribute_limit`, `eav_attribute_params`) VALUES (1, \'status\', \'Status\', \'Materia\\\EAV\\\Values\\\Option\', 0, 1, \'{"options":{"open":"Open","close":"Close"}}\');' );
		$this->_connection->exec( 'INSERT INTO `test_eav_attributes` (`eav_attribute_id`, `eav_attribute_name`, `eav_attribute_label`, `eav_attribute_type`, `eav_attribute_shared`, `eav_attribute_limit`, `eav_attribute_params`) VALUES (2, \'body\', \'Body\', \'Materia\\\EAV\\\Values\\\Text\', 0, 2, \'{}\');' );
		$this->_connection->exec( 'INSERT INTO `test_eav_entities_attributes` (`eav_entity_attribute_hash`, `eav_entity_attribute_entity`, `eav_entity_attribute_attribute`) VALUES (\'99999999\', 1, 1);' );
		$this->_connection->exec( 'INSERT INTO `test_eav_entities_attributes` (`eav_entity_attribute_hash`, `eav_entity_attribute_entity`, `eav_entity_attribute_attribute`) VALUES (\'00000000\', 1, 2);' );

		for ( $i = 0; $i < self::ROW_COUNT; $i++ ) {

			$sth = $this->_connection->prepare( 'INSERT INTO `test_contents` (`content_id`, `content_created`, `content_title`, `content_status`) VALUES (?, ?, ?, ?);' );

			$sth->bindValue( 1, $i + 1, \PDO::PARAM_INT );
			$sth->bindValue( 2, $this->_fields['datetime']->__toString(), \PDO::PARAM_STR );
			$sth->bindValue( 3, $this->_fields['char']->__toString(), \PDO::PARAM_STR );
			$sth->bindValue( 4, ( rand( 0, 1 ) ?  'close' : 'open' ), \PDO::PARAM_STR );

			$sth->execute();

			$sth = $this->_connection->prepare( 'INSERT INTO `test_eav_values_00000000` (`eav_value_entity`, `eav_value_value`) VALUES (?, ?);' );

			$sth->bindValue( 1, $i + 1, \PDO::PARAM_INT );
			$sth->bindValue( 2, $this->_fields['char']->__toString(), \PDO::PARAM_STR );

			$sth->execute();

			$sth->bindValue( 1, $i + 1, \PDO::PARAM_INT );
			$sth->bindValue( 2, $this->_fields['char']->__toString(), \PDO::PARAM_STR );

			$sth->execute();

		}

		$this->_connection->exec( 'SET FOREIGN_KEY_CHECKS = 1;' );

	}

	/**
	 * @see	\PHPUnit\Framework\TestCase::tearDown()
	 **/
	public function tearDown() {

		$this->_connection->exec( 'SET FOREIGN_KEY_CHECKS=0;' );
		$this->_connection->exec( 'DROP TABLE test_contents;' );
		$this->_connection->exec( 'DROP TABLE test_eav_entities ;' );
		$this->_connection->exec( 'DROP TABLE test_eav_attributes;' );
		$this->_connection->exec( 'DROP TABLE test_eav_entities_attributes;' );
		$this->_connection->exec( 'DROP TABLE test_eav_values_00000000;' );
		$this->_connection->exec( 'SET FOREIGN_KEY_CHECKS=1;' );

		// Print benchmark
		if ( isset( $GLOBALS['BENCHMARK'] ) ) {

			$benchmark = "\n" . str_repeat( '=', 40 ) . "\n";

			foreach ( $this->_timer as $key => $value ) {

				$benchmark .= sprintf( "%-20s %18ss\n", $key, number_format( $value, 3 ) );

			}

			$benchmark .= str_repeat( '=', 40 ) . "\n";

			fwrite( STDERR, $benchmark );

		}

	}

	/**
	 * Test field generation
	 **/
	public function testEntity() {

		$record = new Content( $this->_connection );
		$query  = $this->_connection
		               ->select()
		               ->from( 'contents' );

		$result = $query->first( Content::class, [ $this->_connection ] );

		$record->load( $result->id );

		$count = count( $record->body );

		$this->assertEquals( $result->id, $record->id );
		$this->assertContains( $record->status, [ 'open', 'close' ] );
		$this->assertNotNull( current( $record->body ) );

		// Update
		$before = current( $record->body );

		$record->setBody('test', 0);

		$this->assertNotEquals( $before, current( $record->body ) );

		$record->save();

		// Reload
		$record = new Content( $this->_connection );

		$record->load( $result->id );

		$this->assertEquals( 'test', current( $record->body ) );

	}

}

