<?php
/**
 * Controller class
 *
 * Use model object to select/insert/update/delete data in database.
 * Use cache object to select/invalidate cached data before/after using model
 * Prepare response for user.
 *
 * @author Vlado Velkov <vlado.velkov@gmail.com>
 */
class Controller {

	/**
	* Object of class Model to manage data in database. 
	*/
	protected $model;

	/**
	* Object of class Cache to manage data in cache files. 
	*/
	protected $cache;	
	
	/**
     * By default the controller works with model and database. 
     * If some controller child class doesn't need model then override to return false.
     * @return bool
     */
	public function hasModel() {
		return true;	
	}
	
	/**
     * By default the controller works with cache files. 
     * If some controller child class doesn't need cache then override to return false.
     * @return bool
     */
	public function hasCache() {
		return true;	
	}
	
	/**
     * Set Model object property when needed. 
	 *
	 * @param Model $model object property 
     */
	public function setModel($model) {
		$this->model = $model;	
	}
	
	/**
     * Set Cache object property when needed. 
	 *
	 * @param Cache $cache object property 
     */
	public function setCache($cache) {
		$this->cache = $cache;	
	}
	
	/**
     * If content is saved in cache, then return it. 
	 * If not then call function to count and select records from database
	 *
	 * @return array with data, parameters and errors 
     */
	public function index() {
		$content = json_decode($this->cache->get(),true);
		if($content!='') {
			return $content;
		}
		$this->model->count();
		return $this->select();
	}

	/**
     * If content is saved in cache, then return it. 
	 * If not then call function to select single record from database
	 *
	 * @return array with data, parameters and errors 
     */
	public function edit() {
		$content = json_decode($this->cache->get(),true);
		if($content!='') {
			return $content;
		}
		return $this->select();
	}

	/**
     * Insert record in database and invalidate related cache items. 
	 *
	 * @return array with data, parameters and errors 
     */
	public function insert() {
		$newid = $this->model->insert();
		$this->cache->invalidate();
		return $this->response($newid,0);
	}
	
	/**
     * Update record in database and invalidate related cache items. 
	 *
	 * @return array with data, parameters and errors 
     */
	public function update() {
		$affected = $this->model->update();
		$this->cache->invalidate();
		return $this->response(0,$affected);
	}
	
	/**
     * Delete record in database and invalidate related cache items. 
	 *
	 * @return array with data, parameters and errors 
     */
	public function delete() {
		$affected = $this->model->delete();
		$this->cache->invalidate();
		return $this->response(0,$affected);
	}
	
	/**
     * Call model to select data from database. If no errors put data in cache file. 
	 *
	 * @return $output array with model data, parameters and errors
     */
	private function select() {
		$this->model->select();
		$output = $this->response();
		if(!$this->hasErrors()) {
			$this->cache->set(json_encode($output,JSON_UNESCAPED_UNICODE));
		}	
		return $output;
	}
	
	/**
     * Check for errors after model being asked to manage data in database. 
	 *
	 * @return bool
     */
	private function hasErrors() {
		$errors = $this->model->getErrors();
		return count($errors)>0;
	}
	
	/**
	 * @return $output array with model data, parameters and errors
     */
	private function response($newid = 0,$affected = 0) {
		if($this->hasErrors()) {
			$report['status'] = 'ERROR';
			$report['errors'] = $this->model->getErrors();
		} else {
			$report['status'] = 'OK';
		}
		if($newid > 0) { 
			$report['newid'] = $newid;
		}
		if($affected > 0) { 
			$report['affected'] = $affected;
		}
		$output = [
			'params' => $this->model->getParams(),
			'data' 	=> $this->model->getData(),
			'report' => $report
		];
		return $output;
	}
}
?>