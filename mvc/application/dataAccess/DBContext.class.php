<?php
// framework/core/DBContext.class.php
// data base context
class DBContext {
    protected $conn = false;                    // database connection object
    protected $table;                           // table name
    protected $fields = array();                // fields list
    protected $pk = array();                    // Primary keys


    public function __construct() {
        // Default db parameters
        try {
            $this->conn = new PDO("mysql:host={$GLOBALS['config']['host']}:{$GLOBALS['config']['port']};
            dbname={$GLOBALS['config']['dbname']}",$GLOBALS['config']['user'],$GLOBALS['config']['password'],
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));    
            $this->conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");
        }
        catch(PDOException $e) {
            return "Connection failed: " . $e->getMessage();
        }        
    }

    public function __destruct() {
        $this->conn = null;
    }
    /**
     * Set the database table used for data persistence and prepare the fields for data manipulation
     * @param $table The database table name
     */
    private function useModel($table) {
        if(isset($table)) {
            $this->table = $table;
            $this->getFields();    
        }

    }

    /**
     * Get the list of the current used table fields
     */
    private function getFields() {
        $fields = $this->query("DESC `" . $this->table ."`");
        $this->pk = array();

        foreach($fields as $f) {
            $this->fields[] = $f['Field'];
            if($f['Key'] == 'PRI') {
                $this->pk[] = $f['Field'];
            }
        }
    }

    private function getSQLParams($model) {
        $field_list = array();  //field list string
        $value_list = array();  //value list string 

        foreach ($model as $field => $value) {
            if (in_array($field, $this->fields)) { 
                $field_list[] = $field;
                $value_list[$field] = $value;               
            }
        }

        return array($field_list, $value_list);
    }

    private function prepareParams($params) {
        return implode(',', array_map(function($p){return '?';}, $params));
    }
    /**
     * Execute procedure
     * 
     */
    public function executeProcedure($procedure_name, $params) {
        try {
            $sql = 'call ' . $procedure_name . '(' . $this->prepareParams($params) . ')';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
        }
        catch (PDOException $e) {
            return $sql . "<br>" . $e->getMessage();
        }   
    }

    /**
     * Execute function
     */
    public function executeFunction($function_name, $params) {
        try {
            $sql = 'SELECT ' . $function_name . '(' . $this->prepareParams($params) . ')';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(); 
        }
        catch (PDOException $e) {
            return $sql . "<br>" . $e->getMessage();
        }
    }

    /**
     * Insert records
     * @param $table the database table
     * @param $model associative array
     * @return mixed If succeed return inserted record id, else return error message
     */
    public function add($table, $model) {

        list($field_list, $value_list) = $this->getSQLParams($model);
        
        $sql_fields = implode(',',array_map(function($f) {return '`'.$f.'`';}, $field_list)); 
        $sql_fields_params = implode(',',array_map(function($f){return ':' . $f;}, $field_list));

        try {      
            $this->useModel($table);     
            $sql = 'INSERT INTO `'. $this->table . '`' . '(' . $sql_fields . ') VALUES ('. $sql_fields_params .')' ;
            $stmt = $this->conn->prepare($sql);

            $stmt->execute($value_list);
            return $this->lastInsertId();

        }
        catch (PDOException $e) {
            return $sql . "<br>" . $e->getMessage();
        }      
    }
    /**
     * Remove records
     * @param $table the database table
     * @param $field_list associative array with ids
     * @return mixed If succeed return true, else return false; If error return error message
     */
    public function remove($table, $field_list) {
        try{
            $this->useModel($table);
            $conditionParams = implode(' AND ', array_map(function($f) {
                return '`' . $f . '`=:' . $f;
            }, array_keys($field_list)));
            
            $sql = 'DELETE FROM `' . $this->table . '` WHERE ' .  $conditionParams;
                            
            $stmt = $this->conn->prepare($sql);  
            return $stmt->execute($field_list);  
        }
        catch (PDOException $e) {
            return $sql . "<br>" . $e->getMessage();
        }

    }
    /**
     * Select model according to a list of ids
     * @param $table the database table
     * @param $field_list associative array with id name and id value
     * @param $func function that does model mapping
     * @return mixed If succeed return the model(key - value pair array), else return empty array;  If error return error message
     */
    public function find($table, $field_list, $func = null) {
        try{        
            $this->useModel($table);
            $sql = 'SELECT * FROM `' . $this->table . '` WHERE ' .  
                    implode(' AND ', array_map(function($f) {
                        return '`' . $f . '`=:' . $f;
                    }, array_keys($field_list))) ;
                            
            $stmt = $this->conn->prepare($sql);  
            $stmt->execute($field_list); 
            $result = $stmt->fetchAll();

            if($func != null) {
                return call_user_func($func, $result);
            }
            else 
                return $result; 
        }
        catch (PDOException $e) {
            return $sql . "<br>" . $e->getMessage();
        }
    }
    /**
     * Update model according to a list of conditions
     * @param $table The database table to update
     * @param $field_list associative array with field name and field value
     * @param $condition_list associative array with condition name and condition value
     * @return mixed If succeed return true, else return false;  If error return error message
     */
    public function update($table, $field_list, $condition_list) {
        try{
            // if(isset($table) && is_string($table)) $this->useModel($table) ;
            // else throw new Exception("Missing parameter \$table.");
            $this->useModel($table);
            $setParams = implode(', ', array_map(function($k) {               
                    return '`' . $k . '`=:' . $k; 
            }, array_keys($field_list)));

            $conditionParams = implode(' AND ', array_map(function($id) use ($condition_list) {
                return '`' . $id . '`=' . $condition_list[$id];
            }, array_keys($condition_list)));

            $sql = 'UPDATE `' . $this->table . '` SET ' . $setParams .'  WHERE ' . $conditionParams;

            $stmt = $this->conn->prepare($sql);  
            return $stmt->execute($field_list); 
        }
        catch (PDOException $e) {
            return $sql . "<br>" . $e->getMessage();
        } 
        catch (Exception $e) {
            return "<br>" . $e->getMessage();
        }     
    }

    private function query($sql) {
        return $this->conn->query($sql); 
    }

    private function lastInsertId() {
        return $this->conn->lastInsertId();
    }

}
?>