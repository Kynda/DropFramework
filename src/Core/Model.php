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
 * Abstract base class provides interface for derrived model classes.
 */
abstract class Model 
{   
    /**
     * Reference to FrameworkRegistry
     * @var FrameworkRegistry 
     */
    protected $registry; 
    
    /**
     * Default is null, Model will get the registry itself if not specified.
     * @param FrameworkRegistry $registry 
     */
    public function __construct( FrameworkRegistry $registry = null ) 
    {
        if( ! $registry )
        {
            $this->registry = FrameworkRegistry::instance( 'FrameworkRegistry' );
        } else {
            $this->registry = $registry;
        }
    }
    
    /**
     * Executes some requested change to the modeled data.
     * @param Request $request
     * @return bool True if executed action succeeded. 
     */
    public function execute( Request $request )
    {
        return $this->{'do' . ucfirst( $request->{$this->registry->method_trigger} ) }( $request );
    }
    
    /**
     * Returns an instance of the Model
     * 
     * @param Request $request
     * @return type
     */
    public function fetch( Request $request )
    {
        return $this->{'get' . ucfirst( $request->{$this->registry->method_trigger} ) }( $request );
    }
}





