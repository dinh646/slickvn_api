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
        
    }
    
    //----------------------------------------------------//
    //                                                    //
    //  APIs Common                                       //
    //                                                    //
    //----------------------------------------------------//
    
    public function upload_image_post() {
        
        //  Get param from client
        $type           = $this->post('type');
        $name_retaurant = $this->post('name_retaurant');
        
        $this->common_model->uploadImage($type, $name_retaurant);
        $error = $this->common_model->getError();
        
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
     * Check Exist Value in a collecstion by $field => $value
     * 
     * @param String $collection_name
     * @param String $field
     * @param String $value
     * 
     * @return boolean
     * 
     **/
    public function check_exist_value_post() {
        
        //  Get param from client
        $collection_name = $this->post('collection_name');
        $field           = $this->post('field');
        $value           = $this->post('value');
        
        
        
        if($collection_name != null && $field != null && $value != null){
            
            $result = $this->common_model->checkExistValue($collection_name, array($field => $value) );
            
            $data =  array(
                   'Status'     =>'SUCCESSFUL',
                   'Result'      =>$result
            );
            $this->response($data);
            
        }
        else{
            $data =  array(
                   'Status'     =>'FALSE',
                   'Error'      =>'Param is null'
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
            foreach ($get_collection as $value){
                $count ++;
                //  Create JSONObject
                $jsonobject = array( 

                            Common_enum::ID              => $value['_id']->{'$id'},
                            Common_enum::NAME            => $value['name'],
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
            foreach ($get_collection as $value){
                $count ++;
                //  Create JSONObject
                $jsonobject = array( 

                            Common_enum::ID              => $value['_id']->{'$id'},
                            Common_enum::NAME            => $value['name'],
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
        $created_date   = $this->post('created_date');
        
        (int)$is_delete = strcmp( strtolower($action), Common_enum::DELETE );
        
        //  Array value
        $array_value = ($is_delete != 0) ? array(
            
            Common_enum::NAME            => $name,
            Common_enum::CREATED_DATE    => $created_date      
            
        ) : array();
        
        //  Resulte
        $resulte = array();
        
        $this->common_model->updateBaseCollection($action, $collection, $id, $array_value);
        
        $error = $this->common_model->getError();
        
        if( $error == null ){

            //  Response
            $resulte =  array(
               'Status'     =>'SUCCESSFUL',
               'Error'      =>$error
            );

            $this->response($resulte);

        }else{
            //  Response
            $resulte =  array(
               'Status'     =>'FALSE',
               'Error'      =>$error
            );

            $this->response($resulte);
        }
        
    }
    
}

?>
