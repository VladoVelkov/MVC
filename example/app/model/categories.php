<?php
class CategoriesModel extends Model {
	
	public function __construct($params,$data,$meta) {
		
		parent::__construct($params,$data,$meta);
		
		$this->hasmany = [
			'products'=>['products_categories_id=categories_id','products_id']
		];
	}
	
}
?>
