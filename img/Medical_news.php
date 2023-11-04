<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Class Medical_news
 */
class Medical_news extends CI_Controller{
	public $data = [];

	public function __construct()	{
		parent::__construct();    
		$this->load->library(['ion_auth','form_validation','common_lib','upload']);
		$this->load->helper(['url', 'language','form']);

    $this->load->model('meta_info_model');
		$this->load->model('medical_news_model');
    
		$this->load->model('articles_comments_model');
    
    
     // $this->_remap($this->router->fetch_method());
    $this->home_meta_data       = $this->meta_info_model->get_meta_info_data(1);

	}
  
    

  function _remap($method){
    if (method_exists($this, $method)){
      $this->$method();
    } else {
      $this->index($method);
    }
  }



	/**
	* Medical news list
	*/

 //function _remap($param1,$param2) {
  //  $this->index($param1,$param2);
  //}  
	public function index()	{
    
    $data = array();
    
    $data["is_sub_cat"] = FALSE;
    
    $data['meta_title']   = $this->home_meta_data[0]['meta_title']; 		
		$data['meta_keyword'] = $this->home_meta_data[0]['meta_keyword']; 		
		$data['meta_desc']    = $this->home_meta_data[0]['meta_desc'];

    if($this->uri->segment('2') == ""){
      $data["medical_cat"]= 'all_categories';
      $data["medical_cat_bc_title"]= '';
    }else{
      $cat_dat = $this->medical_news_model->get_alias_cat_id_data($this->uri->segment('2'));
      if(count($cat_dat) == 0){
        $data["medical_cat"]= 'all_categories';
        $data["medical_cat_bc_title"]= '';

      }else{
        $data["medical_cat"]= $this->uri->segment('2');
        if($cat_dat[0]["meta_title"] !=""){
          $data['meta_title']   = $cat_dat[0]["meta_title"];
        }else{
          $data['meta_title']   = 'Drug today online '. $cat_dat[0]['cat_name'].' Articles'; 		
        }        
        if($cat_dat[0]["meta_keyword"] !=""){
          $data['meta_keyword']   = $cat_dat[0]["meta_keyword"];
        }else{
          $data['meta_keyword'] = $this->home_meta_data[0]['meta_keyword']; 	
        }        
        if($cat_dat[0]["meta_desc"] !=""){
          $data['meta_desc']    = $cat_dat[0]["meta_desc"];
        }else{
          $data['meta_desc']    = $this->home_meta_data[0]['meta_desc']; 
        }
        
        
        $data["medical_cat_bc_title"]= $cat_dat[0]['cat_name'];
        $data["is_sub_cat"]= TRUE;
      }
    }
      /*
      echo "<br>---------- title ---------------<br>";
      print_r($data['meta_title']);
      echo "<br>---------- meta_keyword ---------------<br>";

      print_r($data['meta_keyword']);
      echo "<br>---------- meta_desc ---------------<br>";
      print_r($data['meta_desc']);
      */
    
    $this->load->view('front/medical_news_list',$data);	
	}
  
