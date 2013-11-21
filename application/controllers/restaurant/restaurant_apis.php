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
        
        foreach ($list_assessment as $assessment){

            $approval = $assessment['approval'];
            
            if( strcmp(strtoupper($approval), AssessmentEnum::APPROVAL_YES) == 0){
            
                $count ++ ;

                if(($count) >= $position_start_get && ($count) <= $position_end_get){

                    //  Get User of Assessment
                    $user = $this->Usermodel->getUserById($assessment['id_user']);
                    
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
                        AssessmentEnum::NUMBER_LIKE                 => $this->Usermodel->countUserLogByAction(array ( 
                                                                                                                        UserLogEnum::ID_ASSESSMENT => $assessment['_id']->{'$id'}, 
                                                                                                                        UserLogEnum::ACTION        => Common_enum::LIKE_ASSESSMENT
                                                                                                                        )),
                        //  Number SHARE of Assessment
                        AssessmentEnum::NUMBER_SHARE                => $this->Usermodel->countUserLogByAction(array ( 
                                                                                                                        UserLogEnum::ID_ASSESSMENT => $assessment['_id']->{'$id'}, 
                                                                                                                        UserLogEnum::ACTION        => Common_enum::SHARE_ASSESSMENT
                                                                                                                        )),
                        AssessmentEnum::COMMENT_LIST                =>  $this->restaurant_model->getCommentByIdAssessment($assessment['_id']->{'$id'}),
                                
                        Common_enum::CREATED_DATE                    => $assessment['created_date']
                                

                    );

                    $results[] = $jsonobject;

                }
            }
        }
        //  Response
        $data =  array(
               'Status'     =>'SUCCESSFUL',
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
        
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
    public function get_all_menu_dish_get() {
        
        $list_menu_dish = $this->restaurant_model->getMenuDish();
        
        $results = array();
        
        foreach ($list_menu_dish as $menu_dish) {
            
            $jsonobject = array(
                
                    Menu_dish_enum::ID                => $menu_dish['_id']->{'$id'},
                    Menu_dish_enum::ID_RESTAURANT     => $menu_dish['id_restaurant'],
                    Menu_dish_enum::DISH_LIST         => $menu_dish['dish_list'],        
//                    Menu_dish_enum::NAME              => $menu_dish['name'],
//                    Menu_dish_enum::DESC              => $menu_dish['desc'],
//                    Menu_dish_enum::PRICE             => $menu_dish['price'],
//                    Menu_dish_enum::SIGNATURE_DISH    => $menu_dish['signature_dish'],
//                    Menu_dish_enum::LINK_IMAGE        => $menu_dish['link_image'],
                
                    Common_enum::CREATED_DATE        => $menu_dish['created_date']
                );
            $results [] = $jsonobject;
                    
        }
        
        //  Response
        $data =  array(
               'Status'     =>'SUCCESSFUL',
               'Total'      =>  sizeof($results),
               'Results'    =>$results
        );
        $this->response($data);
        
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Restaurant                                   //
    //                                                    //
    //----------------------------------------------------//

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
    public function search_restaurant_by_name_get() {
        
        //  Get param from client
        $limit = $this->get("limit");
        $page = $this->get("page");

        //  Key search
        $key = $this->get('key');
        
        //  Query
        $where = array(Restaurant_enum::NAME => new MongoRegex('/'.$key.'/i'));
        $list_restaurant = $this->restaurant_model->searchRestaurant($where);
        
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if (sizeof($list_restaurant) > 0){
            
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

                            Restaurant_enum::FAVOURITE_LIST    		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
                            Restaurant_enum::PRICE_PERSON_LIST      		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
                            Restaurant_enum::CULINARY_STYLE_LIST    		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
							
                            Restaurant_enum::NUMBER_LIKE                => 0,
                            Restaurant_enum::NUMBER_SHARE               => 0,

                            Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
                            Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
                            Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
                            Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),

                            Restaurant_enum::ADDRESS                    => $restaurant['address'],
                            Restaurant_enum::CITY                       => $restaurant['city'],
                            Restaurant_enum::DISTRICT                   => $restaurant['district'],
                            Restaurant_enum::EMAIL                      => $restaurant['email'],
                            Restaurant_enum::IMAGE_INTRODUCE_LINK       => $restaurant['image_introduce_link'],
                            Restaurant_enum::IMAGE_CAROUSEL_LINK        => $restaurant['image_carousel_link'] 

                        );

                        $results[] = $jsonobject;
                    }
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
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
        if (sizeof($list_restaurant) > 0){
            
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

							Restaurant_enum::FAVOURITE_LIST    		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
							Restaurant_enum::PRICE_PERSON_LIST      		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
							Restaurant_enum::CULINARY_STYLE_LIST    		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
							
                            Restaurant_enum::NUMBER_LIKE                => 0,
                            Restaurant_enum::NUMBER_SHARE               => 0,

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

                        );

                        $results[] = $jsonobject;
                    }
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
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
        $key = $this->get('key');
        
        //  Query
        $where = array(Restaurant_enum::NAME => new MongoRegex('/'.$key.'/i'));
        $list_restaurant = $this->restaurant_model->searchRestaurant($where);
        
        //  End
        $position_end_get   = ($page == 1) ? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1) ? $page : ( $position_end_get - ($limit - 1) );
        
        //  Array object restaurant
        $results = array();
        
        //  Count object restaurant
        $count = 0;
        if (sizeof($list_restaurant) > 0){
            
            foreach ($list_restaurant as $restaurant){
                //  Current date
                $current_date = $this->common_model->getCurrentDate();

                //  End date
                $end_date = $restaurant['end_date'];
                //  Get interval expired
                $interval_expired = $this->common_model->getInterval($current_date, $end_date);

                //  Is delete
                $is_delete = $restaurant['is_delete'];

                //  Is coupon
                $is_coupon = ($restaurant['id_coupon'] == null) ? 0 : 1;
                
                if($interval_expired >=0 && $is_delete == 0 && $is_coupon == 1){

                    $count ++;

                    if(($count) >= $position_start_get && ($count) <= $position_end_get){

                        //  Create JSONObject Restaurant
                        $jsonobject = array( 

                            Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                            Restaurant_enum::ID_USER                    => $restaurant['id_user'],
                            Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                            Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],

                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::RATE_POINT                 => $restaurant['rate_point'],
                            Restaurant_enum::ADDRESS                    => $restaurant['address'],
                            Restaurant_enum::CITY                       => $restaurant['city'],
                            Restaurant_enum::DISTRICT                   => $restaurant['district'],
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
                            Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],

                            Restaurant_enum::START_DATE                 => $restaurant['start_date'],
                            Restaurant_enum::END_DATE                   => $restaurant['end_date'],

                            Common_enum::CREATED_DATE                   => $restaurant['created_date'] 

                        );

                        $results[] = $jsonobject;
                    }
                }
            }
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
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

		//
        //  Edit field number_view: +1
        //
        
		
        //  Key search
        $key = $this->get('key');
        
        //  Query find collection Menu Dish by name
        $where = array(Menu_dish_enum::DISH_LIST.'.'.Menu_dish_enum::NAME => new MongoRegex('/'.$key.'/i'));
        $list_menu_dish = $this->restaurant_model->searchMenuDish($where);
        
        //  List restaurant
        $list_restaurant = array();
        
        if (sizeof($list_menu_dish) > 0){
            
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
                                Restaurant_enum::ID_USER                    => $restaurant['id_user'],
                                Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                                Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
								Restaurant_enum::AVATAR					   => $restaurant['avatar'],
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
								
                                //Restaurant_enum::FAVOURITE_LIST             => $restaurant['favourite_list'],
                                //Restaurant_enum::PRICE_PERSON_LIST          => $restaurant['price_person_list'],
                                //Restaurant_enum::CULINARY_STYLE_LIST        => $restaurant['culinary_style_list'],
								
								Restaurant_enum::FAVOURITE_LIST    		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $restaurant['favourite_list']),
							    Restaurant_enum::PRICE_PERSON_LIST      		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $restaurant['price_person_list']),
							    Restaurant_enum::CULINARY_STYLE_LIST    		   => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $restaurant['culinary_style_list']),
								
								
                                Restaurant_enum::MODE_USE_LIST              => $restaurant['mode_use_list'],
                                Restaurant_enum::PAYMENT_TYPE_LIST          => $restaurant['payment_type_list'],
                                Restaurant_enum::LANDSCAPE_LIST             => $restaurant['landscape_list'],
                                Restaurant_enum::OTHER_CRITERIA_LIST        => $restaurant['other_criteria_list'],
                                Restaurant_enum::INTRODUCE                  => $restaurant['introduce'],
								
                                Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
								Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($restaurant['_id']->{'$id'}),
								Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),
										
								Restaurant_enum::NUMBER_LIKE                => 0,
								Restaurant_enum::NUMBER_SHARE               => 0,
										
								Restaurant_enum::RATE_SERVICE               => $this->restaurant_model->getRateService(),
								Restaurant_enum::RATE_LANDSCAPE             => $this->restaurant_model->getRateLandscape(),
								Restaurant_enum::RATE_TASTE                 => $this->restaurant_model->getRateTaste(),
								Restaurant_enum::RATE_PRICE                 => $this->restaurant_model->getRatePrice(),

                                Restaurant_enum::START_DATE                 => $restaurant['start_date'],
                                Restaurant_enum::END_DATE                   => $restaurant['end_date'],

                                Common_enum::CREATED_DATE                   => $restaurant['created_date'] 

                            );

                            $results[] = $jsonobject;
                        }
                    }
                }
                
            }
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
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


                    //  Create JSONObject Restaurant
                    $jsonobject = array( 

                        Restaurant_enum::ID                         => $restaurant['_id']->{'$id'},
                        //Restaurant_enum::ID_USER                    => $restaurant['id_user'],
                        Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                        Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],
                        Restaurant_enum::NAME                       => $restaurant['name'],
                                
                        Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],
                        Restaurant_enum::NUMBER_ASSESSMENT          => $this->restaurant_model->countAssessmentForRestaurant($id),
                        Restaurant_enum::RATE_POINT                 => $this->restaurant_model->getRatePoint(),
                                
                        Restaurant_enum::NUMBER_LIKE                => 0,
                        Restaurant_enum::NUMBER_SHARE               => 0,
                                
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
                        Common_enum::CREATED_DATE                   => $restaurant['created_date'] 

                    );

                    $results[] = $jsonobject;


                }


            }
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );
            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>'FALSE',
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

                        );

                        $results[] = $jsonobject;

                    }

                }


            }
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );


            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>'FALSE',
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
                            Restaurant_enum::ID_USER                    => $restaurant['id_user'],
                            Restaurant_enum::ID_MENU_DISH               => $restaurant['id_menu_dish'],
                            Restaurant_enum::ID_COUPON                  => $restaurant['id_coupon'],

                            Restaurant_enum::NAME                       => $restaurant['name'],
                            Restaurant_enum::RATE_POINT                 => $restaurant['rate_point'],
                            Restaurant_enum::ADDRESS                    => $restaurant['address'],
                            Restaurant_enum::CITY                       => $restaurant['city'],
                            Restaurant_enum::DISTRICT                   => $restaurant['district'],
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
                            Restaurant_enum::NUMBER_VIEW                => $restaurant['number_view'],

                            Restaurant_enum::START_DATE                 => $restaurant['start_date'],
                            Restaurant_enum::END_DATE                   => $restaurant['end_date'],

                            Common_enum::CREATED_DATE                   => $restaurant['created_date'] 

                        );

                        $results[] = $jsonobject;

                    }

                }


            }
            //  Response
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Total'      =>  sizeof($results),
                   'Results'    =>$results
            );


            $this->response($data);
        }
        else{
            //  Response
            $data =  array(
                   'Status'     =>'FALSE',
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
//            var_dump($interval);
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
                        Restaurant_enum::NUMBER_LIKE                => 0


                    );
                
                    $results[] = $jsonobject;
                    
                    $this->restaurant_model->setRateService(0);
                    $this->restaurant_model->setRateLandscape(0);
                    $this->restaurant_model->setRateTaste(0);
                    $this->restaurant_model->setRatePrice(0);
                    
                }
            }
            
        }
        //  Response
        $data =  array(
               'Status'     =>'SUCCESSFUL',
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
                        Restaurant_enum::NUMBER_LIKE                => 0


                    );
                
                    $results[] = $jsonobject;
                    
                    $this->restaurant_model->setRateService(0);
                    $this->restaurant_model->setRateLandscape(0);
                    $this->restaurant_model->setRateTaste(0);
                    $this->restaurant_model->setRatePrice(0);
                    
                }
            }
            
        }
        
        //  Response
        $data =  array(
               'Status'     =>'SUCCESSFUL',
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
        $id_user                 = $this->post('id_user');
        $id_menu_dish            = $this->post('id_menu_dish');
        $id_coupon               = $this->post('id_coupon');
        $name                    = $this->post('name');
        $folder_name             = $this->post('folder_name');
        
        $email                   = $this->post('email');
        $desc                   = $this->post('desc');
        
        $approval_show_carousel  = $this->post('approval_show_carousel');
        $address                 = $this->post('address');
        $city                    = $this->post('city');
        $district                = $this->post('district');
        $link_to                 = $this->post('link_to');
        $phone_number            = $this->post('phone_number');
        $working_time            = $this->post('working_time');
        $status_active           = $this->post('status_active');
        
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
        $is_delete               = $this->post('is_delete');
        
        //  More
        $str_image_post = $this->post('array_image');                   //  image.jpg,image2.png,...
        $array_image_post = explode(Common_enum::MARK, $str_image_post); //  ['image.jpg', 'image2.png' ,...]
        
        $file_avatar;
        $file_carousel;
        $file_introduce = array();
        
        $base_path_restaurant = Common_enum::ROOT.Common_enum::DIR_RESTAURANT.$folder_name.'/images/';
        
        $path_avatar    = $base_path_restaurant.'avatar/';
        $path_carousel  = $base_path_restaurant.'carousel/';
        $path_introduce = $base_path_restaurant.'introduce/';
        
        //  Create directory $path
        if(!file_exists($path_avatar)){
            mkdir($path_avatar, 0, true);
        }
        if(!file_exists($path_carousel)){
            mkdir($path_carousel, 0, true);
        }
        if(!file_exists($path_introduce)){
            mkdir($path_introduce, 0, true);
        }
        
        for($i=0; $i<sizeof($array_image_post); $i++) {
            
            $file_temp = Common_enum::ROOT.Common_enum::PATH_TEMP.$array_image_post[$i];
           // var_dump('temp ['.$i.'] = '.$file_temp);
   
            if (file_exists($file_temp)) {
                
                //  Move file from directory post
                if($i == 0){
                  $move_file_avatar = $this->common_model->moveFileToDirectory($file_temp, $path_avatar.$array_image_post[$i]);
                  $file_avatar = $folder_name.'/images/avatar/'.$array_image_post[0];
                }
                else if($i==1){
                  $move_file_carousel = $this->common_model->moveFileToDirectory($file_temp, $path_carousel.$array_image_post[$i]);
                  $file_carousel = $folder_name.'/images/carousel/'.$array_image_post[1];
                  
                }
                else{
                  $move_file_introduce = $this->common_model->moveFileToDirectory($file_temp, $path_introduce.$array_image_post[$i]);
//                  $introduce = str_replace(str_replace(Common_enum::ROOT, Common_enum::LOCALHOST ,$file_temp), 
//                                            str_replace(Common_enum::ROOT, Common_enum::LOCALHOST ,$path_introduce.$array_image_post[$i]),
//                                            $introduce);
          var_dump($file_temp);
          
          var_dump($introduce);
          
                  $introduce = str_replace(str_replace(Common_enum::ROOT, Common_enum::DOMAIN_NAME ,$file_temp), 'folder_image_introduce_detail_page/'.$folder_name.'/images/introduce/'.$array_image_post[$i], $introduce);
                  
                  $file_introduce []= $folder_name.'/images/introduce/'.$array_image_post[$i];
                  
                }
                
            }
            
        }
        
        (int)$is_insert = strcmp( strtolower($action), Common_enum::INSERT );
        
        $array_value = array( 

//            Restaurant_enum::ID                         => $id,
//            Restaurant_enum::ID_USER                    => $id_user,
            Restaurant_enum::ID_MENU_DISH               => $id_menu_dish,
            Restaurant_enum::ID_COUPON                  => $id_coupon,

            Restaurant_enum::NAME                       => $name,
            Restaurant_enum::FOLDER_NAME                => $folder_name,
            Restaurant_enum::EMAIL                      => $email,
            Restaurant_enum::AVATAR                     => $file_avatar,
            Restaurant_enum::APPROVAL_SHOW_CAROSUEL     => ($approval_show_carousel != null) ? (int)$approval_show_carousel : 1,
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
            Restaurant_enum::NUMBER_VIEW                => (int)$number_view,

            Restaurant_enum::START_DATE                 => $start_date,
            Restaurant_enum::END_DATE                   => $end_date,

            Common_enum::CREATED_DATE                   => ($is_insert == 0 ) ? $this->common_model->getCurrentDate(): $created_date,
            Restaurant_enum::IS_DELETE                  => ($is_insert == 0 ) ? Restaurant_enum::DEFAULT_IS_DELETE : (int)$is_delete
                
        );
        
        $this->restaurant_model->updateRestaurant($action, $id, $array_value);
        $error = $this->restaurant_model->getError();
        
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
    
    
    
    //------------------------------------------------------
    //                                                     /
    //  APIs Coupon                                        /
    //                                                     /
    //------------------------------------------------------
    
    /**
     * 
     *  API get Coupon
     * 
     *  Menthod: GET
     * 
     *  @param $limit
     *  @param $page
     * 
     *  Response: JSONObject
     * 
     */
    public function get_coupon_list_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
                
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get collection coupon
        $collection_name = Coupon_enum::COLLECTION_NAME;
        $list_coupon = $this->common_model->getCollection($collection_name);
        
        //  Array object coupon
        $results = array();
        
        //  Count object coupon
        $count = 0;
        
        //  Count result
        $count_result = 0;
        
        foreach ($list_coupon as $coupon){
            
            //  Get deal to date
            $deal_to_date = $coupon['deal_to_date'];

            //  Get now date
            $now_date = new DateTime();

            //  Get interval
            $interval = $this->common_model->getInterval($now_date->format('d-m-Y H:i:s'), $deal_to_date);
            
            if($interval >= 0){
                
                $count++;
                
                if(($count) >= $position_start_get && ($count) <= $position_end_get){
                    
                    $count_result ++ ;
                
                    //  Create JSONObject Coupon
                    $jsonobject = array( 

                               Coupon_enum::ID               => $coupon['_id']->{'$id'},
                               Coupon_enum::COUPON_VALUE     => $coupon['coupon_value'],
                               Coupon_enum::DEAL_TO_DATE     => $coupon['deal_to_date'],
                               Coupon_enum::RESTAURANT_NAME  => $coupon['restaurant_name'],
                               Coupon_enum::CONTENT          => $coupon['content'],
                               Coupon_enum::IMAGE_LINK       => Coupon_enum::BASE_IMAGE_LINK.$coupon['image_link'],
                               Coupon_enum::LINK_TO          => $coupon['link_to']
                                       
                               );

                    $results[] = $jsonobject;
                    
                }
            }
            
        }
        
        //  Response
//        $data = array();
        $data =  array(
               'Status'     =>'SUCCESSFUL',
               'Total'      =>$count_result,
               'Results'    =>$results
        );

        $this->response($data);
    }
    
    /**
     * 
     * API insert Coupon
     * 
     * Menthod: POST
     * 
     * @param int $coupon_value
     * @param String $deal_to_date
     * @param String $restaurant_name
     * @param String $content
     * @param String $image_link
     * @param String $link_to
     * 
     *  Response: JSONObject
     * 
     */
    public function insert_coupon_post(){
        
        //  Get param from client;
         $coupon_value          = $this->post('coupon_value');
         $deal_to_date          = $this->post('deal_to_date');
         $restaurant_name       = $this->post('restaurant_name');
         $content               = $this->post('content');
         $image_link            = $this->post('image_link');
         $link_to               = $this->post('link_to');
         
        //  Resulte
        $resulte = array();
        
        if($coupon_value == null || $deal_to_date == null || $restaurant_name == null || 
           $content == null || $image_link == null || $link_to == null){
           
            //  Response error
            $resulte =  array(
                   'Status'     =>'FALSE',
                   'Error'      => 'Param is NULL'
            );

            $this->response($resulte);
            
        }else{
            
            $error = $this->restaurant_model->insertCoupon($coupon_value, $deal_to_date, $restaurant_name, 
                                                           $content, $image_link, $link_to);
            
            //  If insert successful
            if( is_null($error) ){
                
                //  Response
                $resulte =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
                );

                $this->response($resulte);

            }
            else{
                //  Response error
                $resulte =  array(
                       'Status'     =>'FALSE',
                       'Error'      =>$error
                );

                $this->response($resulte);

            }
        }
        
    }
    
    /**
     * 
     * API upadate Coupon
     * 
     * Menthod: POST
     * 
     * @param String $id
     * @param int $coupon_value
     * @param String $deal_to_date
     * @param String $restaurant_name
     * @param String $content
     * @param String $image_link
     * @param String $link_to
     * 
     *  Response: JSONObject
     * 
     */
    public function update_coupon_post(){
        
        //  Get param from client
         $id                    = $this->post('id');
         $coupon_value          = $this->post('coupon_value');
         $deal_to_date          = $this->post('deal_to_date');
         $restaurant_name       = $this->post('restaurant_name');
         $content               = $this->post('content');
         $image_link            = $this->post('image_link');
         $link_to               = $this->post('link_to');
        
        //  Resulte
        $resulte = array();
        
        if($id == null || $coupon_value == null || $deal_to_date == null || $restaurant_name == null || 
           $content == null || $image_link == null || $link_to == null){
           
            //  Response error
            $resulte =  array(
                   'Status'     =>'FALSE',
                   'Error'      => 'Param is NULL'
            );

            $this->response($resulte);
            
        }else{
            
            $error = $this->restaurant_model->updateCoupon($id, $coupon_value, $deal_to_date, $restaurant_name, 
                                                           $content, $image_link, $link_to);
            
            //  If insert successful
            if( is_null($error) ){
                
                //  Response
                $resulte =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
                );

                $this->response($resulte);

            }
            else{
                //  Response error
                $resulte =  array(
                       'Status'     =>'FALSE',
                       'Error'      =>$error
                );

                $this->response($resulte);

            }
        }
        
    }
    
    /**
     * 
     * API delete Coupon
     * 
     * Menthod: POST
     * 
     * @param $id
     * 
     *  Response: JSONObject
     * 
     */
    public function delete_coupon_post(){
        
        //  Get param from client
        $id  = $this->post('id');
        
        //  Resulte
        $resulte = array();
        
        if($id == null){
            
            //  Response error
            $resulte =  array(
                   'Status'     =>'FALSE',
                   'Error'      => 'Param is NULL'
            );

            $this->response($resulte);
            
        }else{
            
            $error = $this->restaurant_model->deleteCoupon($id);
            
            //  If insert successful
            if( is_null($error) ){
                
                //  Response
                $resulte =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
                );

                $this->response($resulte);

            }
            else{
                //  Response error
                $resulte =  array(
                       'Status'     =>'FALSE',
                       'Error'      =>$error
                );

                $this->response($resulte);

            }
        }
        
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
                           Post_enum::FAVOURITE_TYPE_LIST    => $post['favourite_type_list'],
                           Post_enum::PRICE_PERSON_LIST      => $post['price_person_list'],
                           Post_enum::CULINARY_STYLE_LIST    => $post['culinary_style'],
                           Post_enum::CONTENT                => $post['content'],
                           Post_enum::NUMBER_VIEW            => $post['number_view'],
                           Post_enum::NOTE                   => $post['note'],
                           Post_enum::AUTHORS                => $post['authors'],
                           Common_enum::CREATED_DATE         => $post['created_date'],
                           
                           );

                $results[] = $jsonobject;

            }
            
        }
        
        //  Response
        $data =  array(
               'Status'     =>'SUCCESSFUL',
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
    public function get_post_list_get(){
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
                
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        $list_post = $this->restaurant_model->getAllPost();
//        
        //  Array object post
        $results = array();
        
        //  Count object post
        $count = 0;
        
        //  Count resulte
        $count_resulte = 0;
        
        foreach ($list_post as $post_){
            
			
			
            $count++;

            if(($count) >= $position_start_get && ($count) <= $position_end_get){

                $count_resulte ++;
             
                //  Create JSONObject Post
                $jsonobject = array( 
                    
                           Post_enum::ID                     => $post_['_id']->{'$id'},
                           Post_enum::ID_USER                => $post_['id_user'],
                           Post_enum::TITLE                  => $post_['title'],
                           Post_enum::AVATAR                 => $post_['avatar'],
                           Post_enum::ADDRESS                => $post_['address'],
                           Post_enum::FAVOURITE_TYPE_LIST    => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::FAVOURITE_TYPE,   $post_['favourite_type_list']),
                           Post_enum::PRICE_PERSON_LIST      => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::PRICE_PERSON,   $post_['price_person_list']),
                           Post_enum::CULINARY_STYLE_LIST    => $this->common_model->getValueFeildNameBaseCollectionById(Common_enum::CULINARY_STYLE,   $post_['culinary_style_list']),
                           Post_enum::CONTENT                => $post_['content'],
                           //Post_enum::NUMBER_VIEW            => $post['number_view'],
                           Post_enum::NOTE                   => $post_['note'],
                           Post_enum::AUTHORS                => $post_['authors'],
                           Common_enum::CREATED_DATE         => $post_['created_date'],
                           
                           );

                $results[] = $jsonobject;

            }
            
        }
        
        //  Response
