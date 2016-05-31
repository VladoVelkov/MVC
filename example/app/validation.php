<?php
return [
	'categories'=> [
		'insert'	=> ['categories_name'],
		'update'	=> ['categories_name']
	],
	'products'=> [
		'insert'	=> ['products_categories_id','products_name','products_description'],
		'update'	=> ['products_categories_id','products_name','products_description']
	],
	'prices'=> [
		'insert'	=> ['prices_products_id','prices_quantity','prices_amount'],
		'update'	=> ['prices_products_id','prices_quantity','prices_amount']
	],
	
];
?>