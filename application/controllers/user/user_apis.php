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
        $this->load->model('common/encode_utf8');
        
        $this->load->model('common/list_point_enum');
        
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs User                                         //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * API get point of User by id
     * 
     * Menthod: GET
     * @param String $id
     * Response: JSONObject
     * 
     */
    public function get_point_of_user_get() {
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
            if(is_array($get_collection)){
                foreach ($get_collection as $value){

                    if($value['is_delete'] == 0){
                        $count ++;
                        //  Create JSONObject
                        $jsonobject = array( 

                                    User_enum::ID                => $value['_id']->{'$id'},
                                    User_enum::FULL_NAME         => $value['full_name'],
                                    User_enum::POINT             => $value['point'],
                                    Common_enum::UPDATED_DATE    => $value['updated_date'],
                                    Common_enum::CREATED_DATE    => $value['created_date']

                                   );
                        $results[] = $jsonobject;
                    }
                }
            }
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    /**
     * API search User by name
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function search_user_get() {
        //  Get limit from client
        $limit = $this->get("limit");
        //  Get page from client
        $page = $this->get("page");
        
        $key = Encode_utf8::toUTF8($this->get('key'));
        
        //  Query
        $where_select_by_name = array(User_enum::FULL_NAME => new MongoRegex('/'.$key.'/i'));
        $where_select_by_email = array(User_enum::EMAIL => new MongoRegex('/'.$key.'/i'));
        $where_select_by_phone = array(User_enum::PHONE_NUMBER => new MongoRegex('/'.$key.'/i'));
        
        $where = array();
        
        if(is_numeric($key)){
            $where = array( '$or'=>array($where_select_by_name, $where_select_by_email, $where_select_by_phone) );
        }else{
            $where = array( '$or'=>array($where_select_by_name, $where_select_by_email) );
        }
        
        
        $list_user = $this->user_model->searchUser($where);
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object
        $results = array();
        //  Count object
        $count = 0;
        if(is_array($list_user)){
            foreach ($list_user as $value){
                if($value['is_delete'] == 0){
                    $count ++;
                    if(($count) >= $position_start_get && ($count) <= $position_end_get){
                        //  Create JSONObject
                        $jsonobject = array( 

                                    User_enum::ID                => $value['_id']->{'$id'},
                                    User_enum::FULL_NAME         => $value['full_name'],
                                    User_enum::EMAIL             => $value['email'],        
                                    User_enum::PHONE_NUMBER      => $value['phone_number'],
                                    User_enum::ADDRESS           => $value['address'],
                                    User_enum::LOCATION          => $value['location'],
                                    User_enum::AVATAR            => $value['avatar'],
                                    User_enum::IS_DELETE         => $value['is_delete'],
                                    User_enum::DESC              => $value['desc'],
                                    User_enum::ROLE_LIST         => $value['role_list'],
                                    Common_enum::UPDATED_DATE    => $value['updated_date'],
                                    Common_enum::CREATED_DATE    => $value['created_date']

                                   );
                        $results[] = $jsonobject;
                    }
                }
            }
        }
        $data =  array(
               'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
    }
    
    /**
     * API Get all User
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_all_user_get() {
        //  Get limit from client
        $limit = $this->get("limit");
        //  Get page from client
        $page = $this->get("page");
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        //  Get collection 
        $get_collection = $this->user_model->getAllUser();
        
        $error = $this->user_model->getError();
//        echo $error;
        if($error == null){
        
            //  Array object
            $results = array();
            //  Count object
            $count = 0;
            if(is_array($get_collection)){
                foreach ($get_collection as $value){

                    if($value['is_delete'] == 0){
                        $count ++;
                        if(($count) >= $position_start_get && ($count) <= $position_end_get){
                            //  Create JSONObject
                            $jsonobject = array( 

                                        User_enum::ID                => $value['_id']->{'$id'},
                                        User_enum::FULL_NAME         => $value['full_name'],
                                        User_enum::EMAIL             => $value['email'],        
                                        User_enum::PHONE_NUMBER      => $value['phone_number'],
                                        User_enum::ADDRESS           => $value['address'],
                                        User_enum::LOCATION          => $value['location'],
                                        User_enum::AVATAR            => $value['avatar'],
                                        User_enum::POINT             => $value['point'],
                                        User_enum::IS_DELETE         => $value['is_delete'],
                                        User_enum::DESC              => $value['desc'],
                                        User_enum::POINT             => $value['point'],
                                        User_enum::ROLE_LIST         => $value['role_list'],
                                        Common_enum::UPDATED_DATE    => $value['updated_date'],
                                        Common_enum::CREATED_DATE    => $value['created_date']

                                       );
                            $results[] = $jsonobject;
                        }
                    }
                }
            }
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
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
            if(is_array($get_collection)){
                foreach ($get_collection as $value){

                    if($value['is_delete'] == 0){
                        $count ++;
                        //  Create JSONObject
                        $jsonobject = array( 

                                    User_enum::ID                => $value['_id']->{'$id'},
                                    User_enum::FULL_NAME         => $value['full_name'],
                                    User_enum::EMAIL             => $value['email'],        
                                    User_enum::PHONE_NUMBER      => $value['phone_number'],
                                    User_enum::ADDRESS           => $value['address'],
                                    User_enum::LOCATION          => $value['location'],
                                    User_enum::AVATAR            => $value['avatar'],
                                    User_enum::POINT             => $value['point'],
                                    User_enum::ROLE_LIST         => $value['role_list'],
                                    User_enum::DESC              => $value['desc'],
                                    User_enum::POINT             => $value['point'],
                                    User_enum::IS_DELETE         => $value['is_delete'],
                                    Common_enum::UPDATED_DATE    => $value['updated_date'],
                                    Common_enum::CREATED_DATE    => $value['created_date']

                                   );
                        $results[] = $jsonobject;
                    }
                }
            }
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        
    }
    
    public function check_permission_user_post() {
        //  Get param from client
        $id_role = $this->post('id_role');
        $array_role = $this->user_model->getRoleById($id_role);
//        var_dump($array_role);  
        
        if($array_role == null){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>''
            );
            $this->response($data);
        }
        else{
            $role = $array_role[$id_role];
            $function_list = $role['function_list'];
            var_dump($function_list);
        }
    }
    
    /**
     * Get Active Members
     * 
     * Menthod: GET
     * 
     * @param int $limit
     * @param int $page
     * 
     * Response: JSONObject
     **/
//    public function get_active_members_get() {
//        //  Get limit from client
//        $limit = $this->get("limit");
//        //  Get page from client
//        $page = $this->get("page");
//        //  End
//        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
//        //  Start
//        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
//        //  Get collection 
//        $get_collection = $this->user_model->getAllUser();
//        $results = array();
//        $count = 0;
//        if(is_array($get_collection)){
//            foreach ($get_collection as $value) {
//                $number_assessment = $this->user_model->countUserLogByAction(array ( 
//                                                                                User_log_enum::ID_USER => $value['_id']->{'$id'}, 
//                                                                                User_log_enum::ACTION        => Common_enum::ASSESSMENT_RESTAURANT
//                                                                                ));
//                if($number_assessment>=Common_enum::LEVEL_ACTIVE_MEMBERS){
//                    $count ++;
//                    if(($count) >= $position_start_get && ($count) <= $position_end_get){
//                        //  Create JSONObject Restaurant
//                        $jsonobject = array( 
//                                            User_enum::FULL_NAME => $value['full_name'],
//                                            User_enum::AVATAR => $value['avatar'],
//                                            User_enum::NUMBER_ASSESSMENT => $number_assessment
//                                            );
//                        $results [] = $jsonobject;
//                    }
//                }
//            }
//        }
//        //  Response
//        $data =  array(
//               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
//               'Total'      =>sizeof($results),
//               'Results'    =>$results
//        );
//        $this->response($data);
//    }
    
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
        $desc           = $this->post('desc');
        $point          = $this->post('point');
        $delete         = $this->post('delete');
        $created_date   = $this->post('created_date');
        $updated_date   = $this->post('updated_date');
        
        $role_list      = $this->post('role_list');// 527b512b3fce119ed62d8599, 527b512b3fce119ed62d8599
        
        $file_temp = Common_enum::ROOT.Common_enum::PATH_TEMP;
        $path_avatar = Common_enum::ROOT.Common_enum::DIR_USER_PROFILE;
        
        (int)$is_insert = strcmp( strtolower($action), Common_enum::INSERT );
        (int)$is_edit = strcmp( strtolower($action), Common_enum::EDIT );
        (int)$is_delete = strcmp( strtolower($action), Common_enum::DELETE );
        
        if($is_insert == 0){
            //  Create directory $path
            $this->common_model->createDirectory($path_avatar, Common_enum::WINDOWN);

            if(file_exists($file_temp)){
                $move_file_avatar = $this->common_model->moveFileToDirectory($file_temp.$avatar, $path_avatar.$avatar);
                if(!$move_file_avatar){
                    $this->common_model->setError('Move file avatar '.$move_file_avatar);
                }
            }
        }
        else if($is_edit == 0){
            
            $new_old_avatar = explode(Common_enum::MARK, $avatar);
            
            $new_avatar = $new_old_avatar[0];
            $old_avatar = $new_old_avatar[1];
            
            $file_new_avatar = $path_avatar.$new_avatar;
            $file_old_avatar = $path_avatar.$old_avatar;
            
            if(!file_exists($file_new_avatar)){
                unlink($file_old_avatar);
                $move_file_avatar = $this->common_model->moveFileToDirectory($file_temp.$new_avatar, $file_new_avatar);
                if(!$move_file_avatar){
                    $this->common_model->setError('Move file avatar '.$move_file_avatar);
                }
                $avatar = $new_avatar;
            }
            else{
                $avatar = $old_avatar;
            }
        }
        $array_value = ($is_delete != 0) ? 
                array(
                        User_enum::FULL_NAME         => $full_name,
                        User_enum::EMAIL             => $email,        
                        User_enum::PASSWORD          => $password,
                        User_enum::PHONE_NUMBER      => $phone_number,
                        User_enum::ADDRESS           => $address,
                        User_enum::LOCATION          => $location,
                        User_enum::AVATAR            => $avatar,
                        User_enum::DESC              => $desc,
                        User_enum::POINT             => ($is_insert == 0)? 0 : null,
                        User_enum::IS_DELETE         => ($delete == null) ? 0 : $delete,
                        User_enum::ROLE_LIST         => ($role_list == null) ? array(User_enum::DEFAULT_ROLE_LIST) : explode(Common_enum::MARK, $role_list),
                        Common_enum::UPDATED_DATE    => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date,
                        Common_enum::CREATED_DATE    => ($created_date == null) ? $this->common_model->getCurrentDate(): $created_date
                ) : array();
        if( $array_value['password'] == null ){
            unset($array_value['password']);
        }
//        var_dump($array_value);
        $this->user_model->updateUser($action, $id, $this->common_model->removeElementArrayNull($array_value));
        $error = $this->user_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
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
//    public function update_user_log_post() {
//        //  Get param from client
//        $id_user        = $this->post('id_user');
//        $id_restaurant  = $this->post('id_restaurant');
//        $id_assessment  = $this->post('id_assessment');
//        $id_comment     = $this->post('id_comment');
//        $id_post        = $this->post('id_post');
//        $action         = $this->post('action');
//        $desc           = $this->post('desc');
//        $created_date   = $this->post('created_date');
//        $updated_date   = $this->post('updated_date');
//        
//        $array_value = array(
//            
//                        User_log_enum::ID_USER              => $id_user,
//                        User_log_enum::ID_RESTAURANT        => $id_restaurant,        
//                        User_log_enum::ID_ASSESSMENT        => $id_assessment,
//                        User_log_enum::ID_COMMENT           => $id_comment,
//                        User_log_enum::ID_POST              => $id_post,
//                        User_log_enum::ACTION               => $action,
//                        User_log_enum::DESC                 => $desc,
//                        Common_enum::UPDATED_DATE    => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date,
//                        Common_enum::CREATED_DATE    => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
//                
//                );
//        
//        $this->user_model->updateUserLog($action, null/*id*/, $array_value);
//        $error = $this->user_model->getError();
//        
//        if($error == null){
//            $data =  array(
//                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
//                   'Error'      =>$error
//            );
//            $this->response($data);
//        }
//        else{
//            $data =  array(
//                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
//                   'Error'      =>$error
//            );
//            $this->response($data);
//        }
//        
//    }
    
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
        $created_date   = $this->post('created_date');
        $updated_date   = $this->post('updated_date');
        
        if($id_user == null || $id_restaurant == null){return;}
        
        $array_value = array(
                        User_log_enum::ID_USER              => $id_user,
                        User_log_enum::ID_RESTAURANT        => $id_restaurant,        
                        User_log_enum::ID_ASSESSMENT        => null,
                        User_log_enum::ID_COMMENT           => null,
                        User_log_enum::ID_POST              => null,
                        User_log_enum::ACTION               => Common_enum::LIKE_RESTAURANT,
                        User_log_enum::DESC                 => 'Like for a restaurant',
                        Common_enum::UPDATED_DATE    => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date,
                        Common_enum::CREATED_DATE    => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                );
        
        $this->user_model->updateUserLog(Common_enum::INSERT, Common_enum::LIKE, $array_value);
        $error = $this->user_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
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
        $created_date   = $this->post('created_date');
        $updated_date   = $this->post('updated_date');
        
        if($id_user == null || $id_restaurant == null){return;}
        
        $array_value = array(
                        User_log_enum::ID_USER              => $id_user,
                        User_log_enum::ID_RESTAURANT        => $id_restaurant,        
                        User_log_enum::ID_ASSESSMENT        => null,
                        User_log_enum::ID_COMMENT           => null,
                        User_log_enum::ID_POST              => null,
                        User_log_enum::ACTION               => Common_enum::SHARE_RESTAURANT,
                        User_log_enum::DESC                 => 'Share for a restaurant',
                        Common_enum::UPDATED_DATE           => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date,
                        Common_enum::CREATED_DATE           => ($created_date==null) ? $this->common_model->getCurrentDate() : $created_date
                );
        
        $this->user_model->updateUserLog(Common_enum::INSERT, Common_enum::SHARE, $array_value);
        $error = $this->user_model->getError();
        if($error == null){
            $this->common_model->editSpecialField(User_enum::COLLECTION_USER, $id_user, array('$inc' => array(User_enum::POINT => List_point_enum::SHARE) ) );
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
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
        $results= array();
        if(is_array($user)){
            foreach ($user as $value) {
                $results[] = array( 
                            Common_enum::ID              => $value['_id']->{'$id'},
                            User_enum::FULL_NAME         => $value['full_name'],
                            User_enum::EMAIL             => $value['email'],        
                            User_enum::PHONE_NUMBER      => $value['phone_number'],
                            User_enum::ADDRESS           => $value['address'],
                            User_enum::LOCATION          => $value['location'],
                            User_enum::POINT             => $value['point'],
                            User_enum::AVATAR            => $value['avatar'],
                            User_enum::ROLE_LIST         => $value['role_list'],
                            Common_enum::UPDATED_DATE    => $value['updated_date'],
                            Common_enum::CREATED_DATE    => $value['created_date']
                );
            }
        }
//        var_dump(is_array($results));
        if(!is_array($results) || sizeof($results) == 0){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'    =>'Login fail'
            );
            $this->response($data);
        }
        else{
            $this->user_model->updateUserLog(Common_enum::INSERT, null, 
                                                array(
                                                    User_log_enum::ID_USER              => $value['_id']->{'$id'},
                                                    User_log_enum::ID_RESTAURANT        => '',        
                                                    User_log_enum::ID_ASSESSMENT        => '',
                                                    User_log_enum::ID_COMMENT           => '',
                                                    User_log_enum::ID_POST              => '',
                                                    User_log_enum::ACTION               => Common_enum::LOGIN,
                                                    User_log_enum::DESC                 => Common_enum::LOGIN,
                                                    Common_enum::CREATED_DATE           => $this->common_model->getCurrentDate()
                                                )
                                            );
            $this->common_model->editSpecialField(User_enum::COLLECTION_USER, $results['id'], array('$inc' => array(User_enum::POINT => List_point_enum::LOGIN) ) );
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
            if(is_array($get_collection)){
                foreach ($get_collection as $value){
                    $count ++;
                    //  Create JSONObject
                    $jsonobject = array( 

                                Role_enum::ID                    => $value['_id']->{'$id'},
                                Role_enum::NAME                  => $value['name'],
                                Role_enum::DESC                  => $value['desc'],        
                                Role_enum::FUNCTION_LIST         => $value['function_list'],
                                Common_enum::UPDATED_DATE    => $value['updated_date'],
                                Common_enum::CREATED_DATE    => $value['created_date']

                               );
                    $results[] = $jsonobject;
                }
            }
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
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
            if(is_array($get_collection)){
                foreach ($get_collection as $value){
                    $count ++;
                    //  Create JSONObject
                    $jsonobject = array( 

                                Role_enum::ID                    => $value['_id']->{'$id'},
                                Role_enum::NAME                  => $value['name'],
                                Role_enum::DESC                  => $value['desc'],        
                                Role_enum::FUNCTION_LIST         => $value['function_list'],
                                Common_enum::UPDATED_DATE        => $value['updated_date'],
                                Common_enum::CREATED_DATE        => $value['created_date']

                               );
                    $results[] = $jsonobject;
                }
            }
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
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
        $updated_date       = $this->post('updated_date');
        
        $array_value = array(
                        Role_enum::NAME              => $name,
                        Role_enum::DESC              => $desc,        
                        Role_enum::FUNCTION_LIST     => explode(Common_enum::MARK, $function_list),
                        Common_enum::UPDATED_DATE    => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date,
                        Common_enum::CREATED_DATE    => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                
                );
        
        $this->user_model->updateRole($action, $id, $array_value);
        $error = $this->user_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
            if(is_array($get_collection)){
                foreach ($get_collection as $value){
                    $count ++;
                    //  Create JSONObject
                    $jsonobject = array( 

                                function_enum::ID                    => $value['_id']->{'$id'},
                                function_enum::NAME                  => $value['name'],
                                function_enum::DESC                  => $value['desc'],        
                                Common_enum::UPDATED_DATE    => $value['updated_date'],
                                Common_enum::CREATED_DATE    => $value['created_date']

                               );
                    $results[] = $jsonobject;
                }
            }
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
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
            if(is_array($get_collection)){
                foreach ($get_collection as $value){
                    $count ++;
                    //  Create JSONObject
                    $jsonobject = array( 

                                Role_enum::ID                    => $value['_id']->{'$id'},
                                Role_enum::NAME                  => $value['name'],
                                Role_enum::DESC                  => $value['desc'],        
                                Common_enum::UPDATED_DATE    => $value['updated_date'],
                                Common_enum::CREATED_DATE    => $value['created_date']

                               );
                    $results[] = $jsonobject;
                }
            }
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>$count,
                   'Results'    =>$results
            );
            $this->response($data);
            
        }else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
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
        $updated_date       = $this->post('updated_date');
        
        $array_value = array(
            
                        Role_enum::NAME              => $name,
                        Role_enum::DESC              => $desc,        
                        Common_enum::UPDATED_DATE    => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date,
                        Common_enum::CREATED_DATE    => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                
                );
        
        $this->user_model->updateFunction($action, $id, $array_value);
        $error = $this->user_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    
    
}
