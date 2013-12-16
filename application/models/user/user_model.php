<?php

/**
 * 
 * This class connect and hands collection User, Role, Funcion
 * 
 */
class User_model extends CI_Model{
    
    public function __construct() {
        parent::__construct();
        
        //  Load model COMMON
        $this->load->model('common/common_enum');
        $this->load->model('common/common_model');
        
        //  Load model USER
        $this->load->model('user/role_enum');
        $this->load->model('user/function_enum');
        $this->load->model('user/user_log_enum');
        
    }
    
    /**
     * 
     * Get Error
     * 
     * @return String $error
     */
    public function getError() {
        return $this->common_model->getError();
    }
    
    /**
     * 
     * Set Error
     * 
     * @return String $error
     * 
     */
    public function setError($e) {
        return $this->common_model->setError($e);
    }
    
    //----------------------------------------------------------------------//
    //                                                                      //
    //                  FUNCTION FOR COLLECTION USER LOG                    //
    //                                                                      //
    //----------------------------------------------------------------------//
    
    //
    //  update 
    //
    
    //
    //  Count login
    //
    
    //
    //  Get list restaurant are visited
    //  
    //
    
    /**
     * 
     * Count User Log by Action of User: LOGIN | VISIT RESTAURANT | ASSESSMENT | COMMENT | LIKE | SHARE
     * 
     * @param String $id_restaurant
     * 
     * @return int
     * 
     */
    public function countUserLogByAction(array $where) {
        return (sizeof( $this->common_model->searchCollection(User_log_enum::COLLECTION_USER_LOG, $where) ));
    }
    
    /**
     * 
     * Update Collection User Log
     * 
     * @param String $id
     * @param Array $array_value
     * 
     * @param String $action:  insert | edit | delete
     * 
     **/
    public function updateUserLog($action, $user_action, array $array_value) {
        
        try{
            
            if($action == null){ 
                $this->setError('Action is null'); return;
            }
            
            else{
                // Connect collection User
                $collection = User_log_enum::COLLECTION_USER_LOG;
                //  Action insert
                if( strcmp( strtolower($action), Common_enum::INSERT ) == 0 ) {
                    if(strcmp(strtoupper($user_action), Common_enum::LIKE ) || strcmp(strtoupper($user_action), Common_enum::SHARE )){
                        //  Remove created_date
                        unset($array_value['desc']);
                        //  Remve desc
                        unset($array_value['created_date']);
                        unset($array_value['updated_date']);
                        $id_user = $array_value['id_user'];
                        $check_id_user = $this->common_model->checkExistValue(User_enum::COLLECTION_USER, array(Common_enum::_ID => new MongoId($id_user)));
                        if(sizeof($check_id_user) == 0){
                            $this->setError('Id user invaild'); return;
                        }
                        $check_liked_or_shared = $this->common_model->checkExistValue($collection, $this->common_model->removeElementArrayNull($array_value) );
                        if(sizeof($check_liked_or_shared) > 0){
                            $this->setError('Was liked or shared'); return;
                        }
                    }
                    $this->common_model->updateCollection($collection, Common_enum::INSERT, null, $array_value );
                }

                //  Action edit
                else if( strcmp( strtolower($action), Common_enum::EDIT ) == 0 ){

                    if($id == null){$this->setError('Is is null'); return;}
                    $array_value[Common_enum::_ID] = new MongoId($id);
                    
                    $this->collection ->save( $array_value );
                }

                //  Action delete
                else if( strcmp( strtolower($action), Common_enum::DELETE ) == 0 ){

                    if($id == null){$this->setError('Id is null'); return;}
                    $where = array(
                                    Common_enum::_ID => new MongoId($id)
                                );
                    
                    $this->collection ->remove( $where );
                }
                else{
                    $this->setError('Action '.$action.' NOT support');
                }
                
            }
        }catch ( MongoConnectionException $e ){
                $this->setError($e->getMessage());
        }catch ( MongoException $e ){
                $this->setError($e->getMessage());
        }
        
    }
    
    /**
     * 
     * Get list id_restaurant by id_user
     * 
     * @param String $id_user
     * @param Array $id_restaurant
     * 
     **/
   public function getRestaurantsLikedByUser($id_user) {
      
       $array_id_restaurant = array();
       
       $value = array(
                   User_log_enum::ID_USER => $id_user,
                   User_log_enum::ACTION =>  Common_enum::LIKE_RESTAURANT
               );
               
       $user_logs = $this->common_model->getCollectionByField(
               User_log_enum::COLLECTION_USER_LOG,
               $value
       );
       if(is_array($user_logs)){
           foreach ($user_logs as $value) {
               $array_id_restaurant [] = $value['id_restaurant'];
           }
       }
       return $array_id_restaurant;
   }
   
