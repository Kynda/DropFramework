<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @version 0.4.0
 * @package DropFramework
 * @subpackage Mapper
 * @author Joe Hallenbeck
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

namespace Kynda\DropFramework;

class DomainIntegrityException extends Exception { }
class TypeException extends Exception { }

/**
 * Provides interface between DomainObjects and database.
 */
class Mapper {
    /**
     * PDO handle to database.
     * @var PDO 
     */
    protected static $dbh;
    
    /**
     * Target Table
     * @var string
     */
    protected $targetTable;
    
    /**
     * Class name of DomainObjects child mapper works with.
     * @var string
     */
    protected $targetDomain;
    
    /**
     * PDOStatement for selecting an item.
     * @var PDOStatement 
     */
    protected $selectStmt;
    
    /**
     * PDOStatement for selecting an item.
     * @var PDOStatement 
     */
    protected $deleteStmt;
    
    /**
     * PDOStatement for selecting an item.
     * @var PDOStatement 
     */
    protected $insertStmt;
    
    /**
     * PDOStatement for selecting an item.
     * @var PDOStatement 
     */
    protected $updateStmt;  
    
    /**
     * String Pattern for Select All PDO Statement.
     * @var string 
     */
    protected $selectAllStmt;
    
    /**
    * Instatiates Mapper
    *
    * @param PDO $dbh
    * @param string $table
    * @param DomainObject $obj
    * @param DomainFormatter $formatter
    * @param StmtFactory $stmtFactory 
    */
    public function __construct( PDO $dbh, 
            $table, 
            DomainObject $obj, 
            DomainFormatter $formatter = null, 
            StmtFactory $stmtFactory  = null ) 
    {
        self::$dbh = $dbh;
        $this->targetTable = $table;
        $this->targetDomain = get_class( $obj );
        $this->idField = array_shift( array_keys( $obj->getArray() ) );
        $this->formatter = $formatter;
        if( $stmtFactory != null )
        {
            $this->stmtFactory = $stmtFactory;
        } else {
            $this->stmtFactory = new StmtFactory();
        }        
        
        $target = $this->targetDomain;
        $this->selectStmt = $this->stmtFactory->selectStmt( self::$dbh, $this->targetTable );
        $this->selectAllStmt = $this->stmtFactory->selectAllStmt(self::$dbh, $this->targetTable);
        $this->deleteStmt = $this->stmtFactory->deleteStmt(self::$dbh, $this->targetTable );               
        $this->insertStmt = $this->stmtFactory->insertStmt( self::$dbh, $this->targetTable, new $target() );
        $this->updateStmt = $this->stmtFactory->updateStmt( self::$dbh, $this->targetTable, new $target() );
    }
    
    /**
     * Selects the row specified by $id and returns it as a proper object.
     * @param int $id
     * @return DomainObject or null if it could not be found.
     */
    public function find( $id ) 
    {   
        $old = $this->getFromMap( $id );
        if( $old ) 
        { 
            return $old;
        }
        
        $this->selectStmt->execute( array ( $id ) );
        $array = $this->selectStmt->fetch(PDO::FETCH_ASSOC);        
        if( ! is_array( $array ) || ! isset( $array[$this->idField] ) ) 
        { 
            return null;             
        }        
        return $this->doCreateObject( $array );     
    }
    
    /**
     * Inserts object into database, updates object with new
     * id then returns id.
     * @param mixed $obj array or DomainObject
     * @return int The id of the newly inserted row. 
     */
    public function insert( $obj )
    {        
        if( is_array( $obj ) )
        {
            $obj = $this->doCreateObject( $obj );
        }
        $this->checkDomainIntegrity( $obj );
        
        if( ! $this->formatter)
        {        
            $obj = $obj->getArray();
            $obj[$this->idField] = intval( $this->doInsert( $obj ) );
            $this->doCreateObject( $obj );
        } else {
            $arr = $obj->getArray();
            $arr[$this->idField] = intval( $this->doFormattedInsert( $obj ) );
            $this->doCreateObject( $arr );
        }
        return $obj[$this->idField];
    }
    
    /**
     * Updates a DomainObject's record in the database.
     * @param mixed $obj array or DomainObject
     * @return true on successfully update 
     */
    public function update( $obj )
    {
        if( is_array( $obj ) )
        {
            $targetDomain = $this->targetDomain;
            $obj = new $targetDomain( $obj );
        }
        $this->checkDomainIntegrity( $obj );
        $this->removeFromMap( $obj );     
        
        if( ! $this->formatter )
        {                                
            return $this->doUpdate( $obj->getArray() );
        } else {
            return $this->doFormattedUpdate( $obj );
        }
    }
    
