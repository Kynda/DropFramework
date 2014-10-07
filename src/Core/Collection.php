<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage Collection
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework\Core;

/**
 * Defines an iterator for Domain objects.
 */
class Collection implements Iterator {
    /**
     * A Mapper child.
     * @var Mapper 
     */
    protected $mapper;
    /**
     * Total number of objects in collection.
     * @var int 
     */
    protected $total = 0;
    /**
     * An array of associative arrays typically pulled from the database.
     * @var array 
     */
    protected $raw = array();
    
    /**
     * Internal iterator pointer.
     * @var int 
     */
    private $pointer = 0;
    /**
     * An array of subsantiated Domain objects.
     * @var array 
     */
    private $objects = array();
    
    /**
     * Stores Mapper object for use by collection and takes raw data that
     * will be lazy-loaded as needed.
     * @param Mapper $mapper
     * @param array $raw 
     */
    public function __construct( Mapper $mapper=null, array $raw=null ) 
    {
        if( ! is_null( $raw ) && ! is_null( $mapper ) ) 
        {
            $this->raw = $raw;
            $this->total = count( $raw );
        }
        $this->mapper = $mapper;
    }
    
    /**
     * If DomainObject matches collection needs it is added to the collection
     * @param DomainObject $obj 
     */
    public function add( DomainObject $obj ) 
    {
        if( is_a( $this->mapper, 'Mapper' ) )
        {
            $this->mapper->checkDomain( $obj );
        }
        $this->objects[$this->total] = $obj;
        $this->total++;
    }     
    
    /**
     * Resets internal pointer.
     */
    public function rewind() 
    { 
        $this->pointer = 0;         
    }
    
    /**
     * Returns current DomainObject
     * @return DomainObject 
     */
    public function current() 
    {
        return $this->getRow( $this->pointer );
    }
    
    /**
     * Returns internal pointer value.
     * @return int 
     */
    public function key() 
    {
        return $this->pointer;
    }
    
    /**
     * Gets next DomainObject and increments internal pointer.
     * @return DomainObject 
     */
    public function next() 
    {
        //$row = $this->getRow( $this->pointer + 1 );
        //if( $row ) { $this->pointer++; }
        ++$this->pointer;
    }
    
    /**
     * Checks if current position is valid.
     * @return bool 
     */
    public function valid() 
    {
        return( ! is_null( $this->current() ) );
    }
    
    public function count() {
        return $this->total;
    }
    
    /**
     * If row exists in collection it will be fetched from existing objects
     * or insantiated from the raw data if not already in existance.
     * @param type $num
     * @return type 
     */
    private function getRow( $num ) 
    {
        //echo "getRow Called: $num />";
        if( $num >= $this->total || $num < 0 ) 
        {
            //echo "Number is outside range<br />";
            return null;
        }
        if( isset( $this->objects[$num] ) ) 
        {
            //echo "Returning Existing object<br />";
            return $this->objects[$num];
        }
        if( isset ( $this->raw[$num] ) ) 
        {
            //echo "Creating From RAW<br />";
            $this->objects[$num] = $this->mapper->doCreateObject( $this->raw[$num] );           
            return $this->objects[$num];
        }
    }    
}