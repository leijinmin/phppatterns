<?php
class UserModel implements ArrayAccess, Iterator   {

    // Array saving properties
    private $data = [];

    // Class properties with validation pattern and error message
    private $properties = array( 'user_id'      => array(
                                    'pattern'   => '/^\d+$/'
                                    ,'error'    => 'user_id should be an integer'
                                )
                                ,'password'   => array(
                                    'pattern'   => '/^[a-zA-Z]+[a-zA-Z0-9\-_]*[a-zA-Z0-9]+$/'
                                    ,'error'    => 'password should start with a letter and be composed of letters, numbers and -,_'
                                )
                                ,'login_name'   => array(
                                    'pattern'   => '/^[a-zA-Z]+[a-zA-Z0-9\-_]*[a-zA-Z0-9]+$/'
                                    ,'error'    => 'login_name should start with a letter and be composed of letters, numbers and -,_'
                                )
                                ,'firstname'    => array(
                                    'pattern'   => '/^[\p{L}\p{P}\p{Zs}]+$/u'
                                    ,'error'    => 'firstname should be composed of letters'
                                )
                                ,'lastname'     => array(
                                    'pattern'   => '/^[\p{L}\p{P}\p{Zs}]+$/u'
                                    ,'error'    =>  'lastname should be composed of letters'
                                )
                                ,'email'        => array(
                                    'pattern'   => '/^[a-zA-z]+[a-zA-Z0-9\-._]*@[a-zA-Z0-9]+(.)[a-zA-Z]+$/u'
                                    ,'error'    =>  'email should have the format of ****@***.***, start with a letter and be composed of letters, numbers, -, _, and .'
                                )                                    
                                ,'telephone'    => array(
                                    'pattern'   => '/^\d{3}-\d{3}-\d{4}$/u'
                                    ,'error'    =>  'telephone should be composed of numbers and have the format of ###-###-####'
                                )                                    
                                ,'cellphone'    => array(
                                    'pattern'   => '/^\d{3}-\d{3}-\d{4}$/u'
                                    ,'error'    =>  'cellphone should be composed of numbers and have the format of ###-###-####'
                                )            
                                ,'role_id'      => array(
                                    'pattern'   => '/^\d(,\d)*$/'
                                    ,'error'    =>  'role_id should be composed of numbers separated by commas ,'
                                ) 
                                ,'role'         => array(
                                    'pattern'   => '/^[\p{L}\p{P}\p{Zs}]+(,[\p{L}\p{P}\p{Zs}]+)*$/ui'
                                    ,'error'    =>  'role name should be a valid word separated by ,'
                                )
                                ,'status_id'    => array(
                                    'pattern'   => '/^\d$/'
                                    ,'error'    =>  'status_id should be an integer'
                                )
                                ,'status'       => array(
                                    'pattern'   => '/^[\p{L}\p{P}\p{Zs}]+$/u'
                                    ,'error'    =>  'status should be a valid word'
                                )
                                ,'failing_login_count'       => array(
                                    'pattern'   => '/^\d+$/'
                                    ,'error'    =>  'failing_login_count should be a number'
                                ));


    public function __construct($array)
    {
        if (is_array($array) && count($array)>0) {
            $keys = array_keys($array);
            array_walk($keys, function($key) use($array) {
                $this[$key] = $array[$key];
            }, array_keys($array));  
        }     
    }   

        /**
         * ArrayAccess interface method
         * Assigns a value to the specified offset
         *
         * @param $offset string The property to assign the value to
         * @param $value  mixed  The value to set
         */
        public function offsetSet($offset,$value) {
            
            // Verify if the $offset exists in the model properties and passes the validation            
            $isValidProperty = (function() use($offset, $value) {
                // verify if the model contains the property $offset
                if(!array_key_exists($offset, $this->properties)) 
                    throw new Exception("This model doesn't contain the property $offset.");

                // Verify if the property is valid according to the validation pattern
                if (!preg_match($this->properties[$offset]['pattern'], $value)) 
                    throw new Exception("The property $offset with the value $value doesn't pass  the validation. 
                                         The error message: ". $this->properties[$offset]['error']);

                return true;
            })();
            
            // If the offset exists in the properties and passes the validation, assign the value
            if ( $isValidProperty) 
                    $this->data[$offset] = $value;                    
        }
    
        /**
         * ArrayAccess interface method
         * Whether or not an offset exists
         *
         * @param string An offset to check for
         * @access public
         * @return boolean
         * @abstracting ArrayAccess
         */
        public function offsetExists($offset) {
            return isset($this->data[$offset]);
        }
    
        /**
         * ArrayAccess interface method
         * Unsets an offset
         *
         * @param string The offset to unset
         * @access public
         * @abstracting ArrayAccess
         */
        public function offsetUnset($offset) {
            if ($this->offsetExists($offset)) {
                unset($this->data[$offset]);
            }
        }
    
        /**
         * ArrayAccess interface method
         * Returns the value at specified offset
         *
         * @param string The offset to retrieve
         * @access public
         * @return mixed
         * @abstracting ArrayAccess
         */
        public function offsetGet($offset) {
            return $this->offsetExists($offset) ? $this->data[$offset] : null;
        }


        public function rewind() {
            reset($this->data);
        }
    
        public function current() {
            return current($this->data);
        }
    
        public function key() {
            return key($this->data);
        }
    
        public function next() {
            return next($this->data);
        }
    
        public function valid() {
            $key = key($this->data);
            return ($key !== NULL && $key !== FALSE);
        }

}
?>