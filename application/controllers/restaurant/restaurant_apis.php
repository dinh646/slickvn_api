<?php

require APPPATH.'/libraries/REST_Controller.php';
/**
 * 
 * This class support APIs Restaurant for client
 *
 * @author Huynh Xinh
 * Date: 8/11/2013
 * 
 */
class restaurant_apis extends REST_Controller{
    
    public function __construct() {
        parent::__construct();
        
        //  Load model RESTAURANT
        $this->load->model('restaurant/restaurant_model');
        $this->load->model('restaurant/restaurant_enum');
        $this->load->model('restaurant/coupon_enum');
        $this->load->model('restaurant/post_enum');
        $this->load->model('restaurant/subscribed_email_enum');
        $this->load->model('restaurant/menu_dish_enum');
        
        //  Load model COMMON
        $this->load->model('common/common_model');
        $this->load->model('common/common_enum');
        $this->load->model('common/encode_utf8');
        
        //  Load model USER
        $this->load->model('user/user_model');
        $this->load->model('user/user_enum');
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Assessment                                   //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Assessment by Id Restaurant
     * 
     * @param int $limit
     * @param int $page
     * @param String $id_restaurant
     * 
     * Response: JSONObject
     * 
     */
    public function get_assessment_by_id_restaurant_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
        
        $id_restaurant = $this->get('id_restaurant');
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get collection Assessment
        $list_assessment    = $this->restaurant_model->getAssessmentByIdRestaurant($id_restaurant);
        
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if(is_array($list_assessment)){
            foreach ($list_assessment as $assessment){

                $approval = $assessment['approval'];

                if( strcmp(strtoupper($approval), AssessmentEnum::APPROVAL_YES) == 0){

                    $count ++ ;

                    if(($count) >= $position_start_get && ($count) <= $position_end_get){

                        //  Get User of Assessment
                        $user = $this->user_model->getUserById($assessment['id_user']);

                        //  Create JSONObject Restaurant
                        $jsonobject = array( 

                            AssessmentEnum::ID                          => $assessment['_id']->{'$id'},
                            AssessmentEnum::ID_USER                     => $assessment['id_user'],
                            AssessmentEnum::ID_RESTAURANT               => $assessment['id_restaurant'],
                            Userenum::FULL_NAME                         => $user[$assessment['id_user']]['full_name'],
                            Userenum::AVATAR                            => $user[$assessment['id_user']]['avatar'],
                            Userenum::NUMBER_ASSESSMENT                 => $this->restaurant_model->countAssessmentForUser($assessment['id_user']),
                            AssessmentEnum::CONTENT                     => $assessment['content'],

                            AssessmentEnum::RATE_SERVICE                => $assessment['rate_service'],
                            AssessmentEnum::RATE_LANDSCAPE              => $assessment['rate_landscape'],
                            AssessmentEnum::RATE_TASTE                  => $assessment['rate_taste'],
                            AssessmentEnum::RATE_PRICE                  => $assessment['rate_price'],

                            //  Number LIKE of Assessment
                            AssessmentEnum::NUMBER_LIKE                 => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            UserLogEnum::ID_ASSESSMENT => $assessment['_id']->{'$id'}, 
                                                                                                                            UserLogEnum::ACTION        => Common_enum::LIKE_ASSESSMENT
                                                                                                                            )),
                            //  Number SHARE of Assessment
                            AssessmentEnum::NUMBER_SHARE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            UserLogEnum::ID_ASSESSMENT => $assessment['_id']->{'$id'}, 
                                                                                                                            UserLogEnum::ACTION        => Common_enum::SHARE_ASSESSMENT
                                                                                                                            )),
                            AssessmentEnum::COMMENT_LIST                =>  $this->restaurant_model->getCommentByIdAssessment($assessment['_id']->{'$id'}),
                            Common_enum::UPDATED_DATE                   => $assessment['updated_date'],
                            Common_enum::CREATED_DATE                    => $assessment['created_date']


                        );

                        $results[] = $jsonobject;

                    }
                }
            }
        }
        //  Response
        $data =  array(
               'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
        
    }
    
    /**
     * 
     *  API update Assessment
     * 
     *  Menthod: POST
     * 
     *  Response: JSONObject
     * 
     */
    public function update_assement_post(){
        
        //  Get param from client
        $action = $this->post('action');
        $id = $this->post('id');
        
        $id_user = $this->post('id_user');
        $id_restaurant = $this->post('id_restaurant');
        $content = $this->post('content');
        $rate_service = $this->post('rate_service');
        $rate_landscape = $this->post('rate_landscape');
        $rate_taste = $this->post('rate_taste');
        $rate_price = $this->post('rate_price');
        $approval = $this->post('approval');
        $updated_date = $this->post('updated_date');
        $created_date = $this->post('created_date');
            
        $array_value = array(
            Assessment_enum::ID_USER => $id_user,
            Assessment_enum::ID_RESTAURANT => $id_restaurant,
            Assessment_enum::CONTENT => $content,
            Assessment_enum::RATE_SERVICE => (int)$rate_service,
            Assessment_enum::RATE_LANDSCAPE => (int)$rate_landscape,
            Assessment_enum::RATE_TASTE => (int)$rate_taste,
            Assessment_enum::RATE_PRICE => (int)$rate_price,
            Assessment_enum::APPROVAL => $approval,
            Common_enum::UPDATED_DATE       => ($updated_date == null ) ? $this->common_model->getCurrentDate(): $updated_date,
            Common_enum::CREATED_DATE       => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
        );
        
        $this->restaurant_model->updateAssessment($action, $id, $array_value);
        $error = $this->restaurant_model->getError();
        
        if($error == null){
            
            $this->user_model->updateUserLog(Common_enum::INSERT, null, 
                                                array(
                                                    User_log_enum::ID_USER              => $id_user,
                                                    User_log_enum::ID_RESTAURANT        => $id_restaurant,        
                                                    User_log_enum::ID_ASSESSMENT        => '',
                                                    User_log_enum::ID_COMMENT           => '',
                                                    User_log_enum::ID_POST              => '',
                                                    User_log_enum::ACTION               => Common_enum::ASSESSMENT_RESTAURANT,
                                                    User_log_enum::DESC                 => Common_enum::ASSESSMENT_RESTAURANT,
                                                    Common_enum::CREATED_DATE           => $this->common_model->getCurrentDate()
                                                )
                                            );
            
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Menu Dish                                    //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     *  API get all Menu Dish
     * 
     *  Menthod: GET
     * 
     *  Response: JSONObject
     * 
     */
//    public function get_all_menu_dish_get() {
//        
//        //  Get limit from client
//        $limit = $this->get("limit");
//        
//        //  Get page from client
//        $page = $this->get("page");
//        
//        //  End
//        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
//        
//        //  Start
//        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
//        
//        $list_menu_dish = $this->restaurant_model->getMenuDish();
//        
//        $results = array();
//        
//        $count = 0;
//        if(is_array($list_menu_dish)){
//            foreach ($list_menu_dish as $menu_dish) {
//                $count ++ ;
//                if(($count) >= $position_start_get && ($count) <= $position_end_get){
//
//                    $jsonobject = array(
//                            Menu_dish_enum::ID                => $menu_dish['_id']->{'$id'},
//                            Menu_dish_enum::ID_RESTAURANT     => $menu_dish['id_restaurant'],
//                            Menu_dish_enum::DISH_LIST         => $menu_dish['dish_list'],       
//                            Common_enum::UPDATED_DATE         => $menu_dish['updated_date'],
//                            Common_enum::CREATED_DATE         => $menu_dish['created_date']
//                        );
//                    $results [] = $jsonobject;
//                }
//            }
//        }
//        
//        //  Response
//        $data =  array(
//               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
//               'Total'      =>  sizeof($results),
//               'Results'    =>$results
//        );
//        $this->response($data);
//        
//    }
    
    
    /**
     * 
     *  API update Menu Dish
     * 
     *  Menthod: POST
     * 
     *  Response: JSONObject
     * 
     */
    public function update_menu_dish/*_post*/($action, /*$id, $id_restaurant,*/ $str_dish_list, $created_date, $updated_date) {
        
        //  Get param from client
//        $action = $this->post('action');
//        $id = $this->post('id');
//        $id_restaurant = $this->post('id_restaurant');
//        $str_dish_list = $this->post('dist_list');
//        $created_date = $this->post('created_date');
        
        (int)$is_insert = strcmp( strtolower($action), Common_enum::INSERT );
        (int)$is_edit = strcmp( strtolower($action), Common_enum::EDIT );
        
        $array_dish_list = explode(Common_enum::MARK_DISH, $str_dish_list);
        
        $dish_list = array();
        
        if(is_array($array_dish_list)){
            
            foreach ( $array_dish_list as $value ) {
                $detail_dish = explode(Common_enum::MARK_DETAIL_DISH, $value);
                
                if(is_array($detail_dish) && count($detail_dish) > 0){
                    $name = $detail_dish[0];
                    $desc= $detail_dish[1];
                    $price = (int)$detail_dish[2];
                    $signature_dish = trim($detail_dish[3]);

                    $dish = array(
                        Menu_dish_enum::NAME =>$name,
                        Menu_dish_enum::DESC =>$desc,
                        Menu_dish_enum::PRICE =>$price,
                        Menu_dish_enum::SIGNATURE_DISH =>$signature_dish,
                    );
                    $dish_list [] = $dish;
                }
            }
            $array_value = array(
                Menu_dish_enum::ID_RESTAURANT => '',
                Menu_dish_enum::DISH_LIST => $dish_list,
                Common_enum::CREATED_DATE => ($created_date==null) ? $this->common_model->getCurrentDate() : $created_date ,
                Common_enum::UPDATED_DATE => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date ,
            );
            $this->restaurant_model->updateMenuDish($action, /*$id*/null, $array_value);
            return ($array_value['_id']->{'$id'});
        }
        else{
            return null;
        }
//        $this->restaurant_model->updateMenuDish($action, /*$id*/null, $array_value);
//        $error = $this->restaurant_model->getError();
//        return ($array_value['_id']->{'$id'});
        
//        if($error == null){
//            $data =  array(
//                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
//                   'Error'      =>$error
//            );
//            $this->response($data);
//        }
//        else{
//            $data =  array(
//                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
//                   'Error'      =>$error
//            );
//            $this->response($data);
//        }
        
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Restaurant                                   //
    //                                                    //
    //----------------------------------------------------//

    /**
     * 
     *  API search Restaurant
     * 
     *  Menthod: GET
     * 
     *  @param int    $limit
     *  @param int    $page
     *  @param String $key
     * 
     *  Response: JSONObject
     * 
     */
    public function search_restaurant_get(){
        
        //  Get param from client
        $limit = $this->get("limit");
        $page = $this->get("page");

        //  Key search
        $key = Encode_utf8::toUTF8($this->get('key'));
        
        $array_key_word = explode(' ', $key);
//        var_dump($array_key_word);
        
        //  Query
        $where_name = array();
        foreach ($array_key_word as $value) {
            $where_name[] = array(Restaurant_enum::NAME => new MongoRegex('/'.$value.'/i'));
        }
        $where_email = array(Restaurant_enum::EMAIL => $key);
        $where_phone = (is_numeric($key))? array(Restaurant_enum::PHONE_NUMBER => $key) : array();
        
        $list_restaurant = $this->restaurant_model->searchRestaurant(array( '$or'=>$where_name ));
//        var_dump($array_key_word);
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if (is_array($list_restaurant)){
            
            foreach ($list_restaurant as $restaurant){
                //  Current date
                $current_date = $this->common_model->getCurrentDate();

                //  End date
                $end_date = $restaurant['end_date'];
                //  Get interval expired
                $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                //  Is delete
                $is_delete = $restaurant['is_delete'];

                if($interval_expired >=0 && $is_delete == 0){

                    $count ++;

                    if(($count) >= $position_start_get && ($count) <= $position_end_get){

                        //  Create JSONObject Restaurant
                        $jsonobject = array( 

                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            //Restaurant_enum::ID_USER                    => $restaurant['id_user'],
                            Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                            Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::AVATAR                     => $restaurant['avatar'],

                            Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                            Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                            Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                            Restaurant_enum::FAVOURITE_LIST    		=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                            Restaurant_enum::PRICE_PERSON_LIST      	=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
                            Restaurant_enum::CULINARY_STYLE_LIST    	=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
							
                            //  Number LIKE of Restaurant
                            Restaurant_enum::NUMBER_LIKE                 => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                            )),
                            //  Number SHARE of Restaurant
                            Restaurant_enum::NUMBER_SHARE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                        User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                        User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                        )),

                            Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                            Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                            Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                            Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),

                            Restaurant_enum::ADDRESS                    => $restaurant['address'],
                            Restaurant_enum::CITY                       => $restaurant['city'],
                            Restaurant_enum::DISTRICT                   => $restaurant['district'],
                            Restaurant_enum::EMAIL                      => $restaurant['email'],
                            Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                            Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'], 

                            Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                            Common_enum::CREATED_DATE         => $restaurant['created_date']
                                                                                                                                
                        );

                        $results[] = $jsonobject;
                    }
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
    }
    
    /**
     * 
     *  API search Restaurant by Name
     * 
     *  Menthod: GET
     * 
     *  @param int    $limit
     *  @param int    $page
     *  @param String $key
     * 
     *  Response: JSONObject
     * 
     */
    public function search_restaurant_by_name_get(){
        
        //  Get param from client
        $limit = $this->get("limit");
        $page = $this->get("page");

        //  Key search
        $key = Encode_utf8::toUTF8($this->get('key'));
        
        $array_key_word = explode(' ', $key);
//        var_dump($array_key_word);
        
        //  Query
        $where = array();
        foreach ($array_key_word as $value) {
            $where[] = array(Restaurant_enum::NAME => new MongoRegex('/'.$value.'/i'));
        }
        
        $list_restaurant = $this->restaurant_model->searchRestaurant(array( '$or'=>$where));
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if (is_array($list_restaurant)){
            
            foreach ($list_restaurant as $restaurant){
                //  Current date
                $current_date = $this->common_model->getCurrentDate();

                //  End date
                $end_date = $restaurant['end_date'];
                //  Get interval expired
                $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                //  Is delete
                $is_delete = $restaurant['is_delete'];

                if($interval_expired >=0 && $is_delete == 0){

                    $count ++;

                    if(($count) >= $position_start_get && ($count) <= $position_end_get){

                        //  Create JSONObject Restaurant
                        $jsonobject = array( 

                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            //Restaurant_enum::ID_USER                    => $restaurant['id_user'],
                            Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                            Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::AVATAR                     => $restaurant['avatar'],

                            Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                            Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                            Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                            Restaurant_enum::FAVOURITE_LIST    		=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                            Restaurant_enum::PRICE_PERSON_LIST      	=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
                            Restaurant_enum::CULINARY_STYLE_LIST    	=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
							
                            //  Number LIKE of Restaurant
                            Restaurant_enum::NUMBER_LIKE                 => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                            )),
                            //  Number SHARE of Restaurant
                            Restaurant_enum::NUMBER_SHARE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                        User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                        User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                        )),

                            Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                            Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                            Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                            Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),

                            Restaurant_enum::ADDRESS                    => $restaurant['address'],
                            Restaurant_enum::CITY                       => $restaurant['city'],
                            Restaurant_enum::DISTRICT                   => $restaurant['district'],
                            Restaurant_enum::EMAIL                      => $restaurant['email'],
                            Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                            Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'], 

                            Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                            Common_enum::CREATED_DATE         => $restaurant['created_date']
                                                                                                                                
                        );

                        $results[] = $jsonobject;
                    }
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
    }
    
    /**
     * 
     *  API search Restaurant by Id of Base colleciont
     * 
     *  Menthod: GET
     * 
     *  @param int    $limit
     *  @param int    $page
     *  @param String $key: id of FAVOURITE, PRICE_PERSON, MODE_USE, PAYMENT_TYPE, LANDSCAPE_LIST, OTHER_CRITERIA
     * 
     *  Response: JSONObject
     * 
     */
    public function search_restaurant_by_id_base_collection_get() {
        
        //  Get param from client
        $limit = $this->get("limit");
        $page  = $this->get("page");

        //  Field search
        $field = $this->get('field');
        //  Key search
        $key  = $this->get('key');
        
        //  Query
        $where = array($field => array('$in' => array($key)) );
        $list_restaurant = $this->restaurant_model->searchRestaurant($where);
        
        //  End
        $position_end_get   = ($page == 1) ? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1) ? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if (is_array($list_restaurant)){
            
            foreach ($list_restaurant as $restaurant){
                //  Current date
                $current_date = $this->common_model->getCurrentDate();

                //  End date
                $end_date = $restaurant['end_date'];
                //  Get interval expired
                $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                //  Is delete
                $is_delete = $restaurant['is_delete'];

                if($interval_expired >=0 && $is_delete == 0){

                    $count ++;

                    if(($count) >= $position_start_get && ($count) <= $position_end_get){

                        //  Create JSONObject Restaurant
                        $jsonobject = array( 

                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            //Restaurant_enum::ID_USER                    => $restaurant['id_user'],
                            Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                            Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::AVATAR                     => $restaurant['avatar'],

                            Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                            Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                            Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                            Restaurant_enum::FAVOURITE_LIST    		=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                            Restaurant_enum::PRICE_PERSON_LIST      	=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
                            Restaurant_enum::CULINARY_STYLE_LIST    	=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
							
                            //  Number LIKE of Restaurant
                            Restaurant_enum::NUMBER_LIKE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                            )),
                            //  Number SHARE of Restaurant
                            Restaurant_enum::NUMBER_SHARE               => $this->user_model->countUserLogByAction(array ( 
                                                                                                                        User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                        User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                        )),

                            Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                            Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                            Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                            Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),

                            Restaurant_enum::ADDRESS                    => $restaurant['address'],
                            Restaurant_enum::CITY                       => $restaurant['city'],
                            Restaurant_enum::DISTRICT                   => $restaurant['district'],
                            Restaurant_enum::EMAIL                      => $restaurant['email'],
                            Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                            Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'],

                            Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                            Common_enum::CREATED_DATE         => $restaurant['created_date']
                        );

                        $results[] = $jsonobject;
                    }
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
    }
    
    /**
     * 
     *  API search Restaurant by Coupon
     * 
     *  Menthod: GET
     * 
     *  @param int    $limit
     *  @param int    $page
     *  @param String $key
     * 
     *  Response: JSONObject
     * 
     */
    public function search_restaurant_by_coupon_get() {
        
        //  Get param from client
        $limit = $this->get("limit");
        $page = $this->get("page");

        //  Key search
        $key = Encode_utf8::toUTF8($this->get('key'));
        
        //  Query
        $where = array(Coupon_enum::DESC => new MongoRegex('/'.$key.'/i'));
        $list_coupon = $this->restaurant_model->searchCoupon($where);
        
        //  End
        $position_end_get   = ($page == 1) ? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1) ? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if (is_array($list_coupon)){
            
            foreach ($list_coupon as $coupon){
//                print_r($coupon);
                $id_restaurant = $coupon['id_restaurant'];
                $list_restaurant = $this->restaurant_model->getRestaurantById($id_restaurant);
                foreach ($list_restaurant as $restaurant) {
                    //  Current date
                    $current_date = $this->common_model->getCurrentDate();

                    //  End date
                    $end_date = $restaurant['end_date'];
                    //  Get interval expired
                    $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                    //  Is delete
                    $is_delete = $restaurant['is_delete'];

                    $due_date = $this->common_model->getInterval($current_date, $coupon['coupon_due_date']);
                    
                    if( ($interval_expired >=0) && ($is_delete == 0) && ($due_date >=0) ){
                        
                        $count ++;
                        if(($count) >= $position_start_get && ($count) <= $position_end_get){
                            //  Create JSONObject Restaurant
                            $jsonobject = array( 
                                Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                                Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                                Coupon_enum::VALUE_COUPON                   => $coupon['value_coupon'],
                                Coupon_enum::START_DATE                     => $coupon['coupon_start_date'],
                                Coupon_enum::DUE_DATE                       => $coupon['coupon_due_date'],
                                Coupon_enum::DESC                           => $coupon['coupon_desc'],
                                Restaurant_enum::NAME                       => $restaurant['name'],
                                Restaurant_enum::AVATAR                     => $restaurant['avatar'],

                                Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                                Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                                Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                                Restaurant_enum::FAVOURITE_LIST             => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                                Restaurant_enum::PRICE_PERSON_LIST          => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
                                Restaurant_enum::CULINARY_STYLE_LIST        => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),

                                //  Number LIKE of Restaurant
                                Restaurant_enum::NUMBER_LIKE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                                User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                                User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                                )),
                                //  Number SHARE of Restaurant
                                Restaurant_enum::NUMBER_SHARE               => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                            )),

                                Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                                Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                                Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                                Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),
                                Restaurant_enum::ADDRESS                    => $restaurant['address'],
                                Restaurant_enum::CITY                       => $restaurant['city'],
                                Restaurant_enum::DISTRICT                   => $restaurant['district'],
                                Restaurant_enum::EMAIL                      => $restaurant['email'],
                                Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                                Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'], 
                                Common_enum::UPDATED_DATE                   => $restaurant['updated_date'],
                                Common_enum::CREATED_DATE                   => $restaurant['created_date']
                            );

                            $results[] = $jsonobject;
                        }
                    }
                }
            }
