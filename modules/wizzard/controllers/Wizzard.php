<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
*| --------------------------------------------------------------------------
*| Wizzard Controller
*| --------------------------------------------------------------------------
*| For setup first application
*|
*/
class Wizzard extends MX_Controller
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->helper(['cookie']);

		if ($lang = get_cookie('language')) {

            $this->lang->load([
                'web',
                'form_validation',
                'upload',
                'db',
            ], $lang);
        }
	}


	public function language($page = 1)
	{
		$data = [];

		$this->template->set_partial('content', 'frontend/standart/wizzard/select_language');
		$this->template->build('frontend/standart/main_layout', $data);
	}


	public function select_language()
	{

		$lang = $this->input->post('language');

        set_cookie('language', $lang, (60 * 60 * 24) * 365 );
        set_cookie('language_sys', $lang, (60 * 60 * 24) * 365 );
        $this->lang->load('web', $lang);
        redirect('wizzard/setup/1');
	}

	public function setup($page = 1)
	{
		$this->load->helper(['cookie']);

		if (installation_complete()) {
			redirect('');
		}
		$mysql_version_number = get_mysql_version();


		$directory_requirements = [
			'uploads',
			'uploads/user',
			'uploads/tmp',
			'application',
			'application/config',
			'application/routes',
			'application/routes/routes_landing.php',
			'application/cache',
			'application/cache',
			'modules',
			'application/config/database.php',
			'application/config/config.php',
			'application/migrations/001_cicool.php',
		];

		$server_requirements = [
			'mysqli_extension_installed' 	=> function_exists('mysqli_connect'),
			'session_extension_installed'   => extension_loaded('session'),
			'mcrypt_extension_installed'   	=> extension_loaded('mcrypt'),
			'openssl_function_exists'       => function_exists('openssl_random_pseudo_bytes'),
			'php_version_is_greater'		=> phpversion() >= 5.5 ? TRUE : FALSE,
			'mysql_version_is_greater'		=> $mysql_version_number >= 4.3 ? TRUE : FALSE,
		];

		$directory_requirement_is_ok = TRUE;
		$server_requirement_is_ok = TRUE;
		$next = TRUE;

		foreach($directory_requirements as $requirement => $path) {
			if (!is_writable(FCPATH . $path)) {
				$directory_requirement_is_ok = FALSE;
			}
		}

		//If either Mcrypt or OpenSSL exists, proceed with installation.
		if ($server_requirements['mcrypt_extension_installed'] OR $server_requirements['openssl_function_exists']) 
		{
			$server_requirements['mcrypt_extension_installed'] = TRUE;
			$server_requirements['openssl_function_exists'] = TRUE;
		}

		foreach($server_requirements as $requirement => $status) {
			if (!$status) {
				$server_requirement_is_ok = FALSE;
			}
		}

		if ($server_requirement_is_ok == FALSE OR $server_requirements == FALSE) {
			$next = FALSE;
		}

		//page ssetup wizzard
		if ($page == 1) {

			$data = [
				'php_version_is_greater'		=> $server_requirements['php_version_is_greater'],
				'mysqli_extension_installed' 	=> $server_requirements['mysqli_extension_installed'],
				'session_extension_installed'   => $server_requirements['session_extension_installed'],
				'mcrypt_extension_installed'   	=> $server_requirements['mcrypt_extension_installed'],
				'openssl_function_exists'   	=> $server_requirements['openssl_function_exists'],
				'mysql_version_is_greater'		=> $server_requirements['mysql_version_is_greater'],
				'mysql_version_number'			=> $mysql_version_number,
				'directory_requirement_is_ok'   => $directory_requirement_is_ok,
				'server_requirement_is_ok'   	=> $server_requirement_is_ok,
				'next'							=> $next,
				'directory_requirements' 		=> $directory_requirements
			];

			$this->template->set_partial('content', 'frontend/standart/wizzard/page_one');
			$this->template->build('frontend/standart/main_layout', $data);
		} elseif ($page == 2) {
			$this->load->library('parser');
			$this->load->helper('file');
			$this->load->helper('cookie');

			if (!$next) {
				redirect('/','refresh');
			}


			$lang = get_cookie('language_sys');

			$data = [
				'php_tag_open' 					=> '<?php',
				'permitted_uri_chars'			=> addslashes($this->input->post('permitted_uri_chars')),
				'url_suffix'					=> addslashes($this->input->post('url_suffix')),
				'encryption_key'				=> addslashes($this->input->post('encryption_key')),
				'sess_expiration'				=> addslashes($this->input->post('sess_expiration')),
				'sess_time_to_update'			=> addslashes($this->input->post('sess_time_to_update')),
				'global_xss_filtering'			=> addslashes($this->input->post('global_xss_filtering')),
				'language'						=> $lang ? $lang : 'english',
				'csrf_token_name'				=> '__'.generate_key(),
				'csrf_cookie_name'				=> '__'.generate_key(),
				'csrf_expire'					=> 7200,
				'sess_cookie_name'				=> '__'.generate_key(),
				'times' => [
					['label' => '15 Minutes', 'value' => '900'],
					['label' => '30 Minutes', 'value' => '1800'],
					['label' => '1 Hours', 'value' => '3600'],
					['label' => '2 Hours', 'value' => '7200'],
					['label' => '4 Hours', 'value' => '14400'],
					['label' => '6 Hours', 'value' => '21600'],
					['label' => '8 Hours', 'value' => '28800'],
					['label' => '12 Hours', 'value' => '43200'],
					['label' => '1 Days', 'value' => '86400'],
					['label' => '1 Week', 'value' => '604800'],
					['label' => '1 Month', 'value' => '2592000'],
					['label' => '6 Month', 'value' => '15552000'],
					['label' => '1 Years', 'value' => '31104000'],
					['label' => 'Always', 'value' => '0']
				]
			];
			
			$this->form_validation->set_rules('permitted_uri_chars', 'Permitted URI Chars', 'trim|required');
			$this->form_validation->set_rules('encryption_key', 'Encryption Key', 'trim|required');
			$this->form_validation->set_rules('sess_expiration', 'Session Expiration', 'trim|required');
			$this->form_validation->set_rules('sess_time_to_update', 'Session Time to Update', 'trim|required');
			$this->form_validation->set_rules('global_xss_filtering', 'Global XSS Filtering', 'trim|required');

			if ($this->form_validation->run()) {
				$config_template = $this->parser->parse('core_template/config_template.txt', $data, TRUE);
				write_file(FCPATH . '/application/config/config.php', $config_template);

				redirect('wizzard/setup/3','refresh');
			} else {
				$data['error'] = validation_errors();
			}

			$this->template->set_partial('content', 'frontend/standart/wizzard/page_two');
			$this->template->build('frontend/standart/main_layout', $data);
		} elseif ($page == 3) {
			$this->load->library('parser');
			$this->load->helper('file');

			if (!$next) {
				redirect('/','refresh');
			}

			$data = [
				'php_tag_open' 					=> '<?php',
				'database'						=> $this->input->post('database'),
				'username'						=> $this->input->post('username'),
				'password'						=> $this->input->post('password'),
				'hostname'						=> $this->input->post('hostname'),
			];

			$this->form_validation->set_rules('database', 'Database Name', 'trim|required');
			$this->form_validation->set_rules('username', 'Username', 'trim|required');
			$this->form_validation->set_rules('hostname', 'Hostname', 'trim|required');
			$this->form_validation->set_rules('site_name', 'Site Name', 'trim|required');
			$this->form_validation->set_rules('site_email', 'Site Email', 'trim|required|valid_email');
			$this->form_validation->set_rules('site_password', 'Site Password', 'trim|required|min_length[6]');

			if ($this->form_validation->run()) {
				if ($this->check_connection()) {
					$config_template = $this->parser->parse('core_template/database_template.txt', $data, TRUE);
					write_file(FCPATH . '/application/config/database.php', $config_template);

					$this->load->config('config');

					$this->load->library('migration');
					//install database from migration
					
			        if ($this->migration->version(1) === FALSE) {
			           show_error($this->migration->error_string());
			        }	

			    	$data = [
						'php_tag_open' 					=> '<?php',
						'site_name'						=> $this->input->post('site_name'),
						'version'						=> VERSION,
					];

					$this->load->library(['aauth', 'cc_app']);

					$email = $this->input->post('site_email');
					$username = explode('@', $email)[0];
					$username = str_replace('-', '', url_title($username));

					if(!$this->aauth->create_user($email, $this->input->post('site_password'), $username, ['avatar' => '', 'full_name' => $username])) {
						show_error($this->aauth->print_errors());
					} 

			        $site_config_template = $this->parser->parse('core_template/site_template.txt', $data, TRUE);
					write_file(FCPATH . '/application/config/site.php', $site_config_template);

					add_option('site_name', $this->input->post('site_name'));


                	$this->cc_app->eventListen('after_install');

					redirect('wizzard/complete');
				} else {
					$data['error'] = 'Unable to connect the database, please check the database configuration';
				}
			} else {
				$data['error'] = validation_errors();
			}

			$this->template->set_partial('content', 'frontend/standart/wizzard/page_three');
			$this->template->build('frontend/standart/main_layout', $data);
		}
	}

	public function valid_database($database = '')
	{
		$this->load->dbutil();

		if (!$this->dbutil->database_exists($database)) {
			$this->form_validation->set_message('valid_database', 'Database "'.$database.'" is not exist');	
			return FALSE;
		}

		return TRUE;
	}

	public function complete()
	{
        $data = [];

    	$this->template->set_partial('content', 'frontend/standart/wizzard/page_complete');
		$this->template->build('frontend/standart/main_layout', $data);
	}

	public function check_db_connection()
	{
		error_reporting(0);

		if ($this->check_connection()) {
			$response = [
        		'success' => true,
        		'message' => 'database connected',
        	];
		} else {
			$response = [
        		'success' => false,
        		'message' => 'unable to connect the database',
        	];
		}
        
        echo json_encode($response);
	}

	public function check_connection()
	{
		$hostname 	= $this->input->post('hostname');
        $username 	= $this->input->post('username');
        $password 	= $this->input->post('password');
        $dbname 	= $this->input->post('database');

        try {
            $dbh = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);

            $dbh-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $status =  true;
        } catch (PDOException $e) {
            $status =  false;
        }
        
        return $status;
	}
}