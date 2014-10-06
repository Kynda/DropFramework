<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage Registry
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework;

/**
 * Registry is a singleton with basic set/get abilities.
 */
abstract class Registry
{   
    /**
     * Array of all registries in existance.
     * @var Registry An array of registries. 
     */
    protected static $registries = array();
    
    /**
     * Internal array of registry properties.
     * @var array 
     */
    protected $requests = array();  
    
    /**
     * Registry is an abstract singleton class. The construct method will be 
     * overwritten in the child classes.
     */
    abstract protected function __construct();
    
    /**
     * Request an instance of a particular registry. The class type specified 
     * will determine the construction method for the Registry instance.
     * @param string $class Name of the registry class to instantiate or return 
     * if already instantiated.
     * @return Registry a child of Registry.
     */
    public static function instance( $class )
    {

        if( ! isset( self::$registries[$class]) )
        {
            $newRegistry = new $class();
            // Make sure that the class specified is actually a registry.
            if( is_a( $newRegistry, 'Registry' ) )
            {
                self::$registries[$class] = $newRegistry;
            } else {
                throw BadMethodCallException();
            }
        }        
        return self::$registries[$class];
    }    
    
    /**
     * Sets a registry property.
     * @param type $key
     * @param type $request
     */
    public function __set( $key, $request ) 
    {
        $this->requests[$key] = $request;
    }
    
    /**
     * Retrieves a registry property
     * @param type $key
     * @return null if property does not exist.
     */
    public function __get( $key ) 
    {
        if( isset( $this->requests[$key] ) ) 
        {
            return $this->requests[$key];
        }
        return null;
    }    
}
