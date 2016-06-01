<?php
class ProductsModel extends Model {
	
	public function __construct($params,$data,$meta) {
		
		parent::__construct($params,$data,$meta);
		
		$this->belongs[0] = [
			'categories'=>['products_categories_id=categories_id','categories_name']
		];
		
		$this->filters = [
			'id' => 'products_id = :id',
			'cid' => 'products_categories_id = :cid'
		];
		
		$this->types = [
			'id' => PDO::PARAM_INT,
			'cid' => PDO::PARAM_INT
		];	
	}
	
}
?>
