<?php
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage Router
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework;

/**
 * Simple Router
 */
class Router {    
    protected $registry;
    
    /**
     * Finds and calls apropriate Controller.
     * @param FrameworkRegistry $registry 
     */
    public function __construct( FrameworkRegistry $registry ) 
    {
        $this->registry = $registry;
        $controller = $this->getController();
        $controller->execute();
    }
    
    /**
     * Instatiates Controller specified by module params.
     * @return controller 
     */
    protected function getController() 
    {
        try {
            $request = Request::instance( 'Request' );
            $controlTrigger = $this->registry->controller_trigger;
            $controller = $request->$controlTrigger;
        } catch (UnsanitaryRequestException $e ) {
            $request->addResp( 'Error: The server could not find the requested 
                controller. Not permitted URI Characters.' );            
            $controller = 'Error';
        }       
        
        $appDir = $this->registry->appDir;
        
        $controlPath = $appDir . 'controllers/' . $request->$controlTrigger . '.class.php';   
        
        if( is_readable( $controlPath ) == false ) 
        {                     
            $request->addResp( 'Error: The server could not find the requested 
                controller. Directory Not Readable: ' . $controlPath );
            $controlPath = $appDir .'controllers/Error.class.php';   
            $controller = 'Error';            
        }
        
        include ( $controlPath );   
        
        if( class_exists ( $controller ) ) 
        {
            return new $controller( $this->registry );
        } else {
            throw new Exception( 'Requested Controller 
                Class does not exist:' . $controller );
        }
    }
}