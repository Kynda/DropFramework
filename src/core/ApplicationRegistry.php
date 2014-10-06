<?php  

/**
 * @version 0.3.0
 * @package Core
 * @subpackage FrameworkRegistry
 * @author Joe Hallenbeck
 * @todo Add Dependency Injection into Framework
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/**
 * Global registry of all objects substantiated in application. Needs to require Framework and Request because it runs
 * before Autoload.
 */
require_once 'FrameworkRegistry.class.php';
require_once 'Request.class.php';

/**
 * The ApplicationRegistry loads the configuration and autoload parameters that the application will run off of. This
 * is a essentially a singleton superglobal that contains both itself and it's parent class.
 * 
 * To initialize the framework you need to require the ApplicationRegistry.class.php and initialize an instance of the
 * ApplicationRegistry by calling:
 * 
 * ApplicationRegistry::instance( 'ApplicationRegistry' )
 * 
 * This initialization will set up the FrameworkRegistry (using the files in BASEPATH/config) and will store an instance
 * of the FrameworkRegistry in the Registry instances array under the key 'ApplicationRegistry', by immediately calling
 * after this the initApplication method this ApplicationRegistry will load the application specific configurations which
 * will either override the FrameworkRegistry values or instantiate additional configurations. This full registry is
 * then stored in the Registry instances array under the $appName key.
 * 
 * Pass the fully initialized instance of the ApplictionRegistry to the Router class and the application will begin.
 */
class ApplicationRegistry extends FrameworkRegistry
{
    /**
     * Protected because this is a singleton.
     */
    protected function __construct() { }
    
    /**
     * Overrides Registry magic method. If a request does not exist in the ApplicationRegistry, then this will return
     * the value from the FrameworkRegistry.
     * @param string $key Property name
     * @return mixed
     */
    public function __get( $key )
    {
        if( isset( $this->requests[$key] ) )
        {
            return $this->requests[$key];
        }
        return FrameworkRegistry::instance( 'FrameworkRegistry' )->{$key};
    }    
    
    /**
     * Takes module parameters and loads application configurations that override
     * framework-wide configurations; loads additional autoloaders and the
     * php __autoload function for models. Grabs end-user options from database.
     * @param mosParams $params 
     */
    public function initApplication( $appName ) 
    {              
        // Set appDir to appdirectory.
        $this->appDir = BASEPATH . $appName . DIRECTORY_SEPARATOR;
        
        // Load application specific config.
        $this->getConfig( 'config', $this->appDir );                           
       
        if( is_array( $this->config_files ) )
        {
            foreach( $this->config_files as $file ) 
            {
                $this->getConfig( $file, $this->appDir );
            }     
        }        
        // Load application specific helpers and libraries.
        $this->autoLoad( $this->appDir );     
        
        // Remove ApplicationRegistry from the Registries and replace it with an instance of itself specified by it's 
        // appName.

        return $this->register( $appName );
    }
    
    /**
     * Clones the ApplicationRegistry and removes ApplicationRegistry from the Registries and replaces it with an 
     * instance of itself named $appName. (This allows multiple applications to operate at the same time)
     * @param type $appName
     * @return type
     */
    protected function register( $appName )
    {
        self::$registries[$appName] = clone $this;
        unset( self::$registries['ApplicationRegistry'] );
        return self::$registries[$appName];
    }
}