    /**
     * Checks that DomainObject is of type targetDomain
     * @param DomainObject $obj 
     */
    public function checkDomain( DomainObject $obj ) 
    {
        if ( ! is_a( $obj, $this->targetDomain ) ) { 
            throw new TypeException( 'Attempted to use ' . get_class($obj) 
                    . 'in a ' . get_class( $this ) );
        }
        return true;
    }            
    
    /**
     * Deletes DomainObject from database.
     * @param DomainObject $obj 
     * @return bool True on successful deletion.
     */
    public function delete( $obj ) 
    {
        if( is_a(  $obj, 'DomainObject') )
        {
            $this->removeFromMap( $obj );            
            $id = $obj->{$this->idField};
        }
        if( is_array( $obj ) )
        {
            $id = $obj[$this->idField];
        }
        if( is_numeric( $obj ) )
        {
            $id = $obj;
        }        
        return $this->deleteStmt->execute( array ( $id ) );
    }
    
    /**
     * Creates a domain object of type specified in child class.
     */
    public function doCreateObject( array $array )
    {
        $old = $this->getFromMap( $array[$this->idField] );
        if( $old )
        {
            return $old;
        }
        
        $targetDomain = $this->targetDomain;
        if( ! $this->formatter )
        {
            $domain =  new $targetDomain( $array );        
        } else {
            $domain = $this->formatter->format( new $targetDomain( $array ) );
        }
        
        $this->addToMap( $domain );
        return $domain;
    }           
    
    /**
     * Returns a collection of domainObjects from the database.
     * @param array $args Optional arguments for the selectAllStmt()
     * @return Collection 
     */
    public function getCollection( $args = null, $params = null ) 
    {
        if( is_array( $args) ) 
        {
            $stmt = $this->stmtFactory->selectAllStmt( self::$dbh, $this->targetTable, $args );
        } else {            
            $stmt = $this->selectAllStmt;
        }
        
        try {
            $stmt->execute( $params );
        } catch ( PDOException $e ) {
            trigger_error( $e->getMessage() . "\n\n" . $stmt->queryString );
        }
        return new Collection( $this, $stmt->fetchAll( PDO::FETCH_ASSOC ) );
    }
    
    /**
     * Inserts DomainObject into database.
     * @param DomainObject $obj 
     * @return int Insert id of object inserted.
     */
    protected function doInsert( array $arr )
    {   
        $this->bindAndExecute( $arr, $this->insertStmt );    
        return self::$dbh->lastInsertId();
    }
    
    /**
     * Inserts DomainObject and performs any additional formatted as needed.
     * @param DomainObject $obj
     */
    protected function doFormattedInsert( DomainObject $obj )
    {
        $this->formatter->bind( $obj, $this->insertStmt, $this->stmtFactory );    
        return self::$dbh->lastInsertId();
    }
    
    /**
     * Updates DomainObject in database.
     * @param DomainObject $obj
     * @return bool true on success, false otherwise. 
     */
    protected function doUpdate( array $arr )  
    {   
        return $this->bindAndExecute( $arr, $this->updateStmt );
    } 
    
    /**
     * Updates DomainObject and performs any additional formatted as needed.
     * @param DomainObject $obj
     */
    protected function doFormattedUpdate( DomainObject $obj )
    {
        return $this->formatter->bind( $obj, $this->updateStmt, $this->stmtFactory );        
    }    
    
    /**
     * Checks DomainObject Integrity before insertion.
     * @param DomainObject $obj 
     */
    protected function checkDomainIntegrity( DomainObject $obj ) 
    {
        if ( ! $obj->checkIntegrity() || ! $this->checkDomain( $obj ) ) 
        {
            throw new DomainIntegrityException( 'Domain Object is not database safe.' );
        }
        return true;
    }
    
    /**
     * Binds keys/values of an array to a PDO statement then executes it.
     * @param array $arr
     * @param PDOStatement $stmt
     * @return Returns the result of the execution.
     */
    protected function bindAndExecute( array $arr, PDOStatement $stmt)
    {        
        return $this->stmtFactory->bindAndExecute( $arr, $stmt );              
    }
    
    /**
     * Fetches DomainObject from ObjectWatcher.
     * @param int $id
     * @return DomainObject 
     */
    protected function getFromMap( $id )
    {
        return ObjectWatcher::exists( $this->targetDomain, $id );
    }
   
    /**
     * Adds DomainObject to ObjectWatcher
     * @param DomainObject $obj
     * @return null
     */
   protected function addToMap( DomainObject $obj )
    {
        return ObjectWatcher::add( $obj );
    }
    
    /**
     * Removes DomainObject from ObjectWatcher
     * @param DomainObject $obj
     * @return null 
     */
    protected function removeFromMap( DomainObject $obj )
    {
        return ObjectWatcher::remove( $obj );
    }
}