	public function fetch_medical_news(){
    
    sleep(1);

    if($this->uri->segment('3') == ""){
      $medical_cat= 'all_categories';
    }else{
      $cat_dat = $this->medical_news_model->get_alias_cat_id_data($this->uri->segment('3'));
      if(count($cat_dat) == 0){
        $medical_cat= 'all_categories';
      }else{
        $medical_cat= $this->uri->segment('3');
      }
    }

    if($this->uri->segment('4') == ""){$medical_cat_page_no= 1;}else{$medical_cat_page_no= $this->uri->segment('4');}


		$this->load->library("pagination");    
    $config_nav = array();
    
    // $config_nav = array();
		$config_nav["base_url"] = "#";
		$config_nav["total_rows"] = $this->medical_news_model->medical_news_count_all($medical_cat);
		$config_nav["per_page"] = 15;
		$config_nav["uri_segment"] = 4;
		$config_nav["use_page_numbers"] = TRUE;    
		$config_nav["num_links"] = 3;
    //$config['display_pages'] = FALSE;    
    $config_nav['full_tag_open'] = '<ul class="pagination">';
    $config_nav['full_tag_close'] = '</ul>';
    $config_nav['attributes'] = ['class' => 'page-link'];

    $config_nav['first_link'] = '&laquo First';    
    $config_nav['first_tag_open'] = '<li class="page-item">';
    $config_nav['first_tag_close'] = '</li>';
    
    $config_nav['prev_link'] = '&lt Previous';
    $config_nav['prev_tag_open'] = '<li class="page-item">';
    $config_nav['prev_tag_close'] = '</li>';
    
    $config_nav['next_link'] = 'Next &gt';
    $config_nav['next_tag_open'] = '<li class="page-item">';
    $config_nav['next_tag_close'] = '</li>';

    $config_nav['last_link'] = 'Last  &raquo';    
    $config_nav['last_tag_open'] = '<li class="page-item">';
    $config_nav['last_tag_close'] = '</li>';
    
    $config_nav['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
    $config_nav['cur_tag_close'] = '<span class="sr-only">(current)</span></a></li>';
    
    $config_nav['num_tag_open'] = '<li class="page-item">';
    $config_nav['num_tag_close'] = '</li>';
		
    // echo "<pre>";
    // print_r($config_nav);
    // echo "</pre>";
    $this->pagination->initialize($config_nav);
		// $page = $medical_cat_page_no;
		$start = ($medical_cat_page_no - 1) * $config_nav["per_page"];

    $medical_news_data = $this->medical_news_model->fetch_medical_news_data($config_nav["per_page"], $start, $medical_cat);
     
    $output = '';
     
    if($medical_news_data->num_rows() > 0){
       
      $num_of_cols  = 3;
      $row_count    = 0;
      $output = '<div class="row mb-5">';
      
      foreach($medical_news_data->result_array() as $medical_news_row){
        $intro_img = "";
        $intro_img = base_url().$medical_news_row["intro_img"];
        //$txt=$medical_news_row["intro_text"]." ".$medical_news_row["full_text"];
        /*
        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $medical_news_row["intro_text"], $image);
        if(isset($image['src'])){$imgSrc=$image['src'];
          if (strpos($imgSrc, 'http://') !== false) {}else{$imgSrc=$url.$imgSrc;}        
        }else{$imgSrc='';}
        */
        
        $content = preg_replace("/<img[^>]+\>/i", "", $medical_news_row["intro_text"]);
        $short_content = $this->common_lib->reduce_str_content_news($content);
        //        substr($content,0,180);
        
        
        $output .= '<div class="col-lg-4">
                    <div class="card" style="min-height: 585px !important">
                      <a href="'.base_url().'medical-news/news-topic/'.$medical_news_row["id"].'-'.$medical_news_row["alias"].'"><img class="card-img-top embed-responsive" style="max-height: 150px !important; min-height: 150px" src="'.$intro_img.'" alt="'.$medical_news_row["title"].'" title="'.$medical_news_row["title"].'"></a>
                      <div class="card-block">
                          <h6 class="card-title">'.$medical_news_row["title"].'</h6>
                          <p class="card-text clearfix">'.$short_content.'</p>
                      </div>
                      <div class="card-footer">
                       <a href="'.base_url().'medical-news/news-topic/'.$medical_news_row["id"].'-'.$medical_news_row["alias"].'" class="btn btn-primary btn-block">Read More</a>
                      </div>
                    </div>
                  </div>';
        $row_count++;
        if($row_count % $num_of_cols == 0){ $output .= '</div><div class="row mb-5">'; }   
        
      
      }
      $output .= '</div>';
     }else{
      $output = '<h6>No Data Found</h6>';
     }

       
    
		$output = array(
			'pagination_link'		=>	$this->pagination->create_links(),
			'product_list'			=>	$output
		);
		echo json_encode($output);
	}
  

  public function news_topic()	{
    $data = array();  

    // echo 'Param 1: '.$this->uri->segment('1')."<br>";
    // echo 'Param 2: '.$this->uri->segment('2')."<br>";
    if($this->uri->segment('3') == ""){show_404();}else{
      
      $med_news_id = explode("-",$this->uri->segment('3'));
      $med_news_data = $this->medical_news_model->get_alias_art_data($med_news_id[0]);
      
      if(count($med_news_data) == 0 ){show_404();}else{
        
        $med_news_cat_id = $med_news_data[0]['cat_id'];
        
        $med_news_cat_data = $this->medical_news_model->get_alias_art_cat_data($med_news_cat_id);
        
        $this->data['cat_name'] = $med_news_cat_data[0]['cat_name'];
        $this->data['cat_alias'] = $med_news_cat_data[0]['alias'];
        
        /* set meta & title*/
        if($med_news_data[0]["meta_title"] !=""){
          $this->data['meta_title']   = $med_news_data[0]["meta_title"];
        }else{
          $this->data['meta_title']   = $med_news_data[0]["title"]; 		
        }        
        if($med_news_data[0]["meta_keyword"] !=""){
          $this->data['meta_keyword'] = $med_news_data[0]["meta_keyword"];
        }else{
          $this->data['meta_keyword'] = $this->home_meta_data[0]['meta_keyword']; 	
        }        
        if($med_news_data[0]["meta_desc"] !=""){
          $this->data['meta_desc']    = $med_news_data[0]["meta_desc"];
        }else{
          $this->data['meta_desc']    = $this->home_meta_data[0]['meta_desc']; 
        }
        $this->data['meta_author']    = $med_news_data[0]["created_by"];
        /* set meta & title*/
        
        
        $this->data['title']    = $med_news_data[0]["title"];
        
        $this->data['news_date'] = date("F j, Y",strtotime($med_news_data[0]["con"]));
        
        $this->data['intro_img'] = base_url().$med_news_data[0]["intro_img"];
         
        //$txt=$med_news_data[0]["intro_text"]." ".$med_news_data[0]["full_text"];
        /*
        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $med_news_data[0]["intro_text"], $image);
        if(isset($image['src'])){$imgSrc=$image['src'];
          if (strpos($imgSrc, 'http://') !== false) {}else{$imgSrc=$imgSrc;}        
        }else{$imgSrc='';}
        $this->data['intro_img'] = $imgSrc;

        */
        
        $this->data['content'] = preg_replace("/<img[^>]+\>/i", "", $med_news_data[0]["intro_text"]." ". $med_news_data[0]["full_text"]);
       
        $this->data['icaption'] = $med_news_data[0]["icaption"];
        //echo "<pre>";
        // print_r($this->data);
        // echo "</pre>";

        $this->data['med_news_related_cat_data'] = array();
        
        $med_news_related_cat_data = $this->medical_news_model->get_related_art_cat_data($med_news_cat_id,$med_news_id[0],'4');

        $this->data['med_news_related_cat_data'] = $med_news_related_cat_data;
        
        $this->data['article_comments_data'] = array();

        $this->data['article_comments_data'] = $this->articles_comments_model->get_articles_comments($med_news_id[0]);



        /* form validation */  
        $form_rules = array(
                              array(
                                    'field' =>  'txtName',
                                    'rules' =>  'trim|required',
                                    'errors' => array('required' => 'Please fill in the name')
                                    ),
                              array(
                                    'field' =>  'txtEmail',
                                    'rules' =>  'trim|required|valid_email',
                                    'errors' => array('required' => 'Please fill in the name',
                                                      'valid_email' => 'Please fill in valid email id'
                                                      )
                                  ),
                              array(
                                    'field' =>  'txtMobile',
                                    'rules' =>  'trim|required',
                                    'errors' => array('required' => 'Please fill in the mobile number')
                                  ),
                              array(
                                    'field' =>  'txtAddress',
                                    'rules' =>  'trim|required',
                                    'errors' => array('required' => 'Please fill in the address')
                                  ),
                              array(
                                    'field' =>  'txtMessage',
                                    'rules' =>  'trim|required',
                                    'errors' => array('required' => 'Please fill in the Message')
                                  ),
                            );    
        $this->form_validation->set_rules($form_rules);
        /* form validation */  

        /* form processing */  
    
        $this->data['txtName'] = '';
        $this->data['txtEmail'] = '';
        $this->data['txtMobile'] = '';
        $this->data['txtAddress'] = '';
        $this->data['txtMessage'] = '';
        if($this->input->post('txtName') != ""){ $this->data['txtName'] = $this->input->post('txtName'); }
        if($this->input->post('txtEmail') != ""){ $this->data['txtEmail'] = $this->input->post('txtEmail'); }
        if($this->input->post('txtMobile') != ""){ $this->data['txtMobile'] = $this->input->post('txtMobile'); }
        if($this->input->post('txtAddress') != ""){ $this->data['txtAddress'] = $this->input->post('txtAddress'); }
        if($this->input->post('txtMessage') != ""){ $this->data['txtMessage'] = $this->input->post('txtMessage'); }

        $this->data['curr_url'] = base_url().'medical-news/news-topic/'.$this->uri->segment('3');
 
        if($this->form_validation->run() == TRUE){
          // echo " form validation Success ";

          $ins_inquiry = $this->articles_comments_model->add_article_comment($this->data['txtName'],$this->data['txtMobile'],$this->data['txtEmail'],$this->data['txtAddress'],$this->data['txtMessage'],$med_news_id[0]);
        
          if($ins_inquiry){
            // order inserted successfully
            $insert_id = $this->db->insert_id();
           
            $this->session->set_flashdata('suc_message', 'Thank you<br> Your comment has been submitted, will get back to you soon');
            
            redirect($this->data['curr_url']);
            
          }else{
            // unable to add order
            $this->data['message'] = array('error'=>1,'error_message'=>"Unable to send Comment, please try again later");	
          }  
        }  
      }
    }
    //echo "here 1";
    $this->load->view('front/news_topic',$this->data);	
	}


  public function search(){
    
    // echo "here";
    $data = array();    
    $data['meta_title']   = $this->home_meta_data[0]['meta_title']; 		
		$data['meta_keyword'] = $this->home_meta_data[0]['meta_keyword']; 		
		$data['meta_desc']    = $this->home_meta_data[0]['meta_desc'];

    if($this->input->post('search') == ""){
      show_404();  
    }else{
      //echo "in search";
      $data['search_str'] = urlencode($this->input->post('search'));
    }
    
    $this->load->view('front/medical_news_search',$data);	
  }


  public function fetch_search_medical_news(){
    
    sleep(1);
      /*
      echo "\n---------- post ---------------\n";
      echo "Param 1: ".$this->uri->segment('1')."\n";
      echo "Param 2: ".$this->uri->segment('2')."\n";
      echo "Param 3: ".$this->uri->segment('3')."\n";
      echo "Param 4: ".$this->uri->segment('4')."\n";
      echo "\n---------- post ---------------\n";
      //*/
    $this->search_str = "";
    
    if($this->uri->segment('3') == ""){
      $this->search_str = '';
    }else{
      $this->search_str = urldecode($this->uri->segment('3'));
    }
    if($this->uri->segment('4') == ""){$medical_search_page_no= 1;}else{$medical_search_page_no= $this->uri->segment('4');}

		$this->load->library("pagination");    
    $config_nav = array();
    
    // $config_nav = array();
		$config_nav["base_url"] = "#";
		$config_nav["total_rows"] = $this->medical_news_model->medical_news_search_count_all($this->search_str);
		$config_nav["per_page"] = 15;
		$config_nav["uri_segment"] = 4;
		$config_nav["use_page_numbers"] = TRUE;    
		$config_nav["num_links"] = 3;
    //$config['display_pages'] = FALSE;    
    $config_nav['full_tag_open'] = '<ul class="pagination">';
    $config_nav['full_tag_close'] = '</ul>';
    $config_nav['attributes'] = ['class' => 'page-link'];

    $config_nav['first_link'] = '&laquo First';    
    $config_nav['first_tag_open'] = '<li class="page-item">';
    $config_nav['first_tag_close'] = '</li>';
    
    $config_nav['prev_link'] = '&lt Previous';
    $config_nav['prev_tag_open'] = '<li class="page-item">';
    $config_nav['prev_tag_close'] = '</li>';
    
    $config_nav['next_link'] = 'Next &gt';
    $config_nav['next_tag_open'] = '<li class="page-item">';
    $config_nav['next_tag_close'] = '</li>';

    $config_nav['last_link'] = 'Last  &raquo';    
    $config_nav['last_tag_open'] = '<li class="page-item">';
    $config_nav['last_tag_close'] = '</li>';
    
    $config_nav['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
    $config_nav['cur_tag_close'] = '<span class="sr-only">(current)</span></a></li>';
    
    $config_nav['num_tag_open'] = '<li class="page-item">';
    $config_nav['num_tag_close'] = '</li>';
		
    // echo "<pre>";
    // print_r($config_nav);
    // echo "</pre>";
    $this->pagination->initialize($config_nav);
		// $page = $medical_search_page_no;
		$start = ($medical_search_page_no - 1) * $config_nav["per_page"];

    $medical_news_data = $this->medical_news_model->fetch_medical_news_search_data($config_nav["per_page"], $start, $this->search_str);
     
    $output = '';
     
    if($medical_news_data->num_rows() > 0){
       
      $num_of_cols  = 3;
      $row_count    = 0;
      $output = '<div class="row mb-5">';
      
      foreach($medical_news_data->result_array() as $medical_news_row){
        
        $intro_img = base_url().$medical_news_row["intro_img"];
        //$txt=$medical_news_row["intro_text"]." ".$medical_news_row["full_text"];
        /*
        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $medical_news_row["intro_text"], $image);
        if(isset($image['src'])){$imgSrc=$image['src'];
          if (strpos($imgSrc, 'http://') !== false) {}else{$imgSrc=$url.$imgSrc;}        
        }else{$imgSrc='';}
        */
        
        $content = preg_replace("/<img[^>]+\>/i", "", $medical_news_row["intro_text"]);
        $short_content = substr($content,0,180);
        
        $output .= '<div class="col-lg-4">
                    <div class="card" style="min-height: 585px !important">
                      <a href="'.base_url().'medical-news/news-topic/'.$medical_news_row["id"].'-'.$medical_news_row["alias"].'"><img class="card-img-top embed-responsive" style="max-height: 150px !important;" src="'.$intro_img.'" alt="'.$medical_news_row["title"].'" title="'.$medical_news_row["title"].'"></a>
                      <div class="card-block">
                          <h6 class="card-title">'.$medical_news_row["title"].'</h6>
                          <p class="card-text clearfix">'.$short_content.'</p>
                      </div>
                      <div class="card-footer">
                       <a href="'.base_url().'medical-news/news-topic/'.$medical_news_row["id"].'-'.$medical_news_row["alias"].'" class="btn btn-primary btn-block">Read More</a>
                      </div>
                    </div>
                  </div>';
        $row_count++;
        if($row_count % $num_of_cols == 0){ $output .= '</div><div class="row mb-5">'; }   
        
      
      }
      $output .= '</div>';
     }else{
      $output = '<h6>No Data Found</h6>';
     }

       
    
		$output = array(
			'pagination_link'		=>	$this->pagination->create_links(),
			'product_list'			=>	$output
		);
		echo json_encode($output);
	}


}