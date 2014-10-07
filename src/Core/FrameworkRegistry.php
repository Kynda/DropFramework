<?php  
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage FrameworkRegistry
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * 
 */

namespace Kynda\DropFramework\Core;

/**
 * Global registry of all objects substantiated in application. Needs require
 * because it runs before AutoLoad.
 */
require_once 'Registry.class.php';
class FrameworkRegistry extends Registry 
{    
    /**
     * Sets configDir, loads config.php and config array then loads contents
     * of the config['config_files'] array. Finally loads files in autoload
     * array, acquires database connection and stores request object.
     */
    protected function __construct() 
    {   
        // Load Config
        $this->requests['configDir'] = BASEPATH . 'config' . DIRECTORY_SEPARATOR;
        $this->getConfig( 'config' );        
        
        // Autoload Files
        $this->autoLoad();
        
        // Get Database handle.
        $this->getDbh();        
    }
        
    
    /**
     * Loads config files specified by name. If the file loads an array that
     * array is stored in the registry. If that array already exists in the 
     * registry then it merges the two arrays and overrights and collisions with
     * the new array.
     * 
     * Naming convention is $array_name = file_name (no extension) unless the 
     * file is in a subdirectory of the config folder. 
     * 
     * Config files stored in subdirectories of the config directory should be
     * specified by the convention 'directory_name/file_name' whre 
     * directory_name is the array_name.
     * 
     * getConfig can also load files that do not specify any arrays.
     * 
     * @param string $arrName Name of the array/file to load. Array names ought
     * to be the same as the file name in the config directory.
     * @param string $appDir Name of the application directory, if null 
     * getConfig looks for the config file in the framework root config directory.
     * @return type 
     */
    protected function getConfig( $arrName, $appDir = null ) 
    {           
        // If config starts with a "/" then look for the config file in the root 
        // config directory.
        if( $arrName{0} == DIRECTORY_SEPARATOR )
        {
            $arrName = ltrim( $arrName, DIRECTORY_SEPARATOR);
            $appDir = null;
        }        
        
        // If config contains a "/" then parse the config from a subdirectory.
        if( strrchr($arrName, DIRECTORY_SEPARATOR) ) 
        {
            $arrPath = $arrName;
            $arrHldr = explode(DIRECTORY_SEPARATOR, $arrName);
            $arrName = $arrHldr[0];            
        } else {
            $arrPath = $arrName;
        }        
                
        // If not initialize application use default config path.
        if( ! $appDir ) 
        {
            $appPath = $this->configDir . $arrPath . '.php';
            if( is_readable( $appPath ) ) 
            {                
                include $appPath;
            } else {
                throw new Exception('Application Directory Could Not Be Found: ' 
                        . $appPath . '<br />');
            }
        } else {
            $appPath = $appDir . 'config' . DIRECTORY_SEPARATOR . $arrPath 
                    . '.php';       
            if( is_readable( $appPath ) ) 
            {
                include $appPath;
            } else {
                throw new Exception('Application Directory Could Not Be Found: ' 
                        . $appPath . '<br />');
            }
        }
        
        // If loading created an array register it with the FrameworkRegistry.        
        if( $arrName == 'config' )
        {            
            $arrName = 'config';
            foreach( $$arrName as $key => $property )
            {
                $this->requests[$key] = $property;             
            }
            return;
        }
        
        if( isset( $$arrName ) && is_array( $$arrName ) ) 
        {
            if( ! isset( $this->requests[$arrName] ) ) {
                $this->requests[$arrName] = array();
            }
            $this->requests[$arrName] = 
                    array_merge( $this->requests[$arrName], $$arrName );
        }   
    }    
    
    /**
     * Autoload loads the autoload.php file and all subsequent core, helper,
     * and library files.
     * @param string $appDir Directory of the application. 
     */
    protected function autoLoad( $appDir = null ) 
    {          
        
        $path = BASEPATH . $appDir;        
        
        // If not initializing application, use default autoload.
        if( ! $appDir ) 
        {
            include $this->configDir . 'autoload.php';
        } else {
            $appPath = $appDir . DIRECTORY_SEPARATOR . 'config' 
                    . DIRECTORY_SEPARATOR . 'autoload.php';
            if ( is_readable ( $appPath ) ) 
            {                
                require_once $appPath; 
            } else {
                throw new Exception( 
                        'The Appication directory is Not Readable: ' 
                        . $appPath . '<br />' );
            }
        }
        
        // If successfully got the autoload.php file then loop through the 
        // arrays and load each file.
        if( isset( $autoload ) && is_array( $autoload ) ) 
        {
            foreach( $autoload as $directory => $files ) 
            {
                foreach( $files as $file ) 
                {
                    if( $file{0} == DIRECTORY_SEPARATOR )
                    {
                        $fullPath = BASEPATH . $directory . DIRECTORY_SEPARATOR 
                                . ltrim( $file, DIRECTORY_SEPARATOR);
                    } else {
                        $fullPath =  $path . $directory . DIRECTORY_SEPARATOR 
                                . $file;
                    }
                    if( is_readable( $fullPath ) ) {
                        require_once $fullPath;
                    } else {
                        throw new Exception( 'The Full Path is Not Readable: ' 
                                . $fullPath . '<br />' );
                    }
                }
            }   
        }
    }
    
    /**
     * Call after loading config to get the database handle.
     */
    protected function getDbh() 
    {           
        $this->dbh = new PDO( 
                $this->db_dsn, 
                $this->db_user,  
                $this->db_pass ) 
                or trigger_error('Could not connect to database.');
    }     
}