<?php  
/**
 * @version 0.4.0
 * @package DropFramework
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework\Core;

/**
 * Abstract base class for formatting a domain object pulled from the database.
 */
abstract class DomainFormatter {
    /**
     * Target Domain
     * @var string 
     */
    protected $targetDomain;
    
    /**
     * The domain formatter object will only format domains of type 
     * $targetDomain
     * @param type $targetDomain
     */
    public function __construct( $targetDomain )
    {
        $this->targetDomain = $targetDomain;
    }
    
    /**
     * Checks if domain is of type $targetDomain
     * @param DomainObject $obj
     * @return bool
     */
    final protected function is_targetted_domain( DomainObject $obj )
    {
        return is_a( $obj, $this->targetDomain );
    }
    
    /**
     * Subclasses must override the format method.
     * 
     * This method takes a DomainObject of type $targetDomain that has been 
     * created by a Mapper object and then
     * formats that object for use by the application model.
     */
    abstract public function format( DomainObject $obj );
}