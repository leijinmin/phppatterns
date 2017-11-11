<?php
class HomeController extends Controller {
    private $context;

    public function __construct() {
        $this->context  = new DBContext();
    }

    public function get_index() {
        return parent::view('index','This is the home page accessed by everyone.');
    }

    public function get_login() {
        return parent::view('login'); 
    }    

    public function post_login() {

        if (isset($_POST['login_name']) && isset($_POST['password'])) {
            try{
                // Get user_id
                $user_id =  $this->context->executeFunction('func_verify_login'
                                                            ,array($_POST['login_name'],$_POST['password']))[0];
                if($user_id > 0) {
                    $user = $this->context->find('view_user',array('user_id'=>$user_id), function($result){
                        return new UserModel(array('user_id' => $result[0]['user_id']
                                        , 'login_name'   => $result[0]['login_name']
                                        , 'firstname'   => $result[0]['firstname']
                                        , 'lastname'   => $result[0]['lastname']
                                        , 'email'   => $result[0]['email']
                                        , 'telephone'   => $result[0]['telephone']
                                        , 'cellphone'   => $result[0]['cellphone']
                                        , 'status_id'   => $result[0]['status_id']
                                        // , 'status'   => $result[0]['status']
                                        , 'role_id'   => str_replace(' ','',$result[0]['role_id']) 
                                        // , 'role'   => str_replace(' ','',$result[0]['role']) 
                                        , 'failing_login_count'   => $result[0]['failing_login_count'] == null? 0: $result[0]['failing_login_count'] ));
                    });

                    $_SESSION['user'] = $user;
                    $_SESSION['role'] = explode(',', $user['role_id']); 

                    // Reset truncate to default 0
                    $this->context->update('event'
                    , array('truncate'=>0)
                    , array('truncate'=>1, 'user_id'=>$user['user_id']));

                    parent::redirect(APP_CONTAINER . 'home/Home/index');
                }
                else {
                    // The user_id <= 0 is the error code
                    $_POST['error'] = array( '-4'=> 'The passowrd is incorrect.'
                                            ,'-3'=> 'The login name doesn\'t exist'
                                            ,'-2'=> 'The account is deactivated'
                                            ,'0' => 'The account is locked')[(string)$user_id];
                    sleep(4); 
                }   
            }
            catch (Exception $e) {
                $_POST['error'] = $e->getMessage();
                return parent::view('login', $_POST); 
            }                           
        } 
        return parent::view('login', $_POST);            
    }

    public function get_logout() {
        session_destroy();
        parent::redirect(APP_CONTAINER . 'home/Home/index');
    }

    public function get_signin() {
        return parent::view('signin');
    }

    public function post_signin() {
        try{
            if ($this->validPasswordAndEmail()) {
                $user = parent::mapToModel('UserModel', array('password2','email2'));            
                $this->context->executeProcedure('proc_create_user',array($user['login_name'] 
                ,$user['firstname']
                ,$user['lastname'] 
                ,$user['email']
                ,$user['telephone'] 
                ,$user['cellphone']
                ,1 
                ,'1,2,3' 
                ,$user['password']));
                parent::redirect(APP_CONTAINER . 'home/Home/login');
            }
        }
        catch(Exception $e) {
            $_POST['error'] = $e->getMessage();
            return parent::view('signin', $_POST);
        }
    }

    private function validPasswordAndEmail() {
        if($_POST['password2'] != $_POST['password'])
            throw new Exception('The 2nd password is not equivalent with the 1st.');
    
        if($_POST['email2'] != $_POST['email'])
            throw new Exception('The 2nd email is not equivalent with the 1st.');

        return true;
    }
}
?>