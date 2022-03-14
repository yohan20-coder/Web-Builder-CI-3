<?php

/**
 * CC module Class
 *
 * @package			Cicool  
 * @category		Components
 */

class Cc_Module
{
	protected $item;
	private $ignoreParent = ['modules'];

	public function getModules()
	{
		$modules = [];
		$paths = Modules::$locations;

		$ignore = [];

		foreach ($paths as $path => $loc) {
			$ignore[] = basename($path);
		}

		foreach ($paths as $path => $loc) {
			$dir = directory_map($path, 1);
			foreach ($dir as $mod) {
				$mod =  str_replace(DIRECTORY_SEPARATOR, '', $mod);

				if (!in_array($mod, $ignore)) {
					$modInstance =  new Cc_Module_item($mod); 
					$modInstance->path = $path.DIRECTORY_SEPARATOR.$mod.DIRECTORY_SEPARATOR;
					foreach ($paths as $p => $l) {
						if (strpos($modInstance->path, $p) !== false) {
							$parent = basename($p);
							if (!in_array($parent, $this->ignoreParent)) {
								$modInstance->parent = $parent;
							}
						}
					}
					if (is_dir($modInstance->path)) {
						$modules[] = $modInstance;
					}
				}
			}
		}

		return $modules;
	}
}

class Cc_Module_item
{
	public $item;
	public $ci;

	public function __construct($item)
	{
		$this->item = $item;
		return $this;
	}

	public function actived()
	{
		if (file_exists($this->path . 'actived')) {
			return true;
		}

		return false;
	}

	public function getModName()
	{
		return str_replace(DIRECTORY_SEPARATOR, '', $this->item);
	}

	public function getExtName()
	{
		return $this->item;
	}

	public function getInformationFoot()
	{
		cicool()->eventListen('module_info_'.$this->getExtName());
	}
}
