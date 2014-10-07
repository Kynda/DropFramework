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
class StmtFactory
{   
    protected $frozenFields = array();
    
    /**
     * Prepares an insert statement for a given DomainObject.
     * @param PDO $pdo
     * @param string $table
     * @param DomainObject $obj
     * @return PDOStatment 
     */
    public function insertStmt( PDO $pdo, $table, DomainObject $obj )
    {
        $stmt = 'INSERT INTO ' . $table . ' ( ';
        $arr = $obj->getArray();
        if( isset( $arr['id'] ) )
        {
            
        }
        $keys = $values = '';
        foreach( $arr as $key => $value )
        {
            $keys .= " `$key`, ";
            $values .= " :$key, ";
        }
        $stmt .= rtrim( trim( $keys ), ', ' ) . ') VALUES ( ';
        $stmt .= rtrim( trim( $values ), ', ') . ' ) ';
        return $pdo->prepare( $stmt );
    }
    
    /**
     * Prepares an update statement for a given DomainObject. Exludes frozen 
     * fields from update.
     * @param PDO $pdo
     * @param type $table
     * @param DomainObject $obj
     * @return PDOStatement 
     */
    public function updateStmt( PDO $pdo, $table, DomainObject $obj )
    {
        $stmt = 'UPDATE ' . $table . ' SET ';
        $arr = $obj->getArray();
        foreach( $arr as $key => $value )
        {
            if( ! in_array( $key, $this->frozenFields ) )
            {
                $stmt .= " `$key`=:$key, ";
            }
        }
        $stmt = rtrim( trim( $stmt), ',' ) . ' WHERE `id`=:id ';
        return $pdo->prepare( $stmt );
    }
    
    /**
     * Returns a prepared statemnt that selects from a given table by id.
     * @param PDO $pdo
     * @param string $table Table Name
     * @return PDOStatement
     */
    public function selectStmt( PDO $pdo, $table )
    {
        return $pdo->prepare( 'SELECT * FROM ' . $table . ' WHERE `id`=?' );
    }
    
    /**
     * Returns a prepared select statement built around $args. Possible 
     * arguments are:
     * 
     * prepare  =>  A prepared select statement that will override the default 
     *              select all statement. Otherwise statement
     *              will take the form SELECT * FROM $table :where :groupby :orderby :limit
     * orderby  =>  The string component of an ORDER BY
     * where    =>  The string component of a WHERE
     * groupby  =>  The string component of a GROUP BY
     * limit    =>  The string component of a LIMIT 
     * 
     * Remember to include ? in the arguments where values would be.
     * 
     * @param PDO $pdo
     * @param string $table Table to select from
     * @param array $args An array of string arguments for modifying the 
     * returned statement where 'key' will be replaced with 'value'.
     * @return PDOStatement
     */
    public function selectAllStmt( PDO $pdo, $table, $args=null )
    {
        if( isset( $args['prepare'] ) )
        {
            return $pdo->prepare( $args['prepare'] );        
        } else {
            $stmt = 'SELECT * FROM ' . $table . ' :where :groupby :orderby :limit';
        }
            
        if( isset( $args['orderby'] ) ) 
        {        
            $stmt = str_replace(':orderby', ' ORDER BY ' . $args['orderby'], $stmt);
        } else {
            $stmt = str_replace(':orderby', '', $stmt);
        }
        if( isset( $args['where'] ) )
        {
            $stmt = str_replace(':where', ' WHERE ' . $args['where'], $stmt );        
        } else {
            $stmt = str_replace(':where', '', $stmt);
        }
        if( isset( $args['groupby'] ) )
        {
            $stmt = str_replace(':groupby', ' GROUP BY ' . $args['groupby'], $stmt);
        } else {
            $stmt = str_replace(':groupby', '', $stmt);
        }
        if( isset( $args['limit'] ) )
        {            
            $stmt = str_replace(':limit', ' LIMIT ' . $args['limit'], $stmt);
        } else {
            $stmt = str_replace(':limit', '', $stmt);
        }           
        return $pdo->prepare( $stmt );           
    }
    
    /**
     * Prepares a statement to delete a row from the table.
     * @param PDO $pdo
     * @param string $table The table to delete from.
     * @return PDOStatement
     */
    public function deleteStmt( PDO $pdo, $table )
    {
        return $pdo->prepare( 'DELETE FROM ' . $table . ' WHERE `id`=?' );
    }
    
    /**
     * This adds a field to the frozenFields array. All fields in the 
     * frozenFields array will be excluded from calls to update the row.
     * @param string $field A table field.
     */
    public function freezeUpdate( $field )
    {
        $this->frozenFields[] = $field;
    }
    
    /**
     * Binds an array of parementers to a PDO statement then executes them.
     * @param array $arr
     * @param PDOStatement $stmt
     * @throws PDOException
     */
    public function bindAndExecute( array $arr, PDOStatement $stmt )
    {
        $values = "\n";

        
        $update =  (bool) strstr( $stmt->queryString, 'UPDATE' );
        foreach( $arr as $key => $value ) 
        {
            if( ! $update || ! in_array( $key, $this->frozenFields ) )
            {
                $values .= ":$key, $value\n";
                $stmt->bindValue( ":$key", $value );
            }
        }
        try 
        {               
            $result = $stmt->execute( );
        } catch ( PDOException $e ) {
            trigger_error( $e->getMessage() . "\n\n" . $stmt->queryString . "\n" . $values );
            throw $e;
        }                
        
    }
}