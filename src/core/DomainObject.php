<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @version 0.3.0
 * @package Core
 * @subpackage DomainObject
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/**
 * DomainObjects provide a prototype of data in object form to be used by models.
 */
abstract class DomainObject {
    protected $vars = array();
    
    /**
     * If array keys match $vars keys then the array values are copied over
     * into the internal vars, otherwise left blank.
     * @param array $array 
     */
    public function __construct( $array = null ) 
    {
        if(is_array( $array ) ) 
        {
            foreach( $array as $key => $value ) 
            {
                if( array_key_exists( $key, $this->vars ) ) 
                {
                    $this->vars[$key] = $value;
                }
            }
        }
    }
    
    /**
     * Setter magic method
     * @param string $key
     * @param mixed $request 
     */
    public function __set( $key, $request ) 
    {
        if( array_key_exists( $key, $this->vars) ) 
        {
            $this->vars[$key] = $request;
        } else {
            return null;
        }
    }
    
    /**
     * Getter magic method
     * @param type $key
     * @return type 
     */
    public function __get( $key ) 
    {
        if( isset( $this->vars[$key] ) ) 
        {
            return $this->vars[$key];
        }
        return null;
    }
    
    /**
     * Checks self to ensure that vars validate to given parameters.
     */
    public abstract function checkIntegrity();
    
    /**
     * Returns the internal array.
     * @return array 
     */
    public function getArray() 
    {
        return $this->vars;
    }
    
    /**
     * Alternative method for getArray()
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }
    
    /**
     * Checks if property $var is a numeric value.
     * @param string $var
     * @throws DomainException If $var is not numeric.
     */
    protected function testNumeric( $var )
    {
        if( ! is_numeric( $this->$var ) ) 
        { 
            throw new DomainException( get_class($this) . "$var must be numeric." );             
        }
        $this->$var = intval( $this->$var );          
    }
    
    /**
     * Checks if property $var is set on this object.
     * @param string $var
     * @throws DomainException If $var is not set.
     */
    protected function testIsSet( $var )
    {
        if( $this->$var === null ) 
        {  
            throw new DomainException( get_class($this) . " must have a $var" );
        }        
    }     
}