   /**
     * 
     * Get list User like Restaurant
     * 
     * @param String $id_restaurant
     * @param Array $id_user
     * 
     **/
   public function getUsersLikedRestaurant($id_restaurant) {
      
       $array_id_restaurant = array();
       
       $value = array(
                   User_log_enum::ID_RESTAURANT => $id_restaurant,
                   User_log_enum::ACTION =>  Common_enum::LIKE_RESTAURANT
               );
               
       $user_logs = $this->common_model->getCollectionByField(
               User_log_enum::COLLECTION_USER_LOG,
               $value
       );
       if(is_array($user_logs)){
           foreach ($user_logs as $value) {
               $array_id_restaurant [] = $value['id_user'];
           }
       }
       return $array_id_restaurant;
   }
    
    //----------------------------------------------------------------------//
    //                                                                      //
    //                  FUNCTION FOR COLLECTION USER                        //
    //                                                                      //
    //----------------------------------------------------------------------//
   
   /**
     * 
     * Search Collection User
     * 
     * @param String $where
     * 
     * @return Array collection User
     * 
     */
    public function searchUser($where) {
        
        //  Collection Restaurant
        $collection = User_enum::COLLECTION_USER;
        $list_user = $this->common_model->searchCollection($collection, $where );
        
        return $list_user;
        
    }
   
    /**
     * 
     * Get Collectin User
     * 
     * Return: Array Collection User
     * 
     */
    public function getAllUser() {

        return $this->common_model->getCollection(User_enum::COLLECTION_USER);
        
    }
    
    /**
     * 
     * Get Collectin User by Id
     * 
     * @param String $id
     * 
     * Return: Collection User
     * 
     */
    public function getUserById($id) {
        
        return $this->common_model->getCollectionById(User_enum::COLLECTION_USER, $id);
        
    }
    
    /**
     * 
     * Update Collection User
     * 
     * @param String $id
     * @param Array $array_value
     * 
     * @param String $action:  insert | edit | delete
     * 
     **/
    public function updateUser($action, $id, array $array_value) {
        try{
            if($action == null){ 
                $this->setError('Action is null'); return;
            }
            else{
                // Connect collection User
                $collection = User_enum::COLLECTION_USER;
                $this->collection = $this->common_model->getConnectDataBase()->$collection;
                
                //  Action insert
                if( strcmp( strtolower($action), Common_enum::INSERT ) == 0 ) {
                    
                    //  Check email
                    $check_email = $this->common_model->checkExistValue(User_enum::COLLECTION_USER, array(User_enum::EMAIL => $array_value[User_enum::EMAIL]) );
                    
                    if(sizeof($check_email) > 0){
                        $this->setError('Existing email'); return;
                    }
                    $this->collection ->insert( $array_value );
                }
                //  Action edit
                else if( strcmp( strtolower($action), Common_enum::EDIT ) == 0 ){

                    if($id == null){$this->setError('Is is null'); return;}
                    $where = array( Common_enum::_ID => new MongoId($id) );
                    
                    $this->collection ->update($where, array( '$set' => $array_value) );
                }
                //  Action delete
                else if( strcmp( strtolower($action), Common_enum::DELETE ) == 0 ){

                    if($id == null){$this->setError('Id is null'); return;}
                    
                    $this->common_model ->editSpecialField($collection, $id, array( '$set' => array(User_enum::IS_DELETE =>1)) );
                }
                else{
                    $this->setError('Action '.$action.' NOT support');
                }
            }
        }catch ( MongoConnectionException $e ){
                $this->setError($e->getMessage());
        }catch ( MongoException $e ){
                $this->setError($e->getMessage());
        }
    }
    
    /**
     * 
     * Get User Role List
     * 
     * @param String $user_id
     * 
     * @return Array id_role_list
     * 
     **/
    public function getUserRoleList($user_id) {
        
        $role_list = $this->getUserById($user_id);
        
        foreach ($role_list as $value){
            return $value['role_list'];
        }
    }
    
    /**
     * 
     * Login
     * 
     * @param String $email
     * @param MD5 $pass
     * 
     * @return Array Collection User
     */
    public function login($email, $pass) {
        
        $array_value = array(
                                User_enum::EMAIL => $email,
                                User_enum::PASSWORD => $pass
                            );
        
        $user = $this->common_model->checkLogin($array_value);
        
        return $user;
        
    }
    
    //----------------------------------------------------------------------//
    //                                                                      //
    //                  FUNCTION FOR COLLECTION ROLE                        //
    //                                                                      //
    //----------------------------------------------------------------------//
    
