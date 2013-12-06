<?php

require APPPATH.'/libraries/REST_Controller.php';
/**
 * 
 * This class support APIs Common for client
 * 
 */
class common_apis extends REST_Controller{
    
    public function __construct() {
        parent::__construct();
        
        //  Load model COMMON
        $this->load->model('common/common_model');
        $this->load->model('common/common_enum');
        
        $this->load->model('common/info_website_enum');
        $this->load->model('common/communications_enum');
        
        $this->load->model('common/card_enum');
        $this->load->model('common/information_inquiry_enum');
        $this->load->model('common/member_card_enum');
        $this->load->model('common/my_favourites_enum');
        $this->load->model('common/quote_enum');
        
        
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Common                                       //
    //                                                    //
    //----------------------------------------------------//
    
//    public function upload_image_post() {
//        
//        //  Get param from client
//        $type           = $this->post('type');
//        $name_retaurant = $this->post('name_retaurant');
//        
//        $this->common_model->uploadImage($type, $name_retaurant);
//        $error = $this->common_model->getError();
//        
//        if($error == null){
//            $data =  array(
//                   'Status'     =>'SUCCESSFUL',
//                   'Error'      =>$error
//            );
//            $this->response($data);
//        }
//        else{
//            $data =  array(
//                   'Status'     =>'FALSE',
//                   'Error'      =>$error
//            );
//            $this->response($data);
//        }
//        
//    }
    
    /**
     * 
     * Check permisstion of user
     * 
     * @param arry $
     * 
     * @return boolean
     * 
     **/
//    public function check_permisstion_post() {
//        
//        //  Get param from client
//        $collection_name = $this->post('collection_name');
//        $field           = $this->post('field');
//        $value           = $this->post('value');
//        
//        
//        
//        if($collection_name != null && $field != null && $value != null){
//            
//            $result = $this->common_model->checkExistValue($collection_name, array($field => $value) );
//            
//            $data =  array(
//                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
//                   'Result'      =>$result
//            );
//            $this->response($data);
//            
//        }
//        else{
//            $data =  array(
//                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
//                   'Error'      =>'Param is null'
//            );
//            $this->response($data);
//        }
//        
//    }
    
    /**
     * 
     * Check Exist Value in a collecstion by $field => $value
     * 
     * @param String $collection_name
     * @param String $field
     * @param String $value
     * 
     * @return boolean
     * 
     **/
//    public function check_exist_value_post() {
//        
//        //  Get param from client
//        $collection_name = $this->post('collection_name');
//        $field           = $this->post('field');
//        $value           = $this->post('value');
//        
//        
//        
//        if($collection_name != null && $field != null && $value != null){
//            
//            $result = $this->common_model->checkExistValue($collection_name, array($field => $value) );
//            
//            $data =  array(
//                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
//                   'Result'      =>$result
//            );
//            $this->response($data);
//            
//        }
//        else{
//            $data =  array(
//                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
//                   'Error'      =>'Param is null'
//            );
//            $this->response($data);
//        }
//        
//    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Infor Website                                //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection Info website
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_info_website_get() {
        $collection = Info_website_enum::COLLECTION_INFO_WEBSITE;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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
                                Info_website_enum::ID                   => $value['_id']->{'$id'},
                                Info_website_enum::SECURITY_POLICIES    => $value['security_policies'],
                                Info_website_enum::TERMS_OF_USE         => $value['terms_of_use'],
                                Info_website_enum::CAREER_OPPORTUNITIES => $value['career_opportunities'],
                                Common_enum::UPDATED_DATE               => $value['updated_date'],
                                Common_enum::CREATED_DATE               => $value['created_date']
                               );
                    $results[] = $jsonobject;
                }
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
     * 
     * Update Collection Info website
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_info_website_post() {
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        $security_policies          = $this->post('security_policies');
        $terms_of_use               = $this->post('terms_of_use');
        $career_opportunities       = $this->post('career_opportunities');
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        
        $array_value = array(
                        Info_website_enum::SECURITY_POLICIES    => $security_policies,
                        Info_website_enum::TERMS_OF_USE         => $terms_of_use,
                        Info_website_enum::CAREER_OPPORTUNITIES => $career_opportunities,
                        Common_enum::UPDATED_DATE               => ($updated_date == null ) ? $this->common_model->getCurrentDate(): $updated_date,
                        Common_enum::CREATED_DATE               => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                );
        $this->common_model->updateCollection(Info_website_enum::COLLECTION_INFO_WEBSITE, $action, $id, $array_value);
        $error = $this->common_model->getError();
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
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Communications                                //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection Communications
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_communications_get() {
        $collection = Communications_enum::COLLECTION_COMMUNICATIONS;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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
                                Communications_enum::ID             => $value['_id']->{'$id'},
                                Communications_enum::TITLE          => $value['title'],
                                Communications_enum::CONTENT        => $value['content'],
                                Communications_enum::FULL_NAME      => $value['full_name'],
                                Communications_enum::EMAIL          => $value['email'],
                                Communications_enum::PHONE          => $value['phone'],
                                Common_enum::UPDATED_DATE           => $value['updated_date'],
                                Common_enum::CREATED_DATE           => $value['created_date']
                               );
                    $results[] = $jsonobject;
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
                   'Status'     =>Common_enum::MESSAGE_RESPONSE_FALSE,
                   'Error'      =>$error
            );
            $this->response($data);
        }
    }
    
    /**
     * 
     * Update Collection Communications
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_communications_post() {
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        $title                      = $this->post('title');
        $content                    = $this->post('content');
        $full_name                  = $this->post('full_name');
        $email                      = $this->post('email');
        $phone                      = $this->post('phone');
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        $array_value = array(
                        Communications_enum::TITLE          => $title,
                        Communications_enum::CONTENT        => $content,
                        Communications_enum::FULL_NAME      => $full_name,
                        Communications_enum::EMAIL          => $email,
                        Communications_enum::PHONE          => $phone,
                        Common_enum::UPDATED_DATE           => ($updated_date == null ) ? $this->common_model->getCurrentDate(): $updated_date,
                        Common_enum::CREATED_DATE           => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                );
        $this->common_model->updateCollection(Info_website_enum::COLLECTION_INFO_WEBSITE, $action, $id, $array_value);
        $error = $this->common_model->getError();
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
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Quote                                        //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection Quote
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_quote_get() {
        
        $collection = Quote_enum::COLLECTION_QUOTE;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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

                        //  TODO

                        );

                    $results[] = $jsonobject;
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
     * 
     * Update Collection Quote
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_quote_post() {
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        
        //  param
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        $array_value = array(
                        //  TODO
                );
        $this->common_model->updateCollection(Quote_enum::COLLECTION_QUOTE, $action, $id, $array_value);
        $error = $this->common_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
    //  APIs Information Inquiry                          //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection Information Inquiry
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_information_inquiry_get() {
        
        $collection = Information_inquiry_enum::COLLECTION_INFORMATION_INQUIRY;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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

                        Information_inquiry_enum::ID => $value['_id']->{'$id'},
                        Information_inquiry_enum::QUESTION => $value['question'],
                        Information_inquiry_enum::ANSWER => $value['answer'],
                        Common_enum::UPDATED_DATE => $value['updated_date'],
                        Common_enum::CREATED_DATE => $value['created_date']

                        );

                    $results[] = $jsonobject;
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
     * 
     * Update Collection Quote
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_information_inquiry_post() {
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        $question                   = $this->post('question');
        $answer                     = $this->post('answer');
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        $array_value = array(
                        Information_inquiry_enum::QUESTION  => $question,
                        Information_inquiry_enum::ANSWER    => $answer,
                        Common_enum::UPDATED_DATE           => ($updated_date == null ) ? $this->common_model->getCurrentDate(): $updated_date,
                        Common_enum::CREATED_DATE           => ($created_date == null ) ? $this->common_model->getCurrentDate(): $created_date
                );
        $this->common_model->updateCollection(Information_inquiry_enum::COLLECTION_INFORMATION_INQUIRY, $action, $id, $array_value);
        $error = $this->common_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
    //  APIs Card Slickvn                                 //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection Card
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_card_get() {
        
        $collection = Card_enum::COLLECTION_CARD;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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

                        //  TODO

                        );

                    $results[] = $jsonobject;
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
     * 
     * Update Collection Card
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_card_post(){
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        
        //  param
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        $array_value = array(
                        //  TODO
                );
        $this->common_model->updateCollection(Card_enum::COLLECTION_CARD, $action, $id, $array_value);
        $error = $this->common_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
    //  APIs Member Card                                  //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection Member Card
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_member_card_get() {
        
        $collection = Member_card_enum::COLLECTION_MEMBER_CARD;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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

                        //  TODO

                        );

                    $results[] = $jsonobject;
                }
            }
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
     * 
     * Update Collection Member Card
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_member_card_post() {
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        
        //  param
        
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        $array_value = array(
                        //  TODO
                );
        $this->common_model->updateCollection(Member_card_enum::COLLECTION_MEMBER_CARD, $action, $id, $array_value);
        $error = $this->common_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
    //  APIs My Favourite                                 //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection My Favourite
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_my_favourite_get() {
        
        $collection = My_favourites_enum::COLLECTION_MY_FAVOURITES;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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

                        //  TODO

                        );

                    $results[] = $jsonobject;
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
     * 
     * Update Collection My Favourite
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_my_favourite_post() {
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        
        //  param
        
        $updated_date                = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        $array_value = array(
                        //  TODO
                );
        $this->common_model->updateCollection(My_favourites_enum::COLLECTION_MY_FAVOURITES, $action, $id, $array_value);
        $error = $this->common_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
    //  APIs Booking                                      //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection Booking
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_booking_get() {
        
        $collection = Booking_enum::COLLECTION_BOOKING_ENUM;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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

                        //  TODO

                        );

                    $results[] = $jsonobject;
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
     * 
     * Update Collection Booking
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_booking_post() {
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        
        //  param
        
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        $array_value = array(
                        //  TODO
                );
        $this->common_model->updateCollection(Booking_enum::COLLECTION_BOOKING_ENUM, $action, $id, $array_value);
        $error = $this->common_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
    //  APIs Introduce                                    //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * 
     * Get Collection Introduce
     * 
     * Menthod: GET
     * 
     * Response: JSONObject
     * 
     */
    public function get_introduce_get() {
        
        $collection = Introduce_enum::COLLECTION_INTRODUCE;
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        $error = $this->common_model->getError();
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

                        //  TODO

                        );

                    $results[] = $jsonobject;
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
     * 
     * Update Collection Introduce
     * 
     * Menthod: POST
     * 
     * Response: JSONObject
     * 
     */
    public function update_introduce_post() {
        //  Get param from client
        $action                     = $this->post('action');
        $id                         = $this->post('id');
        
        //  param
        
        $updated_date               = $this->post('updated_date');
        $created_date               = $this->post('created_date');
        $array_value = array(
                        //  TODO
                );
        $this->common_model->updateCollection(Introduce_enum::COLLECTION_INTRODUCE, $action, $id, $array_value);
        $error = $this->common_model->getError();
        if($error == null){
            $data =  array(
                   'Status'     => Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
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
    //  APIs Base                                         //
    //                                                    //
    //----------------------------------------------------//
    
    /**
     * API Get Collection Base
     * 
     * Menthod: GET
     * 
     * @param String $collection_name
     * 
     * Response: JSONObject
     * 
     */
    public function get_base_collection_get() {
        
        //  Get param from client
        $collection = $this->get('collection_name');
        //  Get collection 
        $get_collection = $this->common_model->getCollection($collection);
        
        $error = $this->common_model->getError();
        
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

                                Common_enum::ID              => $value['_id']->{'$id'},
                                Common_enum::NAME            => $value['name'],
                                Common_enum::UPDATED_DATE    => $value['updated_date'],
                                Common_enum::CREATED_DATE    => $value['created_date']

                               );
                    $results[] = $jsonobject;
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
     * API Get Collection Base by Id
     * 
     * Menthod: GET
     * 
     * @param String $collection_name
     * @param String $id
     * Response: JSONObject
     * 
     */
    public function get_base_collection_by_id_get() {
        
        //  Get param from client
        $collection = $this->get('collection_name');
        $id         = $this->get('id');
        
        //  Get collection 
        $get_collection = $this->common_model->getCollectionById($collection, $id);
        
        $error = $this->common_model->getError();
        
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

                                Common_enum::ID              => $value['_id']->{'$id'},
                                Common_enum::NAME            => $value['name'],
                                Common_enum::UPDATED_DATE    => $value['updated_date'],
                                Common_enum::CREATED_DATE    => $value['created_date']

                               );
                    $results[] = $jsonobject;
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
     * 
     * API Update Collection Base
     * 
     * Menthod: POST
     * 
     * @param String $action    insert | edit | delete
     * @param String $collection_name
     * @param String $id
     * @param String $name
     * 
     * Response: JSONObject
     */
    public function update_base_collection_post(){
        
        //  Get param from client
        $action         = $this->post('action');
        $collection     = $this->post('collection_name');
        $id             = $this->post('id');
        $name           = $this->post('name');
        $updated_date   = $this->post('updated_date');
        $created_date   = $this->post('created_date');
        
        if($name == null){
            //  Response
            $resulte =  array(
               'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
               'Error'      =>'Name is null'
            );

            $this->response($resulte);
            return;
        }
        
        (int)$is_insert = strcmp( strtolower($action), Common_enum::INSERT );
        (int)$is_edit = strcmp( strtolower($action), Common_enum::EDIT );
        (int)$is_delete = strcmp( strtolower($action), Common_enum::DELETE );
        
        //  Array value
        $array_value = ($is_delete != 0) ? array(
            
            Common_enum::NAME            => $name,
            Common_enum::UPDATED_DATE    => ($updated_date==null) ? $this->common_model->getCurrentDate() : $updated_date,
            Common_enum::CREATED_DATE    => ($created_date==null) ? $this->common_model->getCurrentDate() : $created_date
            
        ) : array();
        
        //  Resulte
        $resulte = array();
        
        $this->common_model->updateBaseCollection($action, $collection, $id, $array_value);
        
        $error = $this->common_model->getError();
        if( $error == null ){

            //  Response
            $resulte =  array(
               'Status'     =>  Common_enum::MESSAGE_RESPONSE_SUCCESSFUL,
               'Error'      =>$error
            );

            $this->response($resulte);

        }else{
            //  Response
            $resulte =  array(
               'Status'     =>  Common_enum::MESSAGE_RESPONSE_FALSE,
               'Error'      =>$error
            );

            $this->response($resulte);
        }
        
    }
    
}

?>
