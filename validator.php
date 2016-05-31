<?php
/**
 * Validator class
 *
 * Validate input data against meta information and fields list configured.
 *
 * @author Vlado Velkov <vlado.velkov@gmail.com>
 */
class Validator {

	/**
	* Array for data fields and their values. 
	*/
	protected $data;
	
	/**
	* Array with the meta information about every data field - validation rule, message and PDO bind type. 
	*/
	protected $meta;
	
	/**
	* Array with the list of fields to be validated. 
	*/
	protected $fields;
	
	/**
     * Instantiate the validator object.
     *
     * @param array $field list of fields to be validated in data.
	 * @param array $data data to work with
	 * @param array $meta meta information about data
     */
	public function __construct($data,$meta,$fields) {
		$this->data = $data;
		$this->meta = $meta;
		$this->fields = $fields;
	}	
	
	/**
     * For each field, match the appearance/format of field value in data using regular expression defined in meta.
     *
     * @return array $errors contain invalid fields and appropriate validation messages
     */
	public function validate() {
		$errors = [];
		foreach($this->fields as $f) {
			if($this->meta[$f][0]!='') {
				if(!preg_match('((*UTF8)'.$this->meta[$f][0].')',$this->data[$f])) {
					$errors[$f] = $this->meta[$f][1];
				}
			}
		}
		return $errors;
	}

}
?>