//            //  Response
//            $data =  array(
//                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
//                   'Total'      =>  sizeof($results),
//                   'Results'    =>$results
//            );
//            $this->response($data);
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
    }
    
    /**
     * 
     *  API search Restaurant by Meal type
     * 
     *  Menthod: GET
     * 
     *  @param int    $limit
     *  @param int    $page
     *  @param String $key
     * 
     *  Response: JSONObject
     * 
     */
    public function search_restaurant_by_meal_get() {
        
        //  Get param from client
        $limit = $this->get("limit");
        $page = $this->get("page");

        //  Key search
        $key = $this->get('key');
        
        $key_ = iconv('UTF-8', 'UTF-8//IGNORE', $key);
        
        //  Query find collection Menu Dish by name
        $where = array(Menu_dish_enum::DISH_LIST.'.'.Menu_dish_enum::NAME => new MongoRegex('/'.$key_.'/i'));
        $list_menu_dish = $this->restaurant_model->searchMenuDish($where);
//        var_dump($list_menu_dish);
        //  List restaurant
        $list_restaurant = array();
        
        if (is_array($list_menu_dish) > 0){
            
            foreach ($list_menu_dish as $menu_dish){

                $restaurant = $this->restaurant_model->getRestaurantById($menu_dish['id_restaurant']);
                
                if($restaurant != null){
                    $list_restaurant[] = $restaurant;
                }
            }
        }
        
        //  End
        $position_end_get   = ($page == 1) ? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1) ? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if (sizeof($list_restaurant) > 0){
            
            //  Current date
            $current_date = $this->common_model->getCurrentDate();
            
            foreach ($list_restaurant as $array_restaurant){
                
                foreach ($array_restaurant as $restaurant){
                    //  End date
                    $end_date = $restaurant['end_date'];
                    //  Get interval expired
                    $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                    //  Is delete
                    $is_delete = $restaurant['is_delete'];

                    if($interval_expired >=0 && $is_delete == 0){

                        $count ++;

                        if(($count) >= $position_start_get && ($count) <= $position_end_get){

                            //  Create JSONObject Restaurant
                            $jsonobject = array( 

                                Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                                Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                                Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
				Restaurant_enum::AVATAR                     => $restaurant['avatar'],
                                Restaurant_enum::NAME                       => $restaurant['name'],
                                Restaurant_enum::ADDRESS                    => $restaurant['address'],
                                Restaurant_enum::CITY                       => $restaurant['city'],
                                Restaurant_enum::DISTRICT                   => $restaurant['district'],
                                Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                                Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'],
                                Restaurant_enum::LINK_TO                    => $restaurant['link_to'],
                                Restaurant_enum::PHONE_NUMBER               => $restaurant['phone_number'],
                                Restaurant_enum::WORKING_TIME               => $restaurant['working_time'],
								
                                Restaurant_enum::STATUS_ACTIVE              => $restaurant['status_active'],
								
                                Restaurant_enum::FAVOURITE_LIST             => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                                Restaurant_enum::PRICE_PERSON_LIST          => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
                                Restaurant_enum::CULINARY_STYLE_LIST        => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
								
                                Restaurant_enum::MODE_USE_LIST              => $restaurant['mode_use_list'],
                                Restaurant_enum::PAYMENT_TYPE_LIST          => $restaurant['payment_type_list'],
                                Restaurant_enum::LANDSCAPE_LIST             => $restaurant['landscape_list'],
                                Restaurant_enum::OTHER_CRITERIA_LIST        => $restaurant['other_criteria_list'],
                                Restaurant_enum::INTRODUCE                  => $restaurant['introduce'],
								
                                Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                                Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                                Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                                //  Number LIKE of Restaurant
                                Restaurant_enum::NUMBER_LIKE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                                User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                                User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                                )),
                                //  Number SHARE of Restaurant
                                Restaurant_enum::NUMBER_SHARE               => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                            )),

                                Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                                Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                                Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                                Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),

                                Restaurant_enum::START_DATE                 => $restaurant['start_date'],
                                Restaurant_enum::END_DATE                   => $restaurant['end_date'],

                                Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                                Common_enum::CREATED_DATE         => $restaurant['created_date']

                            );

                            $results[] = $jsonobject;
                        }
                    }
                }
                
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
    }
    
    /**
     * 
     *  API get all Restaurant
     * 
     *  Menthod: GET
     * 
     *  @param int    $limit
     *  @param int    $page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_all_restaurant_get(){
        
        //  Get param from client
        $limit = $this->get("limit");
        $page = $this->get("page");

        //  End
        $position_end_get   = ($page == 1) ? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1) ? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object restaurant
        $results = array();
        
        $list_restaurant = $this->restaurant_model->getAllRestaurant();
        
        //  Count object restaurant
        $count = 0;
        if (is_array($list_restaurant)){
            
//            foreach ($list_restaurant as $array_restaurant){
                
                foreach ($list_restaurant as $restaurant){

                    //  Is delete
                    $is_delete = $restaurant['is_delete'];

                    if($is_delete == 0){

                        $count ++;

                        if(($count) >= $position_start_get && ($count) <= $position_end_get){

                            //  Create JSONObject Restaurant
                            $jsonobject = array( 

                                Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                                Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                                Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
                                Restaurant_enum::NAME                       => $restaurant['name'],
                                Restaurant_enum::AVATAR                       => $restaurant['avatar'],
                                Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                                Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                                Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),
                                //  Number LIKE of Restaurant
                                Restaurant_enum::NUMBER_LIKE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                                User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                                User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                                )),
                                //  Number SHARE of Restaurant
                                Restaurant_enum::NUMBER_SHARE               => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                            )),
                                Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                                Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                                Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                                Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),
                                Restaurant_enum::ADDRESS                    => $restaurant['address'],
                                Restaurant_enum::CITY                       => $restaurant['city'],
                                Restaurant_enum::DISTRICT                   => $restaurant['district'],
                                Restaurant_enum::EMAIL                      => $restaurant['email'],
                                Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                                Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'],
                                Restaurant_enum::LINK_TO                    => $restaurant['link_to'],
                                Restaurant_enum::PHONE_NUMBER               => $restaurant['phone_number'],
                                Restaurant_enum::WORKING_TIME               => $restaurant['working_time'],
                                Restaurant_enum::STATUS_ACTIVE              => $restaurant['status_active'],
                                Restaurant_enum::FAVOURITE_LIST             => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                                Restaurant_enum::PRICE_PERSON_LIST          => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,     $restaurant['price_person_list']),
                                Restaurant_enum::CULINARY_STYLE_LIST        => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
                                Restaurant_enum::MODE_USE_LIST              => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::MODE_USE,         $restaurant['mode_use_list']),
                                Restaurant_enum::PAYMENT_TYPE_LIST          => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PAYMENT_TYPE,     $restaurant['payment_type_list']),
                                Restaurant_enum::LANDSCAPE_LIST             => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::LANDSCAPE,        $restaurant['landscape_list']),
                                Restaurant_enum::OTHER_CRITERIA_LIST        => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::OTHER_CRITERIA,   $restaurant['other_criteria_list']),
                                Restaurant_enum::INTRODUCE                  => $restaurant['introduce'],
                                Restaurant_enum::START_DATE                 => $restaurant['start_date'],
                                Restaurant_enum::END_DATE                   => $restaurant['end_date'],
                                Restaurant_enum::DESC                       => $restaurant['desc'],        
                                Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                                Common_enum::CREATED_DATE         => $restaurant['created_date']
                            );
                            $results[] = $jsonobject;
                        }
                    }
                }
//            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
    }
    
    /**
     * API Get Restaurant by Id
     * 
     * Menthod: GET
     * 
     * @param String $id
     * 
     * Response: JSONObject
     * 
     */
    public function get_detail_restaurant_get() {
        
        //  Get param from client
        $id = $this->get('id');
        
        //
        //  Edit field number_view: +1
        //
        $this->common_model->editSpecialField(Restaurant_enum::COLLECTION_RESTAURANT, $id, array('$inc' => array('number_view' => 1) ) );
        
        //  Get collection 
        $get_collection = $this->restaurant_model->getRestaurantById($id);
        
        $error = $this->restaurant_model->getError();

        if($error == null){
            //  Array object restaurant
            $results = array();
            if(is_array($get_collection)){
                foreach ($get_collection as $restaurant){

                    //  Current date
                    $current_date = $this->common_model->getCurrentDate();

                    //  End date
                    $end_date = $restaurant['end_date'];

                    //  Get interval expired
                    $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                    //  Is delete
                    $is_delete = $restaurant['is_delete'];

                    if($interval_expired >= 0 && $is_delete == 0){
                        
                        $array_coupon = $this->restaurant_model->getCouponById($restaurant['id_coupon']);
                        $coupon = $array_coupon[$restaurant['id_coupon']];
    //                    var_dump($coupon);
                        $due_date = $this->common_model->getInterval($current_date, $coupon['coupon_due_date']);
                        
                        //  Create JSONObject Restaurant
                        $jsonobject = array( 
                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                            Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::AVATAR                     => $restaurant['avatar'],
                            Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                            Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($id),
                            Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),
                            //  Number LIKE of Restaurant
                            Restaurant_enum::NUMBER_LIKE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                            )),
                            //  Number SHARE of Restaurant
                            Restaurant_enum::NUMBER_SHARE               => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                            )),
                            Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                            Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                            Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                            Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),
                            Restaurant_enum::ADDRESS                    => $restaurant['address'],
                            Restaurant_enum::CITY                       => $restaurant['city'],
                            Restaurant_enum::DISTRICT                   => $restaurant['district'],
                            Restaurant_enum::EMAIL                      => $restaurant['email'],
                            Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                            Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'],
                            Restaurant_enum::LINK_TO                    => $restaurant['link_to'],
                            Restaurant_enum::PHONE_NUMBER               => $restaurant['phone_number'],
                            Restaurant_enum::WORKING_TIME               => $restaurant['working_time'],
                            Restaurant_enum::STATUS_ACTIVE              => $restaurant['status_active'],
                            Restaurant_enum::FAVOURITE_LIST             => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                            Restaurant_enum::PRICE_PERSON_LIST          => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,     $restaurant['price_person_list']),
                            Restaurant_enum::CULINARY_STYLE_LIST        => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
                            Restaurant_enum::MODE_USE_LIST              => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::MODE_USE,         $restaurant['mode_use_list']),
                            Restaurant_enum::PAYMENT_TYPE_LIST          => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PAYMENT_TYPE,     $restaurant['payment_type_list']),
                            Restaurant_enum::LANDSCAPE_LIST             => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::LANDSCAPE,        $restaurant['landscape_list']),
                            Restaurant_enum::OTHER_CRITERIA_LIST        => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::OTHER_CRITERIA,   $restaurant['other_criteria_list']),
                            Restaurant_enum::INTRODUCE                  => $restaurant['introduce'],
                            Restaurant_enum::START_DATE                 => $restaurant['start_date'],
                            Restaurant_enum::END_DATE                   => $restaurant['end_date'],
                            Restaurant_enum::DESC                       => $restaurant['desc'],        
                            Coupon_enum::VALUE_COUPON                   => $coupon['value_coupon'],
                            Coupon_enum::START_DATE                     => $coupon['coupon_start_date'],
                            Coupon_enum::DUE_DATE                       => $coupon['coupon_due_date'],        
                            Coupon_enum::DESC                           => $coupon['coupon_desc'],
                            Common_enum::UPDATED_DATE                   => $restaurant['updated_date'],
                            Common_enum::CREATED_DATE                   => $restaurant['created_date']
                        );
                        $results[] = $jsonobject;
                    }
                }
            }
            
