<?php  
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage Request
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework\Core;

class UnsanitaryRequestException extends \Exception {};

/**
 * The request object is a registry object that stores a sanitized copy of the 
 * user REQUEST and any responses generated by that request.
 * 
 * It is possible to pass the Request a mock REQUEST that overrides any of the 
 * default REQUEST values.
 */
class Request {
    /**
     * Responses to the request.
     * @var array of strings
     */
    protected static $resp = array( );
    
    /**
     * Personal set of sanitized properties stored by each instance of a request 
     * object.
     */
    protected $properties = array();
    
    /**
     * Personal set of unsanitized properties stored by each instance of a 
     * request object.
     */
    protected $dirtyProperties = array();
    
    /**
     * Collection of Request Objects in existance.
     */
    protected static $requests = array();
    
    /**
     * Protected because any request object once created is made global.
     * @param array $request
     */
    protected function __construct( array $request = null ) {        
        $this->init( $request );
    }
    
    /**
     * Returns an instance of a request object. Example usage:
     * 
     *  Request::instance()         - Constructs an instance from $_REQUEST
     *  Request::instance( 'Post' ) - Constructs an instance from $_POST
     *  Request::instance( 'Get' )  - Constructs an instance from $_GET
     *  Request::instance( 'Files') - Constructs an instance from $_FILES
     *  Request::instance( 'Get', 'items', array( 'm' => 'items) )
     *   - Constructs an instance from $_GET, named 'items', with obj::m->items; 
     *     this request object can be retrieved latter with 
     *     Request::instance( 'Get', 'items')
     * 
     * @param string $class Name of the request class to instantiate.
     * @param string $class Name to assign this request.
     * @param array $request An array of custom request arguments.
     * @return Request object from the requests collection.
     */
    public static function instance( $class = 'Request', $name = null, $request = null )
    {
        if( ! $name )
        {
            $name = $class;
        }
        
        if( ! isset( self::$requests[$name]) )
        {
            $class = 'Kynda\DropFramework\Core\\' . $class;
            $newRegistry = new $class( $request );
            
            // Make sure that the class specified is actually a request.
            if( $newRegistry instanceof Request )
            {
                self::$requests[$name] = $newRegistry;
            } else {
                throw new \BadMethodCallException( "$class is not a Request");
            }
        }
        return self::$requests[$name];
    }       
    
    /**
     * Sanitizes the request array and assigns it to the internal value of the 
     * request.
     * @param array $request Optional mock request array.
     * @return boolean true on success.
     */
    protected function init( $request = null ) {
        
        if( is_array( $request ) )
        {
            $this->dirtyProperties = array_merge( $_REQUEST, $request );
        } else {
            $this->dirtyProperties = $_REQUEST;
        }
        return true;
    }
    
    /**
     * Retrieve a request property.
     * @param string $key
     * @return string
     */
    public function __get( $key )
    {
        if( isset( $this->properties[$key] ) )
        {
            return $this->properties[$key];
        } else if( isset( $this->dirtyProperties[$key] ) ) {            
            $this->properties[$key] = $this->sanitize( $this->dirtyProperties[$key] );            
            return $this->properties[$key];
        }
        return null;
    }
    
    public function __set( $name, $value )
    {
        $this->dirtyProperties[$name] = $value;
    }
    
    /**
     * Ensures only permitted_uri_chars are in the request URI.
     * @param string $data
     * @return string
     * @throws UnsanitaryRequestException
     */
    protected function sanitize( $data )
    {
        if( preg_match( FrameworkRegistry::instance( 'FrameworkRegistry')->permitted_uri_chars, $data ) == 1 )
        {            
            throw UnsanitaryRequestException();
        } else {
            return $data;
        }       
    }
    
    /**
     * Adds a response to the request. These would be (1) error messages and 
     * (2) success messages.
     * @param string $resp
     */
    public function addResp( $resp ) {
        self::$resp[] = $resp; 
    }
    
    /**
     * Gets all responses that the request generated.
     * @return array of strings.
     */
    public function getResps( ) {
        return self::$resp;
    }
    
    /**
     * Returns the contents of the Request as a validated array.
     * @return array
     */
    public function getArray() {
        if( count( $this->dirtyProperties ) != count( $this->properties ) )
        {
            foreach( $this->dirtyProperties as $key => $property )
            {
                $this->__get( $key );
            }       
        }
        return $this->properties;
    }
}