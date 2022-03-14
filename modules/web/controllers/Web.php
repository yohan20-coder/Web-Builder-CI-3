<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
*| --------------------------------------------------------------------------
*| Web Controller
*| --------------------------------------------------------------------------
*| For default controller
*|
*/
class Web extends Front
{
	
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		if (installation_complete()) {
			$this->home();
		} else {
			redirect('wizzard/language','refresh');
		}
	}

	public function switch_lang($lang = 'english')
	{
        $this->load->helper(['cookie']);

        set_cookie('language', $lang, (60 * 60 * 24) * 365 );
        $this->lang->load('web', $lang);
        redirect_back();
	}

	public function home() 
	{
        if (defined('IS_DEMO')) {
          $this->template->build('home-demo');
        } else {
		  $this->template->build('home');
        }
	}

	public function set_full_group_sql()
	{
        $this->db->query(" 
            set global sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
         "); 

        $this->db->query(" 
            set session sql_mode=’STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION’;
         ");

	}

    public function migrate($version = null)
    {
        $this->load->library('migration');
            
        if ($version) {
            if ($this->migration->version($version) === FALSE) {
               show_error($this->migration->error_string());
            }   
        } 
        else {
            if ($this->migration->latest() === FALSE) {
               show_error($this->migration->error_string());
            }   
        }

    }

    public function migrate_cicool()
    {
        $this->load->helper('file');
        $this->load->helper('directory');

        $files = (directory_map('application/controllers/administrator')); 

        foreach ($files as $file) {
            $f_name = str_replace('.php', '', $file);
            $f_name_lower = strtolower(str_replace('.php', '', $file));

            if ($file == 'index.html' ) {
                continue;
            }
            if ($f_name_lower != 'web') {
                
            mkdir('modules/'.$f_name);
            mkdir('modules/'.$f_name.'/models');
            mkdir('modules/'.$f_name.'/views');
            mkdir('modules/'.$f_name.'/controllers');
            mkdir('modules/'.$f_name.'/controllers/backend');
            mkdir('modules/'.$f_name.'/views/backend');
            mkdir('modules/'.$f_name.'/views/backend/standart');
            mkdir('modules/'.$f_name.'/views/backend/standart/administrator');
            copy(FCPATH.'/application/models/Model_'.$f_name_lower.'.php', 'modules/'.$f_name_lower.'/models/Model_'.$f_name_lower.'.php' );
            copy(FCPATH.'/application/controllers/administrator/'.$f_name.'.php', 'modules/'.$f_name.'/controllers/backend/'.$f_name.'.php' );
            if (is_dir(FCPATH.'/application/views/backend/standart/administrator/'.$f_name_lower)) {
                
            $this->recurse_copy(FCPATH.'/application/views/backend/standart/administrator/'.$f_name_lower, 'modules/'.$f_name.'/views/backend/standart/administrator/'.$f_name_lower );
            }
            //unlink('modules/'.$f_name_lower.'/models'.$f_name_lower.'.php' );
            }
        }


    }
    public function migrate_cicool_front()
    {
        $this->load->helper('file');
        $this->load->helper('directory');

        $files = (directory_map('application/controllers')); 

        foreach ($files as $file) {
            $f_name = str_replace('.php', '', $file);
            $f_name_lower = strtolower(str_replace('.php', '', $file));

            if ($file == 'index.html' ) {
                continue;
            }
            if ($f_name_lower != 'web') {
                
            mkdir('modules/'.$f_name);
            mkdir('modules/'.$f_name.'/models');
            mkdir('modules/'.$f_name.'/views');
            mkdir('modules/'.$f_name.'/controllers');
            mkdir('modules/'.$f_name.'/controllers');
            mkdir('modules/'.$f_name.'/views/backend');
            mkdir('modules/'.$f_name.'/views/backend/standart');
            mkdir('modules/'.$f_name.'/views/backend/standart/administrator');
            copy(FCPATH.'/application/models/Model_'.$f_name_lower.'.php', 'modules/'.$f_name_lower.'/models/Model_'.$f_name_lower.'.php' );
            copy(FCPATH.'/application/controllers/'.$f_name.'.php', 'modules/'.$f_name.'/controllers/'.$f_name.'.php' );
            if (is_dir(FCPATH.'/application/views/backend/standart/administrator/'.$f_name_lower)) {
                
            $this->recurse_copy(FCPATH.'/application/views/backend/standart/administrator/'.$f_name_lower, 'modules/'.$f_name.'/views/backend/standart/administrator/'.$f_name_lower );
            }
            //unlink('modules/'.$f_name_lower.'/models'.$f_name_lower.'.php' );
            }
        }


    }

    public function  recurse_copy($src,$dst) { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { 
                    copy($src . '/' . $file,$dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
    } 

    function image($mime_type_or_return = 'image/png')
    {
        $file_path = $this->input->get('path');
        $this->helper('file');

        $image_content = read_file($file_path);

        // Image was not found
        if($image_content === FALSE)
        {
            show_error('Image "'.$file_path.'" could not be found.');
            return FALSE;
        }

        // Return the image or output it?
        if($mime_type_or_return === TRUE)
        {
            return $image_content;
        }

        header('Content-Length: '.strlen($image_content)); // sends filesize header
        header('Content-Type: '.$mime_type_or_return); // send mime-type header
        header('Content-Disposition: inline; filename="'.basename($file_path).'";'); // sends filename header
        exit($image_content); // reads and outputs the file onto the output buffer
    }
}


/* End of file Web.php */
/* Location: ./application/controllers/Web.php */