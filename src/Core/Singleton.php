<?php  
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage Singleton
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework\Core;

/**
 * One ring to rule them all, one ring to find them,
 * One ring to bring them all and in the darkness bind them.
 * implementation of Singleton. This behavior should be refactored out.
 */
abstract class Singleton {
    protected static $instance = array();    
    
    protected function __construct() {}
    
    public static function instance( $class ) 
    {
        if( ! isset( self::$instance[$class] ) ) 
        {
            self::$instance[$class] = new $class();
        }
        return self::$instance[$class];
    }
}
