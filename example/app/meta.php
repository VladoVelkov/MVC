<?php
const REGEX_REQ			= '^.+$';

const REGEX_ALPHA_REQ		= '^[\p{L}\s\.\-\(\)\,\/]+$';
const REGEX_ALPHANUM_REQ	= '^[\p{L}0-9\s\.\+\-\(\)\,\/]+$';
	
const REGEX_DATE 		= '^(\d{1,2}(\.)\d{1,2}(\.)\d{4})$';
const REGEX_TIME 		= '^(\d{2}(\:)\d{2})$';
const REGEX_DATETIME 		= '^(\d{1,2}(\.)\d{1,2}(\.)\d{4}((\s)\d{2}(\:)\d{2})*)$';
	
const REGEX_INT			= "^([1-9][0-9]*)*$";
const REGEX_INT_REQ		= "^([1-9][0-9]*)+$";
const REGEX_DOUBLE		= '^\d+(\.\d+)?$';
	
const REGEX_EMAIL 		= '^[\w.%-]+@[\w.-]+\.[a-zA-Z]{2,4}$';
const REGEX_PHONE 		= '^[0-9\s\+\-()]+$';
const REGEX_PASSWORD		= '^.{8,40}$';
const REGEX_URL 		= '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$'; 

// format, error message code, bind parameter type
return [
	'categories_id' 	=> ['', 		'',				PDO::PARAM_INT],
	'categories_name'	=> [REGEX_ALPHA_REQ,	'VALID_CATEGORY',		PDO::PARAM_STR],
	
	'products_id' 		=> ['', 		'',				PDO::PARAM_INT],
	'products_categories_id'=> [REGEX_INT_REQ, 	'VALID_PRODUCT_CATEGORY',	PDO::PARAM_INT],
	'products_name'		=> [REGEX_ALPHANUM_REQ,	'VALID_PRODUCT',		PDO::PARAM_STR],
	'products_description'	=> [REGEX_REQ,		'VALID_PRODUCT_DESCRIPTION',	PDO::PARAM_STR],
	
	'prices_id' 		=> ['', 		'',				PDO::PARAM_INT],
	'prices_products_id'	=> [REGEX_INT_REQ, 	'VALID_PRICE_PRODUCT',		PDO::PARAM_INT],
	'prices_quantity'	=> [REGEX_ALPHANUM_REQ,	'VALID_PRICE_QUANTITY',		PDO::PARAM_INT],
	'prices_amount'		=> [REGEX_DOUBLE,	'VALID_PRICE_AMOUNT',		PDO::PARAM_STR],
];
?>
