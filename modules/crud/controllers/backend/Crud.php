<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Crud Controller
*| --------------------------------------------------------------------------
*| crud site
*|
*/
class Crud extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_crud');
	}

	/**
	* show all cruds
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('crud_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['cruds'] = $this->model_crud->get($filter, $field, $this->limit_page, $offset);
		$this->data['crud_counts'] = $this->model_crud->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/crud/index/',
			'total_rows'   => $this->model_crud->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		$this->template->title('Crud List');
		$this->render('backend/standart/administrator/crud/crud_list', $this->data);
	}

	/**
	* show all cruds
	*
	*/
	public function add()
	{
		$this->is_allowed('crud_add');
		$this->template->title('Crud New');
		$this->load->helper('directory');
		$path = FCPATH . 'modules/';
		$directories = directory_map($path, 4);
		$file_exist = [];
		foreach ($directories as $name => $dir) {
			$sub_directories = directory_map($path . '/' . $name . 'controllers/backend', 3);

			foreach ((array)$sub_directories as $value) {
				$file_exist[] = strtolower(str_replace('.php', '', $value));
			}
			
		}

		$tables = array_diff($this->db->list_tables(), $file_exist);

		$tables = array_diff($tables, get_table_not_allowed_for_builder());	

		$this->data['tables'] = $tables;
		$this->render('backend/standart/administrator/crud/crud_add', $this->data);
	}

	/**
	* Add New cruds
	*
	* @return JSON
	*/
	public function add_save()
	{
		if (!$this->is_allowed('crud_add', false)) {
			return $this->response([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}

		$this->form_validation->set_rules('table_name', 'Table', 'trim|required|callback_valid_table_avaiable');
		$this->form_validation->set_rules('subject', 'Subject', 'trim|required|alpha_numeric_spaces');
		$this->form_validation->set_rules('title', 'Subject', 'trim|alpha_numeric_spaces');
		$this->form_validation->set_rules('primary_key', 'Primary Key of Table', 'trim|required');

		echo $this->save_crud();
	}

	

	/**
	* Update view cruds
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('crud_update');

		$crud_field = $this->model_crud->get_crud_field($id);
		$crud = $this->model_crud->find($id);

		$new_crud_field = $this->model_crud->get_new_field($id);

		$crud_field = array_merge($crud_field,  $new_crud_field);

		$this->data = [
			'crud' => $crud,
			'crud_field' => $crud_field,
			'crud_field_validation' => $this->model_crud->get_crud_field_validation($id),
			'crud_field_option' => $this->model_crud->get_crud_field_option($id),
		];
		$this->template->title('Crud Update');
		$this->render('backend/standart/administrator/crud/crud_update', $this->data);
	}

	/**
	* Update cruds
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('crud_update', false)) {
			return $this->response([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}

		$this->form_validation->set_rules('subject', 'Subject', 'trim|required|alpha_numeric_spaces');
		$this->form_validation->set_rules('title', 'Subject', 'trim|alpha_numeric_spaces');
		$this->form_validation->set_rules('primary_key', 'Primary Key of Table', 'trim|required');

		echo $this->save_crud();
	}

	public function save_crud()
	{
		if ($this->form_validation->run()) {
			$this->load->library('parser');
			$this->load->helper('file');
			$this->load->library('crud_builder', [
				'crud' => $_POST['crud']
				]);

			$this->data = [
				'php_open_tag' 				=> '<?php',
				'php_close_tag' 			=> '?>',
				'php_open_tag_echo' 		=> '<?=',
				'table_name'				=> $this->input->post('table_name'),
				'primary_key'				=> $this->input->post('primary_key'),
				'subject'					=> $this->input->post('subject'),
				'non_input_able_validation' => $this->crud_builder->getNonInputableValidation(),
				'input_able_validation'		=> $this->crud_builder->getInputableValidation(),
				'show_in_add_form'			=> $this->crud_builder->getFieldShowInAddForm(),
				'show_in_update_form'		=> $this->crud_builder->getFieldShowInUpdateForm(),
			];

			if ($this->input->post('title')) {
				$this->data['title'] = $this->input->post('title');
			} else {
				$this->data['title'] = $this->input->post('subject');
			}

			$table_name = $this->input->post('table_name');
			$module_folder = strtolower($table_name);

			$view_path = FCPATH . '/modules/'.$module_folder.'/views/backend/standart/administrator/'.$table_name.'/';
			$controller_path = FCPATH . '/modules/'.$module_folder.'/controllers/backend/';
			$model_path = FCPATH . '/modules/'.$module_folder.'/models/';

			$dir_modules[] = 'modules/'.$module_folder;
            $dir_modules[] = 'modules/'.$module_folder.'/models';
            $dir_modules[] = 'modules/'.$module_folder.'/views';
            $dir_modules[] = 'modules/'.$module_folder.'/controllers';
            $dir_modules[] = 'modules/'.$module_folder.'/controllers/backend';
            $dir_modules[] = 'modules/'.$module_folder.'/views/backend';
            $dir_modules[] = 'modules/'.$module_folder.'/views/backend/standart';
            $dir_modules[] = 'modules/'.$module_folder.'/views/backend/standart/administrator';

            foreach ($dir_modules as $dir) {
            	if (!is_dir($dir)) {
					mkdir($dir);
				}
            }

			if (!is_dir($view_path)) {
				mkdir($view_path);
			}

			$validate = $this->crud_builder->validateAll();

			if ($validate->isError()) {
				return $this->response([
					'success' => false,
					'message' => $validate->getErrorMessage()
					]);
				exit;
			}

			$template_crud_path = 'core_template/crud/';

			$builder_list = $this->parser->parse($template_crud_path.'builder_list', $this->data, true);
			write_file($view_path.$table_name.'_list.php', $builder_list);

			$builder_list = $this->parser->parse($template_crud_path.'builder_controller', $this->data, true);
			write_file($controller_path.ucwords($table_name).'.php', $builder_list);

			$builder_list = $this->parser->parse($template_crud_path.'builder_model', $this->data, true);
			write_file($model_path.'Model_'.$table_name.'.php', $builder_list);

			if ($this->input->post('create')) {
				$builder_list = $this->parser->parse($template_crud_path.'builder_add', $this->data, true);
				write_file($view_path.$table_name.'_add.php', $builder_list);
				$this->aauth->create_perm($table_name.'_add');
			}

			if ($this->input->post('update')) {
				$builder_list = $this->parser->parse($template_crud_path.'builder_update', $this->data, true);
				write_file($view_path.$table_name.'_update.php', $builder_list);
				$this->aauth->create_perm($table_name.'_update');
			}
			
			if ($this->input->post('read')) {
				$builder_list = $this->parser->parse($template_crud_path.'builder_view', $this->data, true);
				write_file($view_path.$table_name.'_view.php', $builder_list);
				$this->aauth->create_perm($table_name.'_view');
			}

			$this->aauth->create_perm($table_name.'_delete');
			$this->aauth->create_perm($table_name.'_list');

			$save_data = [
				'table_name' 		=> $this->input->post('table_name'),
				'primary_key'		=> $this->input->post('primary_key'),
				'subject' 			=> $this->input->post('subject'),
				'title' 			=> $this->input->post('title'),
				'page_read' 		=> $this->input->post('read'),
				'page_update' 		=> $this->input->post('update'),
				'page_create' 		=> $this->input->post('create'),
			];

			if ($id_crud = $this->model_crud->crud_exist($this->input->post('table_name'))) {
				$this->model_crud->change($id_crud, $save_data);
			} else {
				$id_crud = $this->model_crud->store($save_data);
			}
			$save_data_field = [];
			$this->db->delete('crud_field', ['crud_id' => $id_crud]);
			$this->db->delete('crud_field_validation', ['crud_id' => $id_crud]);
			$this->db->delete('crud_custom_option', ['crud_id' => $id_crud]);

			foreach ($this->input->post('crud') as $val) {
				$field_name = array_keys($val)[0];
				$field_label = isset($val[$field_name]['label']) ? $val[$field_name]['label'] : '';
				$input_type = isset($val[$field_name]['input_type']) ? $val[$field_name]['input_type'] : '';
				$show_in_column = isset($val[$field_name]['show_in_column']) ? $val[$field_name]['show_in_column'] : '';
				$show_in_add_form = isset($val[$field_name]['show_in_add_form']) ? $val[$field_name]['show_in_add_form'] : '';
				$show_in_add_form = isset($val[$field_name]['show_in_add_form']) ? $val[$field_name]['show_in_add_form'] : '';
				$show_in_update_form = isset($val[$field_name]['show_in_update_form']) ? $val[$field_name]['show_in_update_form'] : '';
				$show_in_detail_page = isset($val[$field_name]['show_in_detail_page']) ? $val[$field_name]['show_in_detail_page'] : '';
				$relation_table = isset($val[$field_name]['relation_table']) ? $val[$field_name]['relation_table'] : '';
				$relation_value = isset($val[$field_name]['relation_value']) ? $val[$field_name]['relation_value'] : '';
				$relation_label = isset($val[$field_name]['relation_label']) ? $val[$field_name]['relation_label'] : '';
				$sort = isset($val[$field_name]['sort']) ? $val[$field_name]['sort'] : '';

				$save_data_field = [
					'crud_id' 				=> $id_crud,
					'field_name' 			=> $field_name,
					'field_label' 			=> $field_label,
					'input_type' 			=> $input_type,
					'show_column' 			=> $show_in_column,
					'show_add_form' 		=> $show_in_add_form,
					'show_update_form' 		=> $show_in_update_form,
					'show_detail_page' 		=> $show_in_detail_page,
					'sort' 					=> $sort,
					'relation_table' 		=> $relation_table,
					'relation_value' 		=> $relation_value,
					'relation_label' 		=> $relation_label,
				];

				$this->db->insert('crud_field', $save_data_field);

				$crud_field_id = $this->db->insert_id();

				$save_data_rule = [];

				if (isset($val[$field_name]['validation']['rules'])) {
					foreach ($val[$field_name]['validation']['rules'] as $rule => $value) {
						$save_data_rule[] = [
							'crud_field_id' 	=> $crud_field_id, 
							'crud_id' 			=> $id_crud,
							'validation_name' 	=> $rule, 
							'validation_value'	=> $value
						];
					}
				}

				$save_data_option = [];

				if (isset($val[$field_name]['custom_option'])) {
					foreach ($val[$field_name]['custom_option'] as $option) {
						if (!empty($option['value']) or !empty($option['label'])) {
							$save_data_option[] = [
								'crud_field_id' 	=> $crud_field_id, 
								'crud_id' 			=> $id_crud,
								'option_value' 		=> $option['value'], 
								'option_label'		=> $option['label']
							];
						}
					}
				}

				if (count($save_data_rule)) {
					$this->db->insert_batch('crud_field_validation', $save_data_rule);
				}
				if (count($save_data_option)) {
					$this->db->insert_batch('crud_custom_option', $save_data_option);
				}
			}

			if ($this->input->post('save_type') == 'stay') {
				$this->response['success'] = true;
				$this->response['message'] = cclang('success_save_data_stay', [
					anchor('administrator/crud', ' Go back to list'),
					anchor('administrator/'.$this->input->post('table_name'), ' View')
				]);
			} else {
				set_message(
					cclang('success_save_data_redirect', [
					anchor('administrator/'.$this->input->post('table_name'), ' View')
				]), 'success');
        		$this->response['success'] = true;
				$this->response['redirect'] = site_url('administrator/crud');
			}
		} else {
			$this->response['success'] = false;
			$this->response['message'] = validation_errors();
		}

		$this->response($this->response);
	}

	/**
	* delete cruds
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('crud_delete');

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
            set_message(cclang('has_been_deleted', 'Crud'), 'success');
        } else {
            set_message(cclang('error_delete', 'Crud'), 'error');
        }

		redirect('administrator/crud');
	}

	/**
	* delete cruds
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$crud = $this->model_crud->find($id);

		if ($crud->table_name) {
			$module_folder = strtolower($crud->table_name);

			$module_path = FCPATH . '/modules/'.$module_folder;
			if (is_dir_empty($module_path.'/controllers/api/')) {
				if (is_dir($module_path)) {
					delete_files($module_path, true);
					rmdir($module_path);
				}
			} else {
				$controller_backend_path = $module_path . '/controllers/backend/';
				$views_path = $module_path . '/views/';
				$model_path = $module_path . '/models/Model_'.$crud->table_name.'.php';
				$del_files = [
					$controller_backend_path,
					$views_path,
					$model_path
				];

				foreach ($del_files as $path) {
					if (is_dir($path)) {
						delete_files($path, true);
						rmdir($path);
					} else {
						delete_files($path);
						@unlink($path);
					}
				}
			}

			$table_name = $crud->table_name;

			$this->db->where_in('name', [
				$table_name.'_list', 
				$table_name.'_add', 
				$table_name.'_update', 
				$table_name.'_view', 
				$table_name.'_delete']
			);
			$this->db->delete('aauth_perms');
		}

		return $this->model_crud->remove($id);
	}

	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('crud_export');

		$this->model_crud->export('crud', 'crud');
	}

	/**
	* Get field data
	*
	* @return html
	*/
	public function get_field_data($table)
	{
		if (in_array($table, get_table_not_allowed_for_builder())) {
			return $this->response([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->data['html'] = $this->load->view('backend/standart/administrator/crud/crud_field_data.php', ['table' => $table], true);
		$this->data['subject'] = ucwords(clean_snake_case($table));
		$this->data['success'] = true;

		return $this->response($this->data);
	}

	/**
	* Get field table
	*
	* @return html
	*/
	public function get_list_field_id($table)
	{
		$this->data['html'] = $this->load->view('backend/standart/administrator/crud/crud_list_field.php', ['table' => $table], true);
		$this->data['success'] = true;

		return $this->response($this->data);
	}

	/**
	* Get field table
	*
	* @return html
	*/
	public function get_list_field_label($table)
	{
		$this->data['html'] = $this->load->view('backend/standart/administrator/crud/crud_list_field_label.php', ['table' => $table], true);
		$this->data['success'] = true;

		return $this->response($this->data);
	}
}

/* End of file Crud.php */
/* Location: ./application/controllers/administrator/Crud.php */