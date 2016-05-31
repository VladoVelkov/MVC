<?php
/**
 * Cache class
 *
 * Store data in cache files in JSON format.
 *
 * @author Vlado Velkov <vlado.velkov@gmail.com>
 */
class Cache {

	/**
	* Storage path + module prefix for file used to set/get information 
	*/
	protected $prefix;
	
	/**
	* Encoded name of file used to set/get information 
	*/
	protected $entry;
	
	/**
     	* Instantiate the cache object.
     	*
     	* @param string $storage path to the system cache storage.
	* @param array $params contains module, action, and other query string parameters
	*/
	public function __construct($storage,$params) {
		$this->prefix = $storage.'/'.$params['module'];
		$this->entry = md5(http_build_query($params));
	}	
	
	/**
     	* Get content from cache file or empty string if not exists.
     	*
     	* @return string 
     	*/
	public function get() {
		if(is_file($this->prefix.'_'.$this->entry)) {
			return file_get_contents($this->prefix.'_'.$this->entry);
		}
		return '';
	}
	
	/**
     	* Save content to cache file.
     	*
     	* @return mixed number of characters saved or false if not successful 
     	*/
	public function set($value) {
		return file_put_contents($this->prefix.'_'.$this->entry,$value);
	}
	
	/**
     	* Delete cache files with the same prefix
     	*/
	public function invalidate() {
		foreach(glob($this->prefix.'*') as $file) {
			unlink($file);
		}
	}

}
?>
