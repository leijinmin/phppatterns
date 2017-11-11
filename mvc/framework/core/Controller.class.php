<?php
class Controller {

    public function __construct() {

    }

    protected function redirect($url) {
        header("Location:$url");
        exit;
    }

    protected function view($viewname, $model = null) {
        ob_start();
        include CURR_VIEW_PATH . "{$viewname}.php";
        return  ob_get_clean();
    }
    /**
     * @param $modelName The model to map
     * @param $notBind   The fields not to bind
     */
    protected function mapToModel($modelName, $notBind = array()) {
        $user = new $modelName(array());
        while( list( $field, $value ) = each( $_POST )) {
            if(!in_array($field, $notBind)) {
                $user[$field] = $value;
            }           
         }
         return $user;

    }
}
?>