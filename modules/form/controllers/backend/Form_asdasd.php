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
	* show all Form Asdasds
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('form_asdasd_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['form_asdasds'] = $this->model_form_asdasd->get($filter, $field, $this->limit_page, $offset);
		$this->data['form_asdasd_counts'] = $this->model_form_asdasd->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/form_asdasd/index/',
			'total_rows'   => $this->model_form_asdasd->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		$this->template->title('Asdasd List');
		$this->render('backend/standart/administrator/form_builder/form_asdasd/form_asdasd_list', $this->data);
	}

	/**
	* Update view Form Asdasds
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('form_asdasd_update');

		$this->data['form_asdasd'] = $this->model_form_asdasd->find($id);

		$this->template->title('Asdasd Update');
		$this->render('backend/standart/administrator/form_builder/form_asdasd/form_asdasd_update', $this->data);
	}

	/**
	* Update Form Asdasds
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('form_asdasd_update', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->form_validation->set_rules('input', 'Input', 'trim|required');
		$this->form_validation->set_rules('select', 'Select', 'trim|required');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
				'input' => $this->input->post('input'),
				'select' => $this->input->post('select'),
			];

			
			$save_form_asdasd = $this->model_form_asdasd->change($id, $save_data);

			if ($save_form_asdasd) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $id;
					$this->data['message'] = cclang('success_update_data_stay', [
						anchor('administrator/form_asdasd', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_update_data_redirect', [
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/form_asdasd');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
					set_message('Your data not change.', 'error');
					
            		$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/form_asdasd');
				}
			}
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}

	/**
	* delete Form Asdasds
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('form_asdasd_delete');

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
            set_message(cclang('has_been_deleted', 'Form Asdasd'), 'success');
        } else {
            set_message(cclang('error_delete', 'Form Asdasd'), 'error');
        }

		redirect_back();
	}

	/**
	* View view Form Asdasds
	*
	* @var $id String
	*/
	public function view($id)
	{
		$this->is_allowed('form_asdasd_view');

		$this->data['form_asdasd'] = $this->model_form_asdasd->find($id);

		$this->template->title('Asdasd Detail');
		$this->render('backend/standart/administrator/form_builder/form_asdasd/form_asdasd_view', $this->data);
	}

	/**
	* delete Form Asdasds
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$form_asdasd = $this->model_form_asdasd->find($id);

		
		return $this->model_form_asdasd->remove($id);
	}
	
	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('form_asdasd_export');

		$this->model_form_asdasd->export('form_asdasd', 'form_asdasd');
	}
}


/* End of file form_asdasd.php */
/* Location: ./application/controllers/administrator/Form Asdasd.php */