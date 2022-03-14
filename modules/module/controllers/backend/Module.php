<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Module Controller
*| --------------------------------------------------------------------------
*| Module site
*|
*/
class Module extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('cc_module');
	}

	/**
	* show all Modules
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		
		$this->is_allowed('module_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');
		$modules = $this->cc_module->getModules();
		$this->data['modules'] = $modules;
		$this->template->title('Module List');

		$this->render('backend/standart/administrator/module/module_list', $this->data);
	}
	
	public function add()
	{
		$this->is_allowed('module_add');

		$modules = $this->cc_module->getModules();
		$this->data['modules'] = $this->_external_module();

		$this->template->title('Module List');

		
		$this->render('backend/standart/administrator/module/module_add', $this->data);
	}
	

	/**
	* delete Modules
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('module_delete');

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
            set_message(cclang('has_been_deleted', 'module'), 'success');
        } else {
            set_message(cclang('error_delete', 'module'), 'error');
        }

		redirect_back();
	}

	
	/**
	* delete Modules
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$module = $this->model_module->find($id);
		
		return $this->model_module->remove($id);
	}

	public function activation()
	{
		$this->is_allowed('module_activate');
		$this->load->helper('file');
		$module = $this->input->get('mod');
		$decode_path = (base64_decode($module));
		$ext_path = $decode_path. '/actived' ;
		file_put_contents($ext_path, '');
        set_message(cclang('has_been_actived', 'module'), 'success');
		redirect_back();
	}

	public function deactivation()
	{
		$this->is_allowed('module_deactivate');
		$this->load->helper('file');
		$module = $this->input->get('mod');
		$decode_path = (base64_decode($module));
		$ext_path = $decode_path. '/actived' ;
		@unlink($ext_path);
        set_message(cclang('has_been_inactived', 'module'), 'success');
		redirect_back();
	}

	public function _external_module()
	{
	    $moduleListUrl = 'https://raw.githubusercontent.com/cicoolbuilder/cc-module/master/extensions.json'.'?'.mt_rand();
		$this->load->library('Ccurl/Curl');


		$result = file_get_contents($moduleListUrl);
		$modules = [];

		if ($result) {
			$modules = json_decode($result);
		}

		return $modules;
	}

	public function list_external_module()
	{
		$this->response($this->_external_module());
	}

	public function install()
	{
		$this->_install();
	}

	public function update()
	{
		$this->_install();
	}

	public function _install()
	{
		if (!$this->is_allowed('module_install', false)) {
            return $this->response([
                'success' => false,
                'message' => cclang('sorry_you_do_not_have_permission_to_access'),
            ]);
        }

		$this->load->helper('file');
		$zip = new ZipArchive;
		$ex = $this->input->get('ex');
        list($cicool, $regid) = explode('/', $ex);

		$path = 'https://github.com/'.$ex.'/archive/master.zip';
		$destination = APPPATH. 'cache/'.$regid.'.zip';

		file_put_contents($destination, fopen($path, 'r'));

		if ($zip->open($destination) === TRUE) {
		    $zip->extractTo(APPPATH.'/cache/');
		    recurse_copy(APPPATH.'/cache/'.$regid.'-master', 'cc-content/modules/'.$regid);
		    $zip->close();
		    sleep(1);
		    @unlink($destination);
		    delete_files(APPPATH.'/cache/'.$regid.'-master', true);
		    @rmdir(APPPATH.'/cache/'.$regid.'-master');
		    $this->response([
                'success' => true,
                'message' => 'Module installed',
            ]);
		} else {
			$this->response([
                'success' => true,
                'message' => 'Module can\'t installed',
            ]);
		}
	}
}


/* End of file module.php */
/* Location: ./application/controllers/administrator/Module.php */