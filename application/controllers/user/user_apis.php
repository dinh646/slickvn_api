<?php

require APPPATH.'/libraries/REST_Controller.php';

/**
 * 
 * This class support APIs User for client
 *
 * @author Huynh Xinh
 * Date: 8/11/2013
 * 
 */
class user_apis extends REST_Controller{
    
    public function __construct() {
        parent::__construct();
        
        //  Load model USER
        $this->load->model('user/user_model');
        $this->load->model('user/user_enum');
        
        $this->load->model('user/user_log_enum');
        
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs User                                         //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * API All Get User
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_all_user_get() {
        
        //  Get collection 
        $get_collection = $this->user_model->getAllUser();
        
        $error = $this->user_model->getError();
//        echo $error;
        if($error == null){
        
            //  Array object
            $results = array();
            //  Count object
            $count = 0;
            foreach ($get_collection as $value){
                $count ++;
                //  Create JSONObject
                $jsonobject = array( 

                            User_enum::ID                => $value['_id']->{'$id'},
                            User_enum::FULL_NAME         => $value['full_name'],
                            User_enum::EMAIL             => $value['email'],        
                            User_enum::PHONE_NUMBER      => $value['phone_number'],
                            User_enum::ADDRESS           => $value['address'],
                            User_enum::LOCATION          => $value['location'],
                            User_enum::AVATAR            => Common_enum::DOMAIN_NAME.Common_enum::URL_USER_PROFILE.$value['avatar'],
                            User_enum::ROLE_LIST         => $value['role_list'],
                            Common_enum::CREATED_DATE    => $value['created_date']

                           );
                $results[] = $jsonobject;
            }
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        
    }
    
    /**
     * API Get User by Id
     * 
     * Menthod: GET
     * 
     * @param String $id
     * 
     * Response: JSONObject
     * 
     */
    public function get_user_by_id_get() {
        
        //  Get param from client
        $id = $this->get('id');
        
        //  Get collection 
        $get_collection = $this->user_model->getUserById($id);
        
        $error = $this->user_model->getError();
//        echo $error;
        if($error == null){
        
            //  Array object
            $results = array();
            //  Count object
            $count = 0;
            foreach ($get_collection as $value){
                $count ++;
                //  Create JSONObject
                $jsonobject = array( 

                            User_enum::ID                => $value['_id']->{'$id'},
                            User_enum::FULL_NAME         => $value['full_name'],
                            User_enum::EMAIL             => $value['email'],        
                            User_enum::PHONE_NUMBER      => $value['phone_number'],
                            User_enum::ADDRESS           => $value['address'],
                            User_enum::LOCATION          => $value['location'],
                            User_enum::AVATAR            => Common_enum::DOMAIN_NAME.Common_enum::URL_USER_PROFILE.$value['avatar'],
                            User_enum::ROLE_LIST         => $value['role_list'],
                            Common_enum::CREATED_DATE    => $value['created_date']

                           );
                $results[] = $jsonobject;
            }
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        
    }
    
    /**
     * API Update User
     * 
     * Menthod: POST
     * 
     * @param String $action:  insert | edit | delete
     * @param String $full_name
     * @param String $email
     * @param MD5    $password
     * @param String $phone_number
     * @param String $address
     * @param String $location
     * @param String $avatar
     * @param String $created_date
     * @param String $role_list
     * @param String $created_date
     * 
     * Response: JSONObject
     * 
     **/
    public function update_user_post() {
        
        //  Get param from client
        $action         = $this->post('action');

        $id             = $this->post('id');
        
        $full_name      = $this->post('full_name');
        $email          = $this->post('email');
        $password       = $this->post('password');
        $phone_number   = $this->post('phone_number');
        $address        = $this->post('address');
        $location       = $this->post('location');
        $avatar         = $this->post('avatar');
        $created_date   = $this->post('created_date');
        
        $role_list      = $this->post('role_list');// 527b512b3fce119ed62d8599, 527b512b3fce119ed62d8599
        
        (int)$is_insert = strcmp( strtolower($action), Common_enum::INSERT );
        (int)$is_delete = strcmp( strtolower($action), Common_enum::DELETE );
        
        $array_value = ($is_delete != 0) ? 
                
                array(
                        User_enum::FULL_NAME         => $full_name,
                        User_enum::EMAIL             => $email,        
                        User_enum::PASSWORD          => $password,
                        User_enum::PHONE_NUMBER      => $phone_number,
                        User_enum::ADDRESS           => $address,
                        User_enum::LOCATION          => $location,
                        User_enum::AVATAR            => $avatar,
                        User_enum::ROLE_LIST         => ( ($is_insert == 0) ) ? array(User_enum::DEFAULT_ROLE_LIST) : explode(Common_enum::MARK, $role_list),
                        Common_enum::CREATED_DATE    => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                
                ) : array();
        
        $this->user_model->updateUser($action, $id, $array_value);
        $error = $this->user_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs User Log                                     //
    //                                                    //
    //----------------------------------------------------//
    public function update_user_log_post() {
        //  Get param from client
        $id_user        = $this->post('id_user');
        $id_restaurant  = $this->post('id_restaurant');
        $id_assessment  = $this->post('id_assessment');
        $id_comment     = $this->post('id_comment');
        $id_post        = $this->post('id_post');
        $action         = $this->post('action');
        $desc           = $this->post('desc');
        
        $array_value = array(
            
                        User_log_enum::ID_USER              => $id_user,
                        User_log_enum::ID_RESTAURANT        => $id_restaurant,        
                        User_log_enum::ID_ASSESSMENT        => $id_assessment,
                        User_log_enum::ID_COMMENT           => $id_comment,
                        User_log_enum::ID_POST              => $id_post,
                        User_log_enum::ACTION               => $action,
                        User_log_enum::DESC                 => $desc,
                        Common_enum::CREATED_DATE           => $this->common_model->getCurrentDate()
                
                );
        
        $this->user_model->updateUserLog($action, null/*id*/, $array_value);
        $error = $this->user_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        
    }
    
    /**
     * 
     * Like for Restaurant
     * 
     * Menthod: POST
     * 
     * @param String $id_user
     * @param String $id_restaurant
     * 
     * Response: JSONObject
     * 
     */
    public function like_restaurant_post() {
        //  Get param from client
        $id_user        = $this->post('id_user');
        $id_restaurant  = $this->post('id_restaurant');
        
        if($id_user == null || $id_restaurant == null){return;}
        
        $array_value = array(
                        User_log_enum::ID_USER              => $id_user,
                        User_log_enum::ID_RESTAURANT        => $id_restaurant,        
                        User_log_enum::ID_ASSESSMENT        => null,
                        User_log_enum::ID_COMMENT           => null,
                        User_log_enum::ID_POST              => null,
                        User_log_enum::ACTION               => Common_enum::LIKE_RESTAURANT,
                        User_log_enum::DESC                 => 'Like for a restaurant',
                        Common_enum::CREATED_DATE           => $this->common_model->getCurrentDate()
                );
        
        $this->user_model->updateUserLog(Common_enum::INSERT, Common_enum::LIKE, $array_value);
        $error = $this->user_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    /**
     * 
     * Share for Restaurant
     * 
     * Menthod: POST
     * 
     * @param String $id_user
     * @param String $id_restaurant
     * 
     * Response: JSONObject
     * 
     */
    public function share_restaurant_post() {
        //  Get param from client
        $id_user        = $this->post('id_user');
        $id_restaurant  = $this->post('id_restaurant');
        
        if($id_user == null || $id_restaurant == null){return;}
        
        $array_value = array(
                        User_log_enum::ID_USER              => $id_user,
                        User_log_enum::ID_RESTAURANT        => $id_restaurant,        
                        User_log_enum::ID_ASSESSMENT        => null,
                        User_log_enum::ID_COMMENT           => null,
                        User_log_enum::ID_POST              => null,
                        User_log_enum::ACTION               => Common_enum::SHARE_RESTAURANT,
                        User_log_enum::DESC                 => 'Like for a restaurant',
                        Common_enum::CREATED_DATE           => $this->common_model->getCurrentDate()
                );
        
        $this->user_model->updateUserLog(Common_enum::INSERT, Common_enum::SHARE, $array_value);
        $error = $this->user_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    /**
     * 
     * Login
     * 
     * @param String $email
     * @param MD5 $password
     * 
     * Response: JSONObject
     * 
     */
    public function login_post() {
        
        //  Get param from client
        $email      = $this->post('email');
        $password   = $this->post('password');
        
        $user = $this->user_model->login($email, $password);
        
        $results='';
        
        foreach ($user as $value) {
            
            $results[] = array( 

                        Common_enum::ID              => $value['_id']->{'$id'},
                        User_enum::FULL_NAME         => $value['full_name'],
                        User_enum::EMAIL             => $value['email'],        
                        User_enum::PHONE_NUMBER      => $value['phone_number'],
                        User_enum::ADDRESS           => $value['address'],
                        User_enum::LOCATION          => $value['location'],
                        User_enum::AVATAR            => $value['avatar'],
                        User_enum::ROLE_LIST         => $value['role_list'],
            );
                        
        }
//        var_dump(is_array($results));
        if(!is_array($results) || sizeof($results) == 0){
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'    =>'Login fail'
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Role                                         //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * API All Get Role
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_all_role_get() {
        
        //  Get collection 
        $get_collection = $this->user_model->getAllRole();
        
        $error = $this->user_model->getError();
        if($error == null){
        
            //  Array object
            $results = array();
            //  Count object
            $count = 0;
            foreach ($get_collection as $value){
                $count ++;
                //  Create JSONObject
                $jsonobject = array( 

                            Role_enum::ID                    => $value['_id']->{'$id'},
                            Role_enum::NAME                  => $value['name'],
                            Role_enum::DESC                  => $value['desc'],        
                            Role_enum::FUNCTION_LIST         => $value['function_list'],
                            Common_enum::CREATED_DATE        => $value['created_date']

                           );
                $results[] = $jsonobject;
            }
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    /**
     * API All Get Role
     * 
     * Menthod: GET
     * 
     * @param String $id
     * 
     * Response: JSONObject
     * 
     */
    public function get_role_by_id_get() {
        
        //  Get param from client
        $id = $this->get('id');
        
        //  Get collection 
        $get_collection = $this->user_model->getRoleById($id);
        
        $error = $this->user_model->getError();
        if($error == null){
        
            //  Array object
            $results = array();
            //  Count object
            $count = 0;
            foreach ($get_collection as $value){
                $count ++;
                //  Create JSONObject
                $jsonobject = array( 

                            Role_enum::ID                    => $value['_id']->{'$id'},
                            Role_enum::NAME                  => $value['name'],
                            Role_enum::DESC                  => $value['desc'],        
                            Role_enum::FUNCTION_LIST         => $value['function_list'],
                            Common_enum::CREATED_DATE        => $value['created_date']

                           );
                $results[] = $jsonobject;
            }
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    /**
     * API Update Role
     * 
     * Menthod: POST
     * 
     * @param String $action
     * @param String $id
     * @param String $name
     * @param String $desc
     * @param String $function_list
     * @param String $created_date
     * 
     * Response: JSONObject
     * 
     **/
    public function update_role_post() {
        
        //  Get param from client
        $action             = $this->post('action');
        
        $id                 = $this->post('id');
        
        $name               = $this->post('name');
        $desc               = $this->post('desc');
        $function_list      = $this->post('function_list');
        $created_date       = $this->post('created_date');
        
        $array_value = array(
                        Role_enum::NAME              => $name,
                        Role_enum::DESC              => $desc,        
                        Role_enum::FUNCTION_LIST     => explode(Common_enum::MARK, $function_list),
                        Common_enum::CREATED_DATE    => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                
                );
        
        $this->user_model->updateRole($action, $id, $array_value);
        $error = $this->user_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Function                                     //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * API All Get Function
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_all_function_get() {
        
        //  Get collection 
        $get_collection = $this->user_model->getAllFunction();
        
        $error = $this->user_model->getError();
        if($error == null){
        
            //  Array object
            $results = array();
            //  Count object
            $count = 0;
            foreach ($get_collection as $value){
                $count ++;
                //  Create JSONObject
                $jsonobject = array( 

                            function_enum::ID                    => $value['_id']->{'$id'},
                            function_enum::NAME                  => $value['name'],
                            function_enum::DESC                  => $value['desc'],        
                            Common_enum::CREATED_DATE            => $value['created_date']

                           );
                $results[] = $jsonobject;
            }
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    /**
     * API All Get function
     * 
     * Menthod: GET
     * 
     * @param String $id
     * 
     * Response: JSONObject
     * 
     */
    public function get_function_by_id_get() {
        
        //  Get param from client
        $id = $this->get('id');
        
        //  Get collection 
        $get_collection = $this->user_model->getFunctionById($id);
        
        $error = $this->user_model->getError();
        if($error == null){
        
            //  Array object
            $results = array();
            //  Count object
            $count = 0;
            foreach ($get_collection as $value){
                $count ++;
                //  Create JSONObject
                $jsonobject = array( 

                            Role_enum::ID                    => $value['_id']->{'$id'},
                            Role_enum::NAME                  => $value['name'],
                            Role_enum::DESC                  => $value['desc'],        
                            Common_enum::CREATED_DATE        => $value['created_date']

                           );
                $results[] = $jsonobject;
            }
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    /**
     * API Update Function
     * 
     * Menthod: POST
     * 
     * @param String $action
     * @param String $id
     * @param String $name
     * @param String $desc
     * @param String $created_date
     * 
     * Response: JSONObject
     * 
     **/
    public function update_function_post() {
        
        //  Get param from client
        $action             = $this->post('action');
        
        $id                 = $this->post('id');
        
        $name               = $this->post('name');
        $desc               = $this->post('desc');
        $created_date       = $this->post('created_date');
        
        $array_value = array(
                        Role_enum::NAME              => $name,
                        Role_enum::DESC              => $desc,        
                        Common_enum::CREATED_DATE    => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                
                );
        
        $this->user_model->updateFunction($action, $id, $array_value);
        $error = $this->user_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
}
