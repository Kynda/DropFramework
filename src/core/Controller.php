<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage Controller
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework;

/**
 * Abstract base class provides interface for derrived control classes.
 */
abstract class Controller 
{
    /**
     * The minimum level neccessary to execute BackControl paths.
     */
    const MIN_AUTHORIZED_LEVEL = 1;    
    
    /**
     *
     * @var FrameworkRegistry 
     */
    protected $registry;
    
    /**
     * $var Request
     */
    protected $request;
    
    /**
     * Stores reference to FrameworkRegistry for internal use.
     * @param FrameworkRegistry $registry 
     */
    public function __construct( FrameworkRegistry $registry ) 
    {        
        $this->registry = $registry;
        $this->request = Request::instance();
    }    
    
    /**
     * Validates request then executes requested method.
     * @param Request $request 
     */
    public function execute( Request $request = null) 
    {        

        if( ! $request )
        {
            $request = Request::instance();
        }            
        
        //Get REQUEST variable used for triggering control methods.
        $methodTrigger = $this->registry->method_trigger;        
        
        //If REQUEST variable is not set then use index.
        try {
            $action = $request->$methodTrigger ? $request->$methodTrigger : 'index';
        } catch( UnsanitaryRequestException $e ) {
            $action = 'index';
        }                
        
        //Checks that request is callable. (XSS protection)
        if( is_callable ( array( $this, $action ) ) == false ) 
        {
            $request->addResp( 'Error: The server could not find the requested action.' );
            $action = 'index';
        }               
        
        //Call requested action.
        try {
            $this->$action( $request );
        } catch ( TemplateException $e ) {
            $request->addResp( $e->getMessage() );
            $this->error();
        }
    }
    
    /**
     * Returns true if user meets minimum access level.
     * @param int $minLevel The minimum access level allowed.
     * @return bool
     */
    protected function accessLevel( $minLevel = 0 ) 
    {
        if( ! $minLevel ) { return true; }
        
        return ( $this->registry->access['user_level'] >= $minLevel );
    }
    
    /**
     * Checks authorization and resets path direction if not allowed.
     * @return bool Returns true if authorized.
     */
    protected function authorized() 
    {
        if ( $this->accessLevel(self::MIN_AUTHORIZED_LEVEL) == false ) 
        {
            $this->request->addResp( $this->registry->lang['access_denied'] );
            $this->error();
            return false;
        }
        return true;
    }
    
    /**
     * Adds a list of messages set in the request to the view.
     */
    protected function error( )
    {
        $this->view->messages = $this->request->getResps();
        $this->view->show( 'Error' );
    }    
    
    /**
     * Default control request.
     */
    abstract protected function index();
}