    /**
     * 
     * Get Collectin Role
     * 
     * Return: Array Collection User
     * 
     */
    public function getAllRole() {

        return $this->common_model->getCollection(Role_enum::COLLECTION_ROLE);
        
    }
    
    /**
     * 
     * Get Collectin Role by Id
     * 
     * @param String $id
     * 
     * Return: Array Collection User
     * 
     */
    public function getRoleById($id) {

        return $this->common_model->getCollectionById(Role_enum::COLLECTION_ROLE, $id);
        
    }
    
    /**
     * 
     * Update Collection Role
     * 
     * @param String $id
     * @param Array $array_value
     * 
     * @param String $action:  insert | edit | delete
     * 
     **/
    public function updateRole($action, $id, array $array_value) {
        
        try{
            if($action == null){ 
                $this->setError('Action is null'); return;
            }
            
            else{
                // Connect collection User
                $collection = Role_enum::COLLECTION_ROLE;
                $this->collection = $this->common_model->getConnectDataBase()->$collection;
                
                //  Action insert
                if( strcmp( strtolower($action), Common_enum::INSERT ) == 0 ) {
                    
                    $this->collection ->insert( $array_value );
                    
                }

                //  Action edit
                else if( strcmp( strtolower($action), Common_enum::EDIT ) == 0 ){

                    if($id == null){$this->setError('Is is null'); return;}
                    
                    $where = array(
                                    Common_enum::_ID => new MongoId($id)
                                );
                    
                    $this->collection ->update($where, array('$set' => $array_value) );
                }

                //  Action delete
                else if( strcmp( strtolower($action), Common_enum::DELETE ) == 0 ){
                    
                    if($id == null){$this->setError('Is is null'); return;}
                    
                    
                    
                    $where = array(
                                    Common_enum::_ID => new MongoId($id)
                                );
                    
                    $this->collection ->remove( $where );
                }
                else{
                    $this->setError('Action '.$action.' NOT support');
                }
                
            }
        }catch ( MongoConnectionException $e ){
                $this->setError($e->getMessage());
        }catch ( MongoException $e ){
                $this->setError($e->getMessage());
        }
    }
    
    //----------------------------------------------------------------------//
    //                                                                      //
    //                  FUNCTION FOR COLLECTION FUNCTION                        //
    //                                                                      //
    //----------------------------------------------------------------------//
    
    /**
     * 
     * Get Collectin Function
     * 
     * Return: Array Collection Function
     * 
     */
    public function getAllFunction() {

        return $this->common_model->getCollection(Function_enum::COLLECTION_FUNCTION);
        
    }
    
    /**
     * 
     * Get Collectin Function by Id
     * 
     * @param String $id
     * 
     * Return: Array Collection Function
     * 
     */
    public function getFunctionById($id) {

        return $this->common_model->getCollectionById(Function_enum::COLLECTION_FUNCTION, $id);
        
    }
    
    /**
     * 
     * Update Collection Function
     * 
     * @param String $id
     * @param Array $array_value
     * 
     * @param String $action:  insert | edit | delete
     * 
     **/
    public function updateFunction($action, $id, array $array_value) {
        
        try{
            if($action == null){ 
                $this->setError('Action is null'); return;
            }
            
            else{
                // Connect collection Function
                $collection = Function_enum::COLLECTION_FUNCTION;
                $this->collection = $this->common_model->getConnectDataBase()->$collection;
                
                //  Action insert
                if( strcmp( strtolower($action), Common_enum::INSERT ) == 0 ) {
                    
                    $this->collection ->insert( $array_value );
                    
                }

                //  Action edit
                else if( strcmp( strtolower($action), Common_enum::EDIT ) == 0 ){

                    if($id == null){$this->setError('Is is null'); return;}
                    
                    $where = array(
                                    Common_enum::_ID => new MongoId($id)
                                );
                    
                    $this->collection ->update($where, array('$set' => $array_value) );
                }

                //  Action delete
                else if( strcmp( strtolower($action), Common_enum::DELETE ) == 0 ){
                    
                    if($id == null){$this->setError('Is is null'); return;}
                    
                    
                    
                    $where = array(
                                    Common_enum::_ID => new MongoId($id)
                                );
                    
                    $this->collection ->remove( $where );
                }
                else{
                    $this->setError('Action '.$action.' NOT support');
                }
                
            }
        }catch ( MongoConnectionException $e ){
                $this->setError($e->getMessage());
        }catch ( MongoException $e ){
                $this->setError($e->getMessage());
        }
    }
    
}

?>