//            $array_value = array(
//                        User_log_enum::ID_USER              => $id_user,
//                        User_log_enum::ID_RESTAURANT        => $id_restaurant,        
//                        User_log_enum::ID_ASSESSMENT        => null,
//                        User_log_enum::ID_COMMENT           => null,
//                        User_log_enum::ID_POST              => null,
//                        User_log_enum::ACTION               => Common_enum::LIKE_RESTAURANT,
//                        User_log_enum::DESC                 => 'Like for a restaurant',
//                        Common_enum::UPDATED_DATE    => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date,
//                        Common_enum::CREATED_DATE    => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
//                );
//        
//            $this->user_model->updateUserLog(Common_enum::INSERT, Common_enum::LIKE, $array_value);
            
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>  $error
            );
            $this->response($data);
        }
        
    }
    
    /**
     * API Get Restaurant by Id
     * 
     * Menthod: GET
     * 
     * @param String $id
     * 
     * Response: JSONObject
     * 
     */
    public function get_restaurant_by_id_get(){
        
        //  Get param from client
        $id = $this->get('id');
        
        //  Get collection 
        $get_collection = $this->restaurant_model->getRestaurantById($id);
        
        $error = $this->restaurant_model->getError();

        if($error == null){
            //  Array object restaurant
            $results = array();
            if(is_array($get_collection)){
                foreach ($get_collection as $restaurant){

                    $array_menu_dish = $this->common_model->getCollectionById(Menu_dish_enum::COLLECTION_MENU_DISH, $restaurant['id_menu_dish']);
                    $menu_dish = $array_menu_dish[$restaurant['id_menu_dish']];
                    
                    $menu_dish_object = array(
                            Menu_dish_enum::ID                => $menu_dish['_id']->{'$id'},
                            Menu_dish_enum::DISH_LIST         => $menu_dish['dish_list'],        
                            Common_enum::CREATED_DATE         => $menu_dish['created_date']
                        );

                    //  Create JSONObject Restaurant
                    $jsonobject = array( 
                        Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                        Restaurant_enum::ID_MENU_DISH               => $menu_dish_object,
                        Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
                        Restaurant_enum::NAME                       => $restaurant['name'],
                        Restaurant_enum::AVATAR                     => $restaurant['avatar'],
                        Restaurant_enum::ADDRESS                    => $restaurant['address'],
                        Restaurant_enum::CITY                       => $restaurant['city'],
                        Restaurant_enum::DISTRICT                   => $restaurant['district'],
                        Restaurant_enum::EMAIL                      => $restaurant['email'],
                        Restaurant_enum::APPROVAL_SHOW_CAROSUEL     => $restaurant['approval_show_carousel'],
                        Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                        Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'],
                        Restaurant_enum::LINK_TO                    => $restaurant['link_to'],
                        Restaurant_enum::PHONE_NUMBER               => $restaurant['phone_number'],
                        Restaurant_enum::WORKING_TIME               => $restaurant['working_time'],
                        Restaurant_enum::STATUS_ACTIVE              => $restaurant['status_active'],
                        Restaurant_enum::FAVOURITE_LIST             => $restaurant['favourite_list'],
                        Restaurant_enum::PRICE_PERSON_LIST          => $restaurant['price_person_list'],
                        Restaurant_enum::CULINARY_STYLE_LIST        => $restaurant['culinary_style_list'],
                        Restaurant_enum::MODE_USE_LIST              => $restaurant['mode_use_list'],
                        Restaurant_enum::PAYMENT_TYPE_LIST          => $restaurant['payment_type_list'],
                        Restaurant_enum::LANDSCAPE_LIST             => $restaurant['landscape_list'],
                        Restaurant_enum::OTHER_CRITERIA_LIST        => $restaurant['other_criteria_list'],
                        Restaurant_enum::INTRODUCE                  => $restaurant['introduce'],
                        Restaurant_enum::START_DATE                 => $restaurant['start_date'],
                        Restaurant_enum::END_DATE                   => $restaurant['end_date'],
                        Restaurant_enum::DESC                       => $restaurant['desc'],        
                        Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],   
                        Restaurant_enum::FOLDER_NAME                => $restaurant['folder_name'],
                        Common_enum::UPDATED_DATE                   => $restaurant['updated_date'],
                        Common_enum::CREATED_DATE                   => $restaurant['created_date']
                    );
                    $results[] = $jsonobject;
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>  $error
            );
            $this->response($data);
        }
        
    }
    
    /**
     * 
     *  API get All Restaurant approval show carousel
     * 
     *  Menthod: GET
     * 
     *  @param int $limit
     *  @param int $page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_all_restaurant_approval_show_carousel_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        $list_order_by_restaurant = $this->restaurant_model->orderByRestaurant( -1 );
        $error = $this->restaurant_model->getError();
        if($error == null){

            //  Array object restaurant
            $results = array();

            //  Count object restaurant
            $count = 0;
            if(is_array($list_order_by_restaurant)){
                foreach ($list_order_by_restaurant as $restaurant){
                    //  Current date
                    $current_date = $this->common_model->getCurrentDate();

                    //  End date
                    $end_date = $restaurant['end_date'];

                    //  Get interval expired
                    $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                    //  Is delete
                    $is_delete = $restaurant['is_delete'];
                    $approval_show_carousel = $restaurant['approval_show_carousel'];
                    if( ($interval_expired >= 0 && $is_delete == 0) && $approval_show_carousel == 1){
                        $count ++;
                        if(($count) >= $position_start_get && ($count) <= $position_end_get){
                            //  Create JSONObject Restaurant
                            $jsonobject = array( 
                                Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                                Restaurant_enum::NAME                       => $restaurant['name'],
                                Restaurant_enum::ADDRESS                    => $restaurant['address'].', '.$restaurant['district'].', '.$restaurant['city'],
                                Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'],
                                Restaurant_enum::LINK_TO                    => $restaurant['link_to'],
                                Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                                Common_enum::CREATED_DATE         => $restaurant['created_date']
                            );
                            $results[] = $jsonobject;
                        }
                    }
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );


            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error,
            );
            $this->response($data);
        }
        
    }
    
    /**
     * 
     *  API get Order By DESC Restaurant
     * 
     *  Menthod: GET
     * 
     *  @param int $limit
     *  @param int $page
     *  @param int $order_by
     * 
     *  Response: JSONObject
     * 
     */
    public function get_order_by_restaurant_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
        
        $order_by = ($this->get("order_by") == null)? 1 : (int)$this->get("order_by");
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        $list_order_by_restaurant = $this->restaurant_model->orderByRestaurant( $order_by );
        $error = $this->restaurant_model->getError();
        if($error == null){
            //  Array object restaurant
            $results = array();
            //  Count object restaurant
            $count = 0;
            if(is_array($list_order_by_restaurant)){
                foreach ($list_order_by_restaurant as $restaurant){
                    //  Current date
                    $current_date = $this->common_model->getCurrentDate();

                    //  End date
                    $end_date = $restaurant['end_date'];

                    //  Get interval expired
                    $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                    //  Is delete
                    $is_delete = $restaurant['is_delete'];

                    if($interval_expired >= 0 && $is_delete == 0){

                        $count ++;

                        if(($count) >= $position_start_get && ($count) <= $position_end_get){

                            //  Create JSONObject Restaurant
                            $jsonobject = array( 

                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::DESC                       => $restaurant['desc'],
                            Restaurant_enum::AVATAR                     => $restaurant['avatar'],
                            Restaurant_enum::ADDRESS                    => $restaurant['address'].', '.$restaurant['district'].', '.$restaurant['city'],
                            Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                            Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                            //  Number LIKE of Restaurant
                            Restaurant_enum::NUMBER_LIKE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                            )),
                            //  Number SHARE of Restaurant
                            Restaurant_enum::NUMBER_SHARE               => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                            )),
                            Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                            Common_enum::CREATED_DATE         => $restaurant['created_date']

                        );

                        $results[] = $jsonobject;

                        $this->restaurant_model->setRateService(0);
                        $this->restaurant_model->setRateLandscape(0);
                        $this->restaurant_model->setRateTaste(0);
                        $this->restaurant_model->setRatePrice(0);

                        }

                    }


                }
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );


            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error,
            );
            $this->response($data);
        }
        
    }
    
    /**
     * 
     *  API get Newest Restaurant
     * 
     *  Menthod: GET
     * 
     *  @param int $limit
     *  @param int $page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_newest_restaurant_list_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get collection restaurant
        $collection_name = Restaurant_enum::COLLECTION_RESTAURANT;
        $list_restaurant = $this->common_model->getCollection($collection_name);
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if(is_array($list_restaurant)){
            foreach ($list_restaurant as $restaurant){
                //  Get created date
                $created_date = $restaurant['created_date'];

                //  Current date
                $current_date = $this->common_model->getCurrentDate();

                //  End date
                $end_date = $restaurant['end_date'];

                //  Get interval expired
                $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                //  Is delete
                $is_delete = $restaurant['is_delete'];

                //  Get interval
                $interval = $this->common_model->getInterval($created_date, $current_date);
    //            var_dump($created_date);
    //            var_dump($current_date);
    //            var_dump($interval_expired);
                if( (($interval <= Common_enum::INTERVAL_NEWST_RESTAURANT) && $interval >=0) && ($interval_expired >=0 && $is_delete == 0) ){
                    $count ++ ;

                    if(($count) >= $position_start_get && ($count) <= $position_end_get){
                        //  Create JSONObject Restaurant
                        $jsonobject = array( 

                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::DESC                       => $restaurant['desc'],
                            Restaurant_enum::AVATAR                     => $restaurant['avatar'],
                            Restaurant_enum::ADDRESS                    => $restaurant['address'].', '.$restaurant['district'].', '.$restaurant['city'],
                            Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                            Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                            //  Number LIKE of Restaurant
                            Restaurant_enum::NUMBER_LIKE                 => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                            )),
                            //  Number SHARE of Restaurant
                            Restaurant_enum::NUMBER_SHARE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                            )),

                            Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                            Common_enum::CREATED_DATE         => $restaurant['created_date']
                        );

                        $results[] = $jsonobject;

                        $this->restaurant_model->setRateService(0);
                        $this->restaurant_model->setRateLandscape(0);
                        $this->restaurant_model->setRateTaste(0);
                        $this->restaurant_model->setRatePrice(0);

                    }
                }

            }
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
    }
    
    /**
     * 
     *  API get Restaurant Coupon
     * 
     *  Menthod: GET
     * 
     *  @param int $limit
     *  @param int $page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_restaurant_coupon_list_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get collection restaurant
        $collection_name = Restaurant_enum::COLLECTION_RESTAURANT;
        $list_restaurant = $this->common_model->getCollection($collection_name);
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if(is_array($list_restaurant)){
            foreach ($list_restaurant as $restaurant){
                //  Get created date
                $created_date = $restaurant['created_date'];
//                print_r($restaurant);
                
                //  Current date
                $current_date = $this->common_model->getCurrentDate();
                //  End date
                $end_date = $restaurant['end_date'];
                
                //  Get interval expired
                $interval_expired = $this->common_model->getInterval($current_date, $end_date);
                //  Is delete
                $is_delete = $restaurant['is_delete'];

                //  Get interval
                $interval = $this->common_model->getInterval($created_date, $current_date);
                if( ($interval_expired >=0 && $is_delete == 0) && ($restaurant['id_coupon'] != null) ){
                    $array_coupon = $this->restaurant_model->getCouponById($restaurant['id_coupon']);
                    if($array_coupon != null){
                      $coupon = $array_coupon[$restaurant['id_coupon']];
                      $due_date = $this->common_model->getInterval($current_date, $coupon['coupon_due_date']);
                      if($due_date >= 0){

                          $count ++ ;
                          if(($count) >= $position_start_get && ($count) <= $position_end_get){
                              //  Create JSONObject Restaurant
                              $jsonobject = array( 
                                  Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                                  Restaurant_enum::NAME                       => $restaurant['name'],
                                  Restaurant_enum::DESC                       => $restaurant['desc'],
                                  Restaurant_enum::AVATAR                     => $restaurant['avatar'],
                                  Restaurant_enum::ADDRESS                    => $restaurant['address'].', '.$restaurant['district'].', '.$restaurant['city'],
                                  Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                                  Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),
                                  //  Number LIKE of Restaurant
                                  Restaurant_enum::NUMBER_LIKE                 => $this->user_model->countUserLogByAction(array ( 
                                                                                                                                  User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                                  User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                                  )),
                                  //  Number SHARE of Restaurant
                                  Restaurant_enum::NUMBER_SHARE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                                  User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                                  User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                                  )),

                                  Coupon_enum::VALUE_COUPON => $coupon['value_coupon'],
                                  Coupon_enum::START_DATE => $coupon['coupon_start_date'],
                                  Coupon_enum::DUE_DATE => $coupon['coupon_due_date'],        
                                  Coupon_enum::DESC => $coupon['coupon_desc'],



                                  Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                                  Common_enum::CREATED_DATE         => $restaurant['created_date']
                              );

                              $results[] = $jsonobject;

                              $this->restaurant_model->setRateService(0);
                              $this->restaurant_model->setRateLandscape(0);
                              $this->restaurant_model->setRateTaste(0);
                              $this->restaurant_model->setRatePrice(0);
                          }
                      }
                    }
                    
                }
            }
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
    }
    
    /**
     * 
     *  API get Order Restaurant
     * 
     *  Menthod: GET
     * 
     *  @param int $limit
     *  @param int $page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_orther_restaurant_list_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
                
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get collection restaurant
        $collection_name = Restaurant_enum::COLLECTION_RESTAURANT;
        $list_restaurant = $this->common_model->getCollection($collection_name);
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        
        //  Count result
        $count_result = 0;
        if(is_array($list_restaurant)){
            foreach ($list_restaurant as $restaurant){
                //  Get created date
                $created_date = $restaurant['created_date'];

                //  Current date
                $current_date = $this->common_model->getCurrentDate();

                //  End date
                $end_date = $restaurant['end_date'];

                //  Get interval expired
                $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                //  Is delete
                $is_delete = $restaurant['is_delete'];

                //  Get interval
                $interval = $this->common_model->getInterval($created_date, $current_date);

                if( ($interval > Common_enum::INTERVAL_NEWST_RESTAURANT) && ($interval_expired >=0 && $is_delete == 0) ){
                    $count++;

                    if(($count) >= $position_start_get && ($count) <= $position_end_get){

                        $count_result ++ ;

                        //  Create JSONObject Restaurant
                        $jsonobject = array( 

                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::DESC                       => $restaurant['desc'],
                            Restaurant_enum::AVATAR                     => $restaurant['avatar'],
                            Restaurant_enum::ADDRESS                    => $restaurant['address'].', '.$restaurant['district'].', '.$restaurant['city'],
                            Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                            Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                            //  Number LIKE of Restaurant
                            Restaurant_enum::NUMBER_LIKE                 => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                            )),
                            //  Number SHARE of Restaurant
                            Restaurant_enum::NUMBER_SHARE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                            )),
                            Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                            Common_enum::CREATED_DATE         => $restaurant['created_date']

                        );

                        $results[] = $jsonobject;

                        $this->restaurant_model->setRateService(0);
                        $this->restaurant_model->setRateLandscape(0);
                        $this->restaurant_model->setRateTaste(0);
                        $this->restaurant_model->setRatePrice(0);

                    }
                }
            }
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>sizeof($results),
               'Results'    =>$results
        );

        $this->response($data);
    }
    
    
    /**
     * 
     * API update Restaurant
     * 
     * Menthod: POST
     * 
     * $action: insert | edit | delete
        
     * @param String $id
     * @param String $id_user
     * @param String $id_menu_dish
     * @param String $id_coupon
     * @param String $name
     * @param int    $rate_point
     * @param String $address
     * @param String $city
     * @param String $district
     * @param String $image_introduce_link
     * @param String $image_carousel_link
     * @param String $link_to
     * @param String $phone_number
     * @param String $working_time
     * @param String $status_active
     * @param String $favourite_list
     * @param String $price_person_list
     * @param String $culinary_style_list
     * @param String $mode_use_list
     * @param String $payment_type_list
     * @param String $landscape_list
     * @param String $other_criteria_list
     * @param String $introduce
     * @param int    $number_view
     * @param String $start_date
     * @param String $end_date
     * @param String $created_date
     * @param int    $is_delete
     * 
     *  Response: JSONObject
     * 
     */
   public function update_restaurant_post(){
        
        //  Get param from client
        $action                  = $this->post('action'); 
        $id                      = $this->post('id'); 
        $id_menu_dish            = $this->post('id_menu_dish');
        $id_coupon               = $this->post('id_coupon');
        $name                    = $this->post('name');
        $folder_name             = $this->post('folder_name');
        $email                   = $this->post('email');
        $desc                    = $this->post('desc');
        $approval_show_carousel  = $this->post('approval_show_carousel');
        $address                 = $this->post('address');
        $city                    = $this->post('city');
        $district                = $this->post('district');
        $link_to                 = $this->post('link_to');
        $phone_number            = $this->post('phone_number');
        $working_time            = $this->post('working_time');
        $status_active           = $this->post('status_active');
        $str_dish_list           = $this->post('dish_list');
        $favourite_list          = $this->post('favourite_list');
        $price_person_list       = $this->post('price_person_list');
        $culinary_style_list     = $this->post('culinary_style_list');
        $mode_use_list           = $this->post('mode_use_list');
        $payment_type_list       = $this->post('payment_type_list');
        $landscape_list          = $this->post('landscape_list');
        $other_criteria_list     = $this->post('other_criteria_list');
        $introduce               = $this->post('introduce');
        $number_view             = $this->post('number_view');
        $start_date              = $this->post('start_date');
        $end_date                = $this->post('end_date');
        $created_date            = $this->post('created_date');
        $updated_date            = $this->post('updated_date');
        $is_delete               = $this->post('is_delete');
        $str_image_post          = $this->post('array_image');                   //  image.jpg,image2.png,...
        
        $file_avatar='';
        $file_carousel='';
        $file_introduce = array();
        $base_path_restaurant = Common_enum::ROOT.Common_enum::DIR_RESTAURANT.$folder_name.'/images/';
        $file_temp = Common_enum::ROOT.Common_enum::PATH_TEMP;
        
        $path_avatar    = $base_path_restaurant.'avatar/';
        $path_carousel  = $base_path_restaurant.'carousel/';
        $path_introduce = $base_path_restaurant.'introduce/';
        
        (int)$is_insert = strcmp( strtolower($action), Common_enum::INSERT );
        (int)$is_edit = strcmp( strtolower($action), Common_enum::EDIT );
        (int)$delete = strcmp( strtolower($action), Common_enum::DELETE );
        
        //  Create directory $path
        $this->common_model->createDirectory($path_avatar, Common_enum::WINDOWN);
        $this->common_model->createDirectory($path_carousel, Common_enum::WINDOWN);
        $this->common_model->createDirectory($path_introduce, Common_enum::WINDOWN);
        
        if($is_insert == 0){
            
            $array_image_post = explode(Common_enum::MARK, $str_image_post); //  ['image.jpg', 'image2.png' ,...]
            
            foreach ($array_image_post as $i => $value) {
                $temp = $file_temp.$value;
                
                if (file_exists($temp)) {
                  
                    //  Move file from directory post
                    if($i == 0){
                      $move_file_avatar = $this->common_model->moveFileToDirectory($temp, $path_avatar.$value);
                      if(!$move_file_avatar){
                          $this->common_model->setError('Move file avatar '.$move_file_avatar);
                      }else{
                          $file_avatar = $folder_name.'/images/avatar/'.$value;
                      }
//                      var_dump($i.' = '.$value);
                    }
                    else if($i == 1){
                      
                      $move_file_carousel = $this->common_model->moveFileToDirectory($temp, $path_carousel.$value);
                      if(!$move_file_carousel){
                          $this->common_model->setError('Move file carousel '.$move_file_carousel);
                      }
                      else{
                          $file_carousel = $folder_name.'/images/carousel/'.$value;
                      }
//                      var_dump($i.' = '.$value);
                    }
                    else{
                      
                      $move_file_introduce = $this->common_model->moveFileToDirectory($temp, $path_introduce.$value);

                      if(!$move_file_introduce){
                          $this->common_model->setError('Move file introduce '.$move_file_introduce);
                      }else{
                          $introduce = str_replace(str_replace(Common_enum::ROOT, Common_enum::DOMAIN_NAME ,$temp), 'folder_image_introduce_detail_page/'.$folder_name.'/images/introduce/'.$array_image_post[$i], $introduce);
//
                          $file_introduce []= $folder_name.'/images/introduce/'.$value;
//                          var_dump($i.' = '.$value);
                      }
                    }
                    
                    
                }
            }
        }
        else if($is_edit == 0){
            $this->common_model->removeDoc(Menu_dish_enum::COLLECTION_MENU_DISH, $id_menu_dish);
            
            $array_image_post = explode(Common_enum::MARK_, $str_image_post); //  [ {'new_avatar.jpg,old_avatar.jpg'}, {'new_carousel.jpg,old_carousel.jpg'}, {'deleted_introduce_1.jpg,deleted_introduce_2.jpg,...'}, {'introduce_1.jpg,introduce_2.jpg,...'}]
            //  string image
            $str_image_avatar = $array_image_post[0];   //{'new_avatar.jpg,old_avatar.jpg'}
            $str_image_carousel = $array_image_post[1]; //{'new_carousel.jpg,old_carousel.jpg'}
            $str_image_deleted = $array_image_post[2];  //{'deleted_introduce_1.jpg,deleted_introduce_2.jpg,...'}
            $str_image_introduce = $array_image_post[3]; //{'introduce_1.jpg,introduce_2.jpg,...'}
            
            //  array imaga avatar
            $array_image_avatar = explode(Common_enum::MARK, $str_image_avatar);//  [new_avatar.jpg, old_avatar.jpg]
            if(strcmp($array_image_avatar[0], $array_image_avatar[1]) != 0 ){
              if(file_exists($file_temp.$array_image_avatar[0])){                 //  check new avatar

//                var_dump('Not match Avatar');

                  $move_file_avatar = $this->common_model->moveFileToDirectory($file_temp.$array_image_avatar[0], $path_avatar.$array_image_avatar[0]);
                  if(!$move_file_avatar){
                      $this->common_model->setError('Move file avatar '.$move_file_avatar);
                  }
                  else{
//                      $file_avatar = $folder_name.'/images/avatar/'.$array_image_avatar[0];
                      if(file_exists($path_avatar.$array_image_avatar[1])){       //  check old avatar
                          unlink($path_avatar.$array_image_avatar[1]);
                      }
                  }
              }
            }
            
            else{
              var_dump('match Avatar');
            }
            $file_avatar = $folder_name.'/images/avatar/'.$array_image_avatar[0];
            
            //  array image carousel
            $array_image_carousel = explode(Common_enum::MARK, $str_image_carousel);//  [new_carousel.jpg, old_carousel.jpg]
            if(strcmp($array_image_carousel[0], $array_image_carousel[1]) != 0 ){
              
//              var_dump('Not match Carousel');
              
              if(file_exists($file_temp.$array_image_carousel[0])){                 //  check new carousel
                  $move_file_carousel = $this->common_model->moveFileToDirectory($file_temp.$array_image_carousel[0], $path_carousel.$array_image_carousel[0]);
                  if(!$move_file_carousel){
                      $this->common_model->setError('Move file carousel '.$move_file_carousel);
                  }
                  else{
//                      $file_carousel = $folder_name.'/images/carousel/'.$array_image_carousel[0];
                      if(file_exists($path_carousel.$array_image_carousel[1])){       //  check old carousel
                          unlink($path_carousel.$array_image_carousel[1]);
                      }
                  }
              }
            }
            else{
              var_dump('match Carousel');
            }
            $file_carousel = $folder_name.'/images/carousel/'.$array_image_carousel[0];
            
            //  array image deleted
            if(strcmp(trim($str_image_deleted), 'null') != 0){
                $array_image_delete = explode(Common_enum::MARK, $str_image_deleted);//  [deleted_introduce_1.jpg,deleted_introduce_2.jpg,...]
//                var_dump($array_image_delete);
                 foreach ($array_image_delete as $value) {
                    if(file_exists($path_introduce.$value)){       //  check old introduce image
                            unlink($path_introduce.$value);
                        }
                }
            }
            else{
              var_dump('$str_image_deleted NULL');
            }
            
            //  array image introlduce
            if(strcmp(trim($str_image_introduce), 'null') != 0){
              $array_image_introduce = explode(Common_enum::MARK, $str_image_introduce);//  [introduce_1.jpg,introduce_2.jpg,...]
              foreach ($array_image_introduce as $value) {
                  if(file_exists($file_temp.$value)){       //  check new introduce image
                          $move_file_carousel = $this->common_model->moveFileToDirectory($file_temp.$value, $path_introduce.$value);
      //                if(!$move_file_carousel){
      //                    $this->common_model->setError('Move file carousel '.$move_file_carousel);
      //                }
//          var_dump(str_replace(Common_enum::ROOT, Common_enum::DOMAIN_NAME ,$file_temp.$value));
                          $introduce = str_replace(str_replace(Common_enum::ROOT, Common_enum::DOMAIN_NAME ,$file_temp.$value), 'folder_image_introduce_detail_page/'.$folder_name.'/images/introduce/'.$value, $introduce);
//                          var_dump($introduce);
                  }
                  else{
                    $introduce = str_replace(Common_enum::DOMAIN_NAME.Common_enum::URL_RESTAURANT_PROFILE, 'folder_image_introduce_detail_page/', $introduce);
                  }
//          var_dump($introduce);
                  $file_introduce []= $folder_name.'/images/introduce/'.$value;
              }
            }
        }
        //  Update menu_dish
        $id_menu_dish_new = $this->update_menu_dish(Common_enum::INSERT, /*$id_menu_dish, $id,*/ $str_dish_list, $created_date, $updated_date);
        $array_value = ($delete != 0)? array( 
            Restaurant_enum::ID_MENU_DISH               => ($id_menu_dish_new == null) ? '' : $id_menu_dish_new,
            Restaurant_enum::ID_COUPON                  => $id_coupon,
            Restaurant_enum::NAME                       => $name,
            Restaurant_enum::FOLDER_NAME                => $folder_name,
            Restaurant_enum::EMAIL                      => $email,
            Restaurant_enum::AVATAR                     => $file_avatar,
            Restaurant_enum::APPROVAL_SHOW_CAROSUEL     => ($approval_show_carousel != null) ? (int)$approval_show_carousel : 0,
            Restaurant_enum::DESC                       => $desc,
            Restaurant_enum::ADDRESS                    => $address,
            Restaurant_enum::CITY                       => $city,
            Restaurant_enum::DISTRICT                   => $district,
            Restaurant_enum::IMAGE_CAROUSEL_LINK        => $file_carousel,
            Restaurant_enum::IMAGE_INTRODUCE_LINK       => $file_introduce,
            Restaurant_enum::LINK_TO                    => $link_to,
            Restaurant_enum::PHONE_NUMBER               => $phone_number,
            Restaurant_enum::WORKING_TIME               => $working_time,
            Restaurant_enum::STATUS_ACTIVE              => $status_active,
            Restaurant_enum::FAVOURITE_LIST             => ($favourite_list != null ) ? explode(Common_enum::MARK, $favourite_list): array(),
            Restaurant_enum::PRICE_PERSON_LIST          => ($price_person_list != null ) ? explode(Common_enum::MARK, $price_person_list): array(),
            Restaurant_enum::CULINARY_STYLE_LIST        => ($culinary_style_list != null ) ? explode(Common_enum::MARK, $culinary_style_list): array(),
            Restaurant_enum::MODE_USE_LIST              => ($mode_use_list != null ) ? explode(Common_enum::MARK, $mode_use_list): array(),
            Restaurant_enum::PAYMENT_TYPE_LIST          => ($payment_type_list != null ) ? explode(Common_enum::MARK, $payment_type_list): array(),
            Restaurant_enum::LANDSCAPE_LIST             => ($landscape_list != null ) ? explode(Common_enum::MARK, $landscape_list): array(),
            Restaurant_enum::OTHER_CRITERIA_LIST        => ($other_criteria_list != null ) ? explode(Common_enum::MARK, $other_criteria_list): array(),
            Restaurant_enum::INTRODUCE                  => $introduce,
            Restaurant_enum::NUMBER_VIEW                => 0,
            Restaurant_enum::START_DATE                 => $start_date,
            Restaurant_enum::END_DATE                   => $end_date,
            Common_enum::UPDATED_DATE       => ($updated_date == null ) ? $this->common_model->getCurrentDate(): $updated_date,
            Common_enum::CREATED_DATE       => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date,
            Restaurant_enum::IS_DELETE                  => ($is_delete == null ) ? Restaurant_enum::DEFAULT_IS_DELETE : (int)$is_delete
        ) : 
        array(Restaurant_enum::IS_DELETE => 1);
        if($is_edit==0){
            unset($array_value['number_view']);
        }
        $this->restaurant_model->updateRestaurant($action, $id, $array_value);
        
        $this->restaurant_model->updateMenuDish(Common_enum::EDIT, $id_menu_dish_new, array(Menu_dish_enum::ID_RESTAURANT => $array_value['_id']->{'$id'}) );
        
        $error = $this->restaurant_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }

    //------------------------------------------------------
    //                                                     /
    //  APIs Coupon                                        /
    //                                                     /
    //------------------------------------------------------
    
    /**
     * 
     * API upadate Coupon
     * 
     * Menthod: POST
     * 
     * @param String $id
     * @param String $id_restaurant
     * @param int $coupon_value
     * @param String $start_date
     * @param String $due_date
     * @param String $desc
     * 
     *  Response: JSONObject
     * 
     */
    public function update_coupon_post(){
        
        //  Get param from client
        $action         = $this->post('action');
        $id             = $this->post('id');
        $id_restaurant  = $this->post('id_restaurant');
        $value_coupon   = $this->post('value_coupon');
        $start_date     = $this->post('start_date');
        $due_date       = $this->post('due_date');
        $desc           = $this->post('desc');
        
        $created_date   = $this->post('created_date');
        $updated_date = $this->post('updated_date');
        
        $action_delete = strcmp( strtolower($action), Common_enum::DELETE );
        
        $array_value = ($action_delete != null)? array(
            Coupon_enum::ID_RESTAURANT => $id_restaurant,
            Coupon_enum::VALUE_COUPON => (int)$value_coupon,
            Coupon_enum::START_DATE => $start_date,
            Coupon_enum::DUE_DATE => $due_date,
            Coupon_enum::DESC => $desc,
            Common_enum::UPDATED_DATE       => ($updated_date == null ) ? $this->common_model->getCurrentDate(): $updated_date,
            Common_enum::CREATED_DATE       => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
        )
         : array();
        
        
        $this->restaurant_model->updateCoupon($action, $id, $array_value);
        $error = $this->restaurant_model->getError();
        if($error == null){
            
            $this->common_model->editSpecialField(Restaurant_enum::COLLECTION_RESTAURANT, 
                                                  $id_restaurant, array('$set' => array(Restaurant_enum::ID_COUPON=>$array_value['_id']->{'$id'} )));
            
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        
    }
    
    public function get_coupon_of_restaurant_get() {
        //  Get param from client
        $id_restaurant = $this->get('id_restaurant');
        //  Get collection 
        $array_coupon = $this->restaurant_model->getCouponByRestaurant($id_restaurant);
        
        $results = array();
        if($array_coupon == null){
            //  
        }
        else{
            foreach ($array_coupon as $value) {
//                var_dump($value);
                $jsonobject = array(
                    Coupon_enum::ID => $value[Common_enum::_ID]->{'$id'},
                    Coupon_enum::ID_RESTAURANT => $value[Coupon_enum::ID_RESTAURANT],
                    Coupon_enum::VALUE_COUPON => $value[Coupon_enum::VALUE_COUPON],
                    Coupon_enum::START_DATE => $value[Coupon_enum::START_DATE],
                    Coupon_enum::DUE_DATE => $value[Coupon_enum::DUE_DATE],
                    Coupon_enum::DESC => $value[Coupon_enum::DESC],
                    Common_enum::UPDATED_DATE => $value[Common_enum::UPDATED_DATE],
                    Common_enum::CREATED_DATE => $value[Common_enum::CREATED_DATE]
                );
                $results[] = $jsonobject;
            }
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
    }
    
    //------------------------------------------------------
    //                                                     /
    //  APIs Post                                          /
    //                                                     /
    //------------------------------------------------------
    
    /**
     * 
     *  API search Post
     * 
     *  Menthod: GET
     * 
     *  @param int $limit
     *  @param int $page
     *  @param String $key
     * 
     *  Response: JSONObject
     * 
     */    
    public function search_post_get(){
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
        
        //  Key search
        $key = $this->get('key');
        $key = Encode_utf8::toUTF8($this->get('key'));
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        //  Query
        $where = array(Post_enum::TITLE => new MongoRegex('/'.$key.'/i'));
        $list_post = $this->restaurant_model->searchPost($where);
        
        //  Array object post
        $results = array();
        
        //  Count object post
        $count = 0;
        
        //  Count resulte
        $count_resulte = 0;
        if(is_array($list_post)){
            foreach ($list_post as $post){
                $count++;
                if(($count) >= $position_start_get && ($count) <= $position_end_get){
                    //  Create JSONObject Post
                    $jsonobject = array( 

                               Post_enum::ID                     => $post['_id']->{'$id'},
                               Post_enum::ID_USER                => $post['id_user'],
                               Post_enum::TITLE                  => $post['title'],
                               Post_enum::AVATAR                 => $post['avatar'],
                               Post_enum::ADDRESS                => $post['address'],
                               Post_enum::FAVOURITE_TYPE_LIST    => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $post['favourite_type_list']),
                               Post_enum::PRICE_PERSON_LIST      => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,     $post['price_person_list']),
                               Post_enum::CULINARY_STYLE_LIST    => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $post['culinary_style_list']),
                               Post_enum::CONTENT                => $post['content'],
                               Post_enum::NUMBER_ASSESSMENT     => $this->restaurant_model->countAssessmentForPost($post['_id']->{'$id'}),
                               Post_enum::RATE_POINT            => $this->restaurant_model->getRatePoint(),
                               Post_enum::NUMBER_VIEW            => $post['number_view'],
                               Common_enum::UPDATED_DATE         => $post['updated_date'],
                               Common_enum::CREATED_DATE         => $post['created_date']
                               );
                    $results[] = $jsonobject;
                }
            }
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );

        $this->response($data);
    }
    
    /**
     * 
     *  API get Post
     * 
     *  Menthod: GET
     * 
     *  @param $limit
     *  @param $page
     * 
     *  Response: JSONObject
     * 
     */    
    public function get_all_post_get(){
        //  Get limit from client
        $limit = $this->get("limit");
        //  Get page from client
        $page = $this->get("page");
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        $list_post = $this->restaurant_model->getAllPost();
        //  Array object post
        $results = array();
        //  Count object post
        $count = 0;
        //  Count resulte
        $count_resulte = 0;
        if(is_array($list_post)){
            foreach ($list_post as $post){
                $count++;
                if(($count) >= $position_start_get && ($count) <= $position_end_get){
                    $count_resulte ++;
                    //  Create JSONObject Post
                    $jsonobject = array( 
                               Post_enum::ID                     => $post['_id']->{'$id'},
                               Post_enum::ID_USER                => $post['id_user'],
                               Post_enum::TITLE                  => $post['title'],
                               Post_enum::AVATAR                 => $post['avatar'],
                               Post_enum::ADDRESS                => $post['address'],
                               Post_enum::FAVOURITE_TYPE_LIST    => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $post['favourite_type_list']),
                               Post_enum::PRICE_PERSON_LIST      => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $post['price_person_list']),
                               Post_enum::CULINARY_STYLE_LIST    => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $post['culinary_style_list']),
                               Post_enum::CONTENT                => $post['content'],
                               Post_enum::NUMBER_ASSESSMENT     => $this->restaurant_model->countAssessmentForPost($post['_id']->{'$id'}),
                               Post_enum::RATE_POINT            => $this->restaurant_model->getRatePoint(),
                               Post_enum::NUMBER_VIEW            => $post['number_view'],
                               Common_enum::UPDATED_DATE         => $post['updated_date'],
                               Common_enum::CREATED_DATE         => $post['created_date']
                               );
                    $results[] = $jsonobject;
                }
            }
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>$count_resulte,
               'Results'    =>$results
        );

        $this->response($data);
    }
    
    /**
     * 
     *  API get Post
     * 
     *  Menthod: GET
     * 
     *  @param $limit
     *  @param $page
     * 
     *  Response: JSONObject
     * 
     */    
    public function get_detail_post_get(){
        //  Get limit from client
        
        $id = $this->get('id');
        
        $list_post = $this->restaurant_model->getPostById($id);
        //  Array object post
        $results = array();
        
        //
        //  Edit field number_view: +1
        //
        $this->common_model->editSpecialField(Post_enum::COLLECTION_POST, $id, array('$inc' => array('number_view' => 1) ) );
        
        if(is_array($list_post)){
            foreach ($list_post as $post){

                    //  Create JSONObject Post
                    $jsonobject = array( 
                               Post_enum::ID                     => $post['_id']->{'$id'},
                               Post_enum::ID_USER                => $post['id_user'],
                               Post_enum::TITLE                  => $post['title'],
                               Post_enum::AVATAR                 => $post['avatar'],
                               Post_enum::ADDRESS                => $post['address'],
                               Post_enum::FAVOURITE_TYPE_LIST    => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $post['favourite_type_list']),
                               Post_enum::PRICE_PERSON_LIST      => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $post['price_person_list']),
                               Post_enum::CULINARY_STYLE_LIST    => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $post['culinary_style_list']),
                               Post_enum::CONTENT                => $post['content'],

                               Post_enum::NUMBER_ASSESSMENT     => $this->restaurant_model->countAssessmentForPost($post['_id']->{'$id'}),
                               Post_enum::RATE_POINT            => $this->restaurant_model->getRatePoint(),

                               Post_enum::NUMBER_VIEW            => $post['number_view'],
                               Common_enum::UPDATED_DATE         => $post['updated_date'],
                               Common_enum::CREATED_DATE         => $post['created_date']

                               );
                    $results[] = $jsonobject;
            }
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );

        $this->response($data);
    }
    
    /**
     * 
     * API Update Post
     * 
     * Menthod: POST
     * 
     * @param String $action
     * @param String $id
     * @param String $id_user
     * @param String $title
     * @param String $content
     * @param String $number_view
     * @param String $note
     * @param String $authors
     * 
     * Response: JSONObject
     * 
     */
   public function update_post_post(){
        
        //  Get param from client
        $action                 = $this->post('action');
        
        $id                     = $this->post('id');
        
        $id_user                = $this->post('id_user');
        $title                  = $this->post('title');
        $address                = $this->post('address');
        $favourite_type_list    = $this->post('favourite_type_list');
        $price_person_list      = $this->post('price_person_list');
        $culinary_style_list    = $this->post('culinary_style_list');
        $number_view             = $this->post('number_view');
        $content                = $this->post('content');
        $updated_date = $this->post('updated_date');
        $created_date = $this->post('created_date');
        
        //  More
        $str_image_post = $this->post('array_image');                   //  image.jpg,image2.png,...
        $array_image_post = explode(Common_enum::MARK, $str_image_post); //  ['image.jpg', 'image2.png' ,...]
        
        $file_avatar='';
        
        $base_path_post = Common_enum::ROOT.Common_enum::DIR_POST.$id_user.'/';
        
        //  Create directory $path
        if(!file_exists($base_path_post)){
            mkdir($base_path_post, 0, true);
        }
        
        for($i=0; $i<sizeof($array_image_post); $i++) {
            
            $file_temp = Common_enum::ROOT.Common_enum::PATH_TEMP.$array_image_post[$i];
            //file_exists('./include/modul_upload/upload_temp/content_21-11-2013_12-03-41_528d942d17bc6.jpg')
//            var_dump(file_exists($file_temp));
            if (file_exists($file_temp)) {
                
                $path_image_post = $base_path_post.$array_image_post[$i];
                var_dump($path_image_post);
                //  Move file from directory post
                $move_file = $this->common_model->moveFileToDirectory($file_temp, $path_image_post);
                
                if($move_file){
					
                    if($i==0){
                        //$file_avatar = str_replace(Common_enum::ROOT,'' ,$path_image_post);
                      $file_avatar=$id_user."/".$array_image_post[$i];
                    }
                    else{

//                            var_dump('Temp :'.str_replace(Common_enum::ROOT, Common_enum::LOCALHOST ,$file_temp));
//                            var_dump('Final :'.str_replace(Common_enum::ROOT, Common_enum::LOCALHOST ,$path_image_post));
//                            var_dump('Content :'.$content);

                            $content=str_replace(str_replace(Common_enum::ROOT, Common_enum::DOMAIN_NAME ,$file_temp), 
                                                 'folder_image_post_replace/'.$id_user."/".$array_image_post[$i],
                                                 $content);
                    }
                }
            }
        }
        
       (int)$is_insert = strcmp( strtolower($action), Common_enum::INSERT );
       (int)$is_delete = strcmp( strtolower($action), Common_enum::DELETE );
        
        $array_value = ($is_delete != 0) ? array(
                        Post_enum::ID_USER               => $id_user,
                        Post_enum::TITLE                 => $title,     
            
                        Post_enum::AVATAR                => $file_avatar,
                        Post_enum::ADDRESS               => $address,
                        Post_enum::FAVOURITE_TYPE_LIST   => explode(Common_enum::MARK, $favourite_type_list),
                        Post_enum::PRICE_PERSON_LIST     => explode(Common_enum::MARK, $price_person_list),
                        Post_enum::CULINARY_STYLE_LIST   => explode(Common_enum::MARK, $culinary_style_list),
            
                        Post_enum::CONTENT               => $content,
                        
                        Post_enum::NUMBER_VIEW           => (int)$number_view,
                        Common_enum::UPDATED_DATE       => ($updated_date == null ) ? $this->common_model->getCurrentDate(): $updated_date,
                        Common_enum::CREATED_DATE       => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                ) : array();
        
        $this->restaurant_model->updatePost($action, $id, $array_value);
        $error = $this->restaurant_model->getError();
        
        if($error == null){
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    //------------------------------------------------------
    //                                                     /
    //  APIs Subscribed Email                              /
    //                                                     /
    //------------------------------------------------------
    
    /**
     * 
     *  API get Subscribed Email
     * 
     *  Menthod: GET
     *  @param limit
     *  @param page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_all_subscribed_email_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
                
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get collection subscribed_email
        $collection_name = Subscribed_email_enum::COLLECTION_SUBSCRIBED;
        $list_subscribed_email = $this->restaurant_model->getAllSubcribedEmail($collection_name);
        
        //  Array object subscribed_email
        $results = array();
        
        //  Count object subscribed_email
        $count = 0;
        foreach ($list_subscribed_email as $subscribed_email){
            $count++;
            if(($count) >= $position_start_get && ($count) <= $position_end_get){
                //  Create JSONObject Post
                $jsonobject = array( 
                           Subscribed_email_enum::ID        => $subscribed_email['_id']->{'$id'},
                           Subscribed_email_enum::EMAIL     => $subscribed_email['email'],
                           Common_enum::UPDATED_DATE        => $subscribed_email['updated_date'],
                           Common_enum::CREATED_DATE        => $subscribed_email['created_date']
                           );
                $results[] = $jsonobject;
            }
        }
        
        //  Response
//        $data = array();
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>sizeof($results),
               'Results'    =>$results
        );

        $this->response($data);
    }
    
    /**
     * 
     *  API update Subcribed Email
     * 
     *  Menthod: POST
     * 
     *  @param String $id
     *  @param String $email
     * 
     *  Response: JSONObject
     * 
     */
    public function update_email_post(){
        
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        $email                      = $this->post('email');
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        
        if($email == null){
            //  Response
            $resulte =  array(
               'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
               'Error'      =>'Email is null'
            );

            $this->response($resulte);
            return;
        }
        
        $array_value = array(
                        Subscribed_email_enum::EMAIL    => $email,
                        Common_enum::UPDATED_DATE       => ($updated_date == null ) ? $this->common_model->getCurrentDate(): $updated_date,
                        Common_enum::CREATED_DATE       => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                );
        
        $this->restaurant_model->updateSubcribedEmail($action, $id, $array_value);
        $error = $this->restaurant_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        else{
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
        
    }
    
    /**
     * 
     *  API get list Restaurant liked by user
     * 
     *  Menthod: GET
     *  @param limit
     *  @param page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_list_restaurant_liked_by_user_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        //  Get page from client
        $page = $this->get("page");
        
        $id_user = $this->get('id_user');
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get list id_restaurant liked by user
        $array_id_restaurant = $this->user_model->getRestaurantsLikedByUser($id_user);
        
        $results = array();
        $count = 0;
        if(is_array($array_id_restaurant)){
            foreach ($array_id_restaurant as $value){
                $count++;
                $array_restaurant = $this->restaurant_model->getRestaurantById($value);
                $restaurant = $array_restaurant[$value];
                
                if(($count) >= $position_start_get && ($count) <= $position_end_get){
                        //  Create JSONObject Restaurant
                        $jsonobject = array( 
                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                            Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::AVATAR                     => $restaurant['avatar'],

                            Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                            Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
                            Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),

                            Restaurant_enum::FAVOURITE_LIST    		=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                            Restaurant_enum::PRICE_PERSON_LIST      	=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
                            Restaurant_enum::CULINARY_STYLE_LIST    	=> $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
							
                            //  Number LIKE of Restaurant
                            Restaurant_enum::NUMBER_LIKE                 => $this->user_model->countUserLogByAction(array ( 
                                                                                                                            User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                            User_log_enum::ACTION        => Common_enum::LIKE_RESTAURANT
                                                                                                                            )),
                            //  Number SHARE of Restaurant
                            Restaurant_enum::NUMBER_SHARE                => $this->user_model->countUserLogByAction(array ( 
                                                                                                                        User_log_enum::ID_RESTAURANT => $restaurant['_id']->{'$id'}, 
                                                                                                                        User_log_enum::ACTION        => Common_enum::SHARE_RESTAURANT
                                                                                                                        )),
                            Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                            Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                            Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                            Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),

                            Restaurant_enum::ADDRESS                    => $restaurant['address'],
                            Restaurant_enum::CITY                       => $restaurant['city'],
                            Restaurant_enum::DISTRICT                   => $restaurant['district'],
                            Restaurant_enum::EMAIL                      => $restaurant['email'],
                            Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                            Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'], 

                            Common_enum::UPDATED_DATE         => $restaurant['updated_date'],
                            Common_enum::CREATED_DATE         => $restaurant['created_date']
                                                                                                                                
                        );
                        $results[] = $jsonobject;
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>sizeof($results),
                   'Results'    =>$results
            );

            $this->response($data);
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>sizeof($results),
               'Results'    =>$results
        );

        $this->response($data);
    }
    
    /**
     * 
     *  API get list User like Restaurant
     * 
     *  Menthod: GET
     *  @param limit
     *  @param page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_list_user_liked_restaurant_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        //  Get page from client
        $page = $this->get("page");
        
        $id_restaurant = $this->get('id_restaurant');
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get list id_restaurant liked by user
        $array_id_user = $this->user_model->getUsersLikedRestaurant($id_restaurant);
        
        $results = array();
        $count = 0;
        if(is_array($array_id_user)){
            foreach ($array_id_user as $value){
                $count++;
                $array_user = $this->user_model->getUserById($value);
                $user = $array_user[$value];
                
                if(($count) >= $position_start_get && ($count) <= $position_end_get){
                        //  Create JSONObject
                        $jsonobject = array( 
                                    User_enum::ID                => $user['_id']->{'$id'},
                                    User_enum::FULL_NAME         => $user['full_name'],
                                    User_enum::EMAIL             => $user['email'],        
                                    User_enum::PHONE_NUMBER      => $user['phone_number'],
                                    User_enum::ADDRESS           => $user['address'],
                                    User_enum::LOCATION          => $user['location'],
                                    User_enum::AVATAR            => $user['avatar'],
                                    User_enum::ROLE_LIST         => $user['role_list'],
                                    User_enum::DESC              => $user['desc'],
                                    User_enum::IS_DELETE         => $user['is_delete'],
                                    Common_enum::UPDATED_DATE    => $user['updated_date'],
                                    Common_enum::CREATED_DATE    => $user['created_date']
                                   );
                        $results[] = $jsonobject;
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
                   'Total'      =>sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        //  Response
        $data =  array(
               'Status'     =>Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Total'      =>sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
    }
    
}

?>
