<?php 

class Restaurant_controller extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
    
        public function __construct() {
            parent::__construct();
            
            $this->load->model('restaurant/restaurant_apis');
            $this->load->model('common/common_apis');
            $this->load->model('user/user_apis');
        }
    
	public function index()
	{
//            echo ($this->load->helper("url"));
        var_dump($this->user_apis->get_role_by_id('527b512b3fce119ed62d8599'));
            
	}
}

