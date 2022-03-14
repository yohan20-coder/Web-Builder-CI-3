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
	* show all Form Tests
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('form_test_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['form_tests'] = $this->model_form_test->get($filter, $field, $this->limit_page, $offset);
		$this->data['form_test_counts'] = $this->model_form_test->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/form_test/index/',
			'total_rows'   => $this->model_form_test->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		$this->template->title('Test List');
		$this->render('backend/standart/administrator/form_builder/form_test/form_test_list', $this->data);
	}

	/**
	* Update view Form Tests
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('form_test_update');

		$this->data['form_test'] = $this->model_form_test->find($id);

		$this->template->title('Test Update');
		$this->render('backend/standart/administrator/form_builder/form_test/form_test_update', $this->data);
	}

	/**
	* Update Form Tests
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('form_test_update', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->form_validation->set_rules('input', 'Input', 'trim|required');
		$this->form_validation->set_rules('textarea', 'Textarea', 'trim|required');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'input' => $this->input->post('input'),
				'textarea' => $this->input->post('textarea'),
						];

			
			$save_form_test = $this->model_form_test->change($id, $save_data);

			if ($save_form_test) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $id;
					$this->data['message'] = cclang('success_update_data_stay', [
						anchor('administrator/form_test', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_update_data_redirect', [
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/form_test');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
					set_message('Your data not change.', 'error');
					
            		$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/form_test');
				}
			}
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}

	/**
	* delete Form Tests
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('form_test_delete');

		$this->load->helper('file');

		$arr_id = $this->input->get('id');
		$remove = false;

		if (!empty($id)) {
			$remove = $this->_remove($id);
		} elseif (count($arr_id) >0) {
			foreach ($arr_id as $id) {
				$remove = $this->_remove($id);
			}
		}

		if ($remove) {
            set_message(cclang('has_been_deleted', 'Form Test'), 'success');
        } else {
            set_message(cclang('error_delete', 'Form Test'), 'error');
        }

		redirect_back();
	}

	/**
	* View view Form Tests
	*
	* @var $id String
	*/
	public function view($id)
	{
		$this->is_allowed('form_test_view');

		$this->data['form_test'] = $this->model_form_test->find($id);

		$this->template->title('Test Detail');
		$this->render('backend/standart/administrator/form_builder/form_test/form_test_view', $this->data);
	}

	/**
	* delete Form Tests
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$form_test = $this->model_form_test->find($id);

		
		return $this->model_form_test->remove($id);
	}
	
	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('form_test_export');

		$this->model_form_test->export('form_test', 'form_test');
	}
}


/* End of file form_test.php */
/* Location: ./application/controllers/administrator/Form Test.php */