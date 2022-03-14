<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Form Asdasd Controller
*| --------------------------------------------------------------------------
*| Form Asdasd site
*|
*/
class Form_asdasd extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_form_asdasd');
	}

	/**
	* Submit Form Asdasds
	*
	*/
	public function submit()
	{
		$this->form_validation->set_rules('input', 'Input', 'trim|required');
		$this->form_validation->set_rules('select', 'Select', 'trim|required');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'input' => $this->input->post('input'),
				'select' => $this->input->post('select'),
			];

			
			$save_form_asdasd = $this->model_form_asdasd->store($save_data);

			$this->data['success'] = true;
			$this->data['id'] 	   = $save_form_asdasd;
			$this->data['message'] = cclang('your_data_has_been_successfully_submitted');
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}

	
}


/* End of file form_asdasd.php */
/* Location: ./application/controllers/administrator/Form Asdasd.php */