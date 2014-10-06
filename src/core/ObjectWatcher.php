<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @version 0.4.0
 * @package DropFramework
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework;

/**
 * Ensures a one-to-one correlation between objects and database sources.
 */
class ObjectWatcher
{   
    /**
     * An array of DomainObjects
     * @var array of DomainObjects 
     */
    private $all    = array();
    
    /**
     * The global instance of ObjectWatcher
     * @var ObjectWatcher
     */
    protected static $instance;
    
    /**
     * ObjectWatcher is a singleton.
     */
    protected function __construct() {}
    
    /**
     * Returns the global instance of ObjectWatcher
     * @param type $class
     */
    public static function instance()
    {
        if( ! is_a( self::$instance, 'ObjectWatcher' ) )
        {
            self::$instance = new ObjectWatcher();
        }
        return self::$instance;
    }
    
    /**
     * Gets the GlobalKey used by ObjectWatcher to store a specified DomainObject
     * @param DomainObject $obj
     * @return string 
     */
    public static function globalKey( DomainObject $obj )
    {
        $key = get_class( $obj ) . $obj->mid;
        return $key;
    }
    
    /**
     * Adds a DomainObject to the ObjectWatcher.
     * @param DomainObject $obj 
     */
    public static function add( DomainObject $obj )
    {
        $inst = self::instance( 'ObjectWatcher' );
        $inst->all[ $inst->globalKey( $obj ) ] = $obj;
    }
    
    /**
     * Removes a DomainObject from the ObjectWatcher
     * @param DomainObject $obj 
     */
    public static function remove( DomainObject $obj )
    {
        $inst = self::instance( 'ObjectWatcher' );
        unset( $inst->all[$inst->globalKey( $obj ) ] );
    }
    
    /**
     * Checks if DomainObject of a particular type and id already
     * exists and if so, returns it.
     * @param string $classname Class Name of a child class of DomainObject
     * @param int $id The `mid` value of that DomainObject
     * @return an instance of DomainObject or null if that DomainObject does
     * not yet exist. 
     */
    public static function exists( $classname, $id )
    {
        $inst = self::instance( 'ObjectWatcher' );
        $key = $classname.$id;
        if( isset( $inst->all[$key] ) )
        {
            return $inst->all[ $key ];
        }
        return null;
    }
    
    /**
     * Resets the Object Watcher by removing all objects from the watcher.
     */
    public static function reset()
    {
        $inst = self::instance( 'ObjectWatcher' );
        foreach( $inst->all as $obj )
        {
            self::remove( $obj );
        }
    }
}