//        $data = array();
        $data =  array(
               'Status'     =>'SUCCESSFUL',
               'Total'      =>$count_resulte,
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
		
        $content                = $this->post('content');
        $note                   = $this->post('note');
        $authors                = $this->post('authors');
        
        //  More
        $str_image_post = $this->post('array_image');                   //  image.jpg,image2.png,...
        $array_image_post = explode(Common_enum::MARK, $str_image_post); //  ['image.jpg', 'image2.png' ,...]
        
        $file_avatar;
        
        $base_path_post = Common_enum::ROOT.Common_enum::DIR_POST.$id_user.'/';
        
        //  Create directory $path
        if(!file_exists($base_path_post)){
            mkdir($base_path_post, 0, true);
        }
        
        for($i=0; $i<sizeof($array_image_post); $i++) {
            
            $file_temp = Common_enum::ROOT.Common_enum::PATH_TEMP.$array_image_post[$i];
           // var_dump('temp ['.$i.'] = '.$file_temp);
			
            if (file_exists($file_temp)) {
                
                $path_image_post = $base_path_post.$array_image_post[$i];
                
                //  Move file from directory post
                $move_file = $this->common_model->moveFileToDirectory($file_temp, $path_image_post);
                
                if($move_file){
					
                    if($i==0){
                        //$file_avatar = str_replace(Common_enum::ROOT,'' ,$path_image_post);
						$file_avatar=$id_user."/".$array_image_post[$i];
                    }
					else{
					
						var_dump('Temp :'.str_replace(Common_enum::ROOT, Common_enum::LOCALHOST ,$file_temp));
						var_dump('Final :'.str_replace(Common_enum::ROOT, Common_enum::LOCALHOST ,$path_image_post));
						var_dump('Content :'.$content);
						
						$content=str_replace(str_replace(Common_enum::ROOT, Common_enum::LOCALHOST ,$file_temp), 
								str_replace(Common_enum::ROOT, Common_enum::LOCALHOST ,$path_image_post),
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
                        //Post_enum::NUMBER_VIEW           => ($is_insert == 0) ? Post_enum::DEFAULT_NUMBER_VIEW : (int)$number_view,
                        Post_enum::NOTE                  => $note,
                        Post_enum::AUTHORS               => $authors,
                        Common_enum::CREATED_DATE        => $this->common_model->getCurrentDate()
                ) : array();
        
        $this->restaurant_model->updatePost($action, $id, $array_value);
        $error = $this->restaurant_model->getError();
        
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
    public function get_email_list_get() {
        
        //  Get limit from client
        $limit = $this->get("limit");
        
        //  Get page from client
        $page = $this->get("page");
                
        //  End
        $position_end_get   = ($page == 1)? $limit : ($limit * $page);
        
        //  Start
        $position_start_get = ($page == 1)? $page : ( $position_end_get - ($limit - 1) );
        
        // Get collection subscribed_email
        $collection_name = Subscribed_email_enum::COLLECTION_NAME;
        $list_subscribed_email = $this->restaurant_model->getCollection($collection_name);
        
        //  Array object subscribed_email
        $results = array();
        
        //  Count object subscribed_email
        $count = 0;
        
        //  Count resulte
        $count_resulte = 0;
        
        foreach ($list_subscribed_email as $subscribed_email){
            
            $count++;

            if(($count) >= $position_start_get && ($count) <= $position_end_get){

                $count_resulte ++;

                //  Create JSONObject Post
                $jsonobject = array( 
                    
                           Subscribed_email_enum::ID        => $subscribed_email['_id']->{'$id'},
                           Subscribed_email_enum::EMAIL     => $subscribed_email['email'],
                           
                           );

                $results[] = $jsonobject;

            }
            
        }
        
        //  Response
//        $data = array();
        $data =  array(
               'Status'     =>'SUCCESSFUL',
               'Total'      =>$count_resulte,
               'Results'    =>$results
        );

        $this->response($data);
    }
    
     /**
     * 
     *  API insert Subcribed Email
     * 
     *  Menthod: POST
     * 
     *  @param String $email
     * 
     *  Response: JSONObject
     * 
     */
    public function insert_email_post(){
        
        //  Get param from client
        $email = $this->post('email');
        
        //  Resulte
        $resulte = array();
        
        if($email == null){
            
            //  Response error
            $resulte =  array(
                   'Status'     =>'FALSE',
                   'Error'      => Subscribed_email_enum::EMAIL.' is NULL'
            );

            $this->response($resulte);
            
        }else{
            
            $error = $this->restaurant_model->insertEmail($email);
            
            //  If insert successful
            if( is_null($error) ){
                
                //  Response
                $resulte =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
                );

                $this->response($resulte);

            }
            else{
                //  Response error
                $resulte =  array(
                       'Status'     =>'FALSE',
                       'Error'      =>$error
                );

                $this->response($resulte);

            }
        }
        
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
        $id = $this->post('id');
        $email = $this->post('email');
        
        //  Resulte
        $resulte = array();
        
        if( $id == null ||$email == null){
            
            //  Response error
            $resulte =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>'Param is NULL'
            );

            $this->response($resulte);
            
        }else{
            
            $error = $this->restaurant_model->updateEmail($id, $email);
            
            //  If insert successful
            if( is_null($error) ){
                
                //  Response
                $resulte =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
                );

                $this->response($resulte);

            }
            else{
                //  Response error
                $resulte =  array(
                       'Status'     =>'FALSE',
                       'Error'      =>$error
                );

                $this->response($resulte);

            }
        }
        
    }
    
    /**
     * 
     *  API delete Subcribed Email
     * 
     *  Menthod: POST
     * 
     *  @param String $id
     * 
     *  Response: JSONObject
     * 
     */
    public function delete_email_post(){
        
        //  Get param from client
        $id = $this->post('id');
        
        //  Resulte
        $resulte = array();
        
        if( $id == null ){
            
            //  Response error
            $resulte =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>'Param is NULL'
            );

            $this->response($resulte);
            
        }else{
            
            $error = $this->restaurant_model->deleteEmail($id);
            
            //  If insert successful
            if( is_null($error) ){
                
                //  Response
                $resulte =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Error'      =>$error
                );

                $this->response($resulte);

            }
            else{
                //  Response error
                $resulte =  array(
                       'Status'     =>'FALSE',
                       'Error'      =>$error
                );

                $this->response($resulte);

            }
        }
        
    }
}

?>
