<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Form Test Controller
*| --------------------------------------------------------------------------
*| Form Test site
*|
*/
class Form_test extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_form_test');
	}

	/**
	* Submit Form Tests
	*
	*/
	public function submit()
	{
		$this->form_validation->set_rules('input', 'Input', 'trim|required');
		$this->form_validation->set_rules('textarea', 'Textarea', 'trim|required');
		$this->form_validation->set_rules('captcha', 'Captcha', 'trim|required|callback_valid_captcha');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'input' => $this->input->post('input'),
				'textarea' => $this->input->post('textarea'),
			];

			
			$save_form_test = $this->model_form_test->store($save_data);

			$this->data['success'] = true;
			$this->data['id'] 	   = $save_form_test;
			$this->data['message'] = cclang('your_data_has_been_successfully_submitted');
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}

	
}


/* End of file form_test.php */
/* Location: ./application/controllers/administrator/Form Test.php */