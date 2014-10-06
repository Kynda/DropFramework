<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage View
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework;

class TemplateException extends Exception {}

/**
 * Handles data for templates.
 */
class View {
    protected $vars = array();
    protected $registry;
    
    /**
     *
     * @param FrameworkRegistry $registry 
     */
    public function __construct( ApplicationRegistry $registry ) {
        $this->registry = $registry;
    }
    
    public function __set($index, $value) {
        $this->vars[$index] = $value;
    }
    
    /**
     * Set bulk template values.
     * @param array $arr Associative array of values to use in template.
     */
    public function addArray( $arr ) {
        if( is_array( $arr ) && ! is_a( $arr, 'Collection' ) ) {
            $this->vars = array_merge( $this->vars, $arr );        
        }
    }
    
    /**
     * Includes template and passes variables to template.
     * @param string $template Template name in application views directory.
     * @return type 
     */
    public function show( $template ) {
        $path = $this->registry->appDir . 'views' . DIRECTORY_SEPARATOR . $template . '.php';
        if( file_exists( $path ) == false ) {
            throw new TemplateException( $template . ' template Not Found! Path:' . $path );
            return false;
        }
        
        foreach( $this->vars as $key => $value ) {
            $$key = $value;            
        }
        include $path;
    }
    
    /**
     * Loops through a collection creating a view using a specified template
     * for each instance of a DomainObject in that collection.
     * @param string $template Name of a view template.
     * @param Collection $collection 
     */
    public function showCollection( $template, $collection ) 
    {
           foreach( $collection as $key => $domain ) 
           {    
                if(is_a( $domain, 'DomainObject' ) ) 
                {                
                    $this->addArray( $domain->getArray() );
                } else {
                    $this->value = $domain;
                }
                $this->show( $template );
            }        
    }
}