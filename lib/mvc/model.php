<?php
/**
 * Model class
 *
 * Store relational data to be selected/saved from/to database in array. No ORM mapping. 
 * Build SQL queries using data, meta, parameters, database tables relations and filters definitions
 * Execute queries in database
 * Save errors for later use.
 *
 * @author Vlado Velkov <vlado.velkov@gmail.com>
 */
class Model {

	/**
	* Array for data fields and their values. 
	*/
	protected $data; 
	
	/**
	* Array with the meta information about every data field - validation rule, message and PDO bind type. 
	*/
	protected $meta; 
	
	/* 
	* Default parameters - records counter, records per page, number of pages, current page, sort column index, sort order ASC/DESC 
	*/
	protected $params = ['cnt'=>'0','recs'=>'50','pgs'=>'1','pg'=>'1','sc'=>'0','so'=>'d']; 
	
	/* 
	* PDO bind types for mapping filters parameters 
	*/
	protected $types = ['id'=>PDO::PARAM_INT]; 
	
	/* 
	* Array map parameter-condition for SQL query filters 
	*/
	protected $filters = [];
	
	/* 
	* PDO object database connection 
	*/
	protected $database;
	
	/* 
	* Database table related to model
	*/
	protected $table;
	
	/* 
	* Database table identifier
	*/
	protected $id;
	
	/* 
	* Related child tables for model database table, defined with table name, join condition and table identifier 
	*/
	protected $hasmany = [];
	
	/* 
	* Related parent tables for model database table, defined with table name, join conditions and table fields to be selected 
	*/
	protected $belongs = [];
	
	/* 
	* Keeps errors that appear during the operations with database
	*/	
	protected $errors = [];

	/**
     * Instantiate the model object.
     *
     * @param array $params parameters used.
	 * @param array $data data to work with
	 * @param array $meta meta information about data
	 * 
	 * default table is named the same with the current module requested
	 * default name for table identifier field is format table_id
	 * default filter is by table_id 
     */
	public function __construct($params,$data,$meta) {
		$this->setParams($params);
		$this->setData($data);
		$this->setMeta($meta);
		$this->table = $params['module'];
		$this->id = $params['module'].'_id';
		$this->setFilters(['id'=>$this->id.'=:id']);
	}
	
	/**
     * By default the model works with database. 
     * If some model child class doesn't need database connection then override to return false.
     * @return bool
     */
	public function hasDatabase() {
		return true;	
	}
	
	/**
     * Set PDO database connection. 
	 *
     * @param PDO $database connection to database.
     */
	public function setDatabase($database) {
		$this->database = $database;
	}
	
	/**
     * Set meta array values. 
	 *
	 * @param array $src source array with meta information.
     */
	public function setMeta($src) {
		if(count($src)>0) {
			foreach($src as $k=>$v) {
				$this->meta[$k] = $v;
			}
		}	
	}
	
	/**
     * Get meta array values. 
	 *
	 * @return array model data.
     */
	public function getData() {
		return $this->data;
	}
	
	/**
     * Set data array values. 
	 *
	 * @param array $src source array with data to be appended to model data array.
     */
	public function setData($src) {
		if(count($src)>0) {
			$cnt = count($this->data);
			foreach($src as $k=>$v) {
				$this->data[$cnt][$k] = $v;
			}
		}	
	}
	
	/**
     * Get parameter by name. 
	 *
	 * @return mixed parameter value or empty string if parameter not set.
     */
	public function getParam($p) {
		return isset($this->params[$p]) ? $this->params[$p] : '';
	}
	
	/**
     * Get all parameters. 
	 *
	 * @return array model parameters.
     */
	public function getParams() {
		return $this->params;
	}
	
	/**
     * Set parameters array values. 
	 *
	 * @param array $src source array with parameters to be appended to model parameters array.
     */
	public function setParams($src) {
		if(count($src)>0) {
			foreach($src as $k=>$v) {
				$this->params[$k] = $v;
			}
		}	
	}

	/**
     * Set filters array keys and values. 
	 *
	 * @param array $src source array with filters to be appended to model filters array.
     */
	public function setFilters($src) {
		if(count($src)>0) {
			foreach($src as $k=>$v) {
				$this->filters[$k] = $v;
			}
		}	
	}
	
	/**
     * Set database tables the model database table belongs to. 
	 *
	 * @param number $i index of belongs array (there can be multiple parents)
	 * @param array $src source array with belongs information to be appended to model belongs array.
     */
	public function setBelongs($i,$src) {
		if(count($src)>0) {
			foreach($src as $k=>$v) {
				$this->belongs[$i][$k] = $v;
			}
		}	
	}
	
	/**
     * Set database tables the model database table is parent to. 
	 *
	 * @param array $src source array with hasmany information to be appended to model hasmany array.
     */
	public function setHasMany($src) {
		if(count($src)>0) {
			foreach($src as $k=>$v) {
				$this->hasmany[$k] = $v;
			}
		}	
	}
	
	/**
     * Get all errors. 
	 *
	 * @return array model errors.
     */
	public function getErrors() {
		return $this->errors;
	}
	
	/**
     * Prepare statement for every SQL execution in database.
     * In case model database property was set to PDOException then set model errors.
	 * If database connection set then bind values to filters named parameters for select/update/delete query conditions 
	 * or bind values to data named parameters for insert/update queries.
	 * Save debug and info messages in model errors array for later use.
	 *
     * @param string $sql SQL query to be executed with named params values to be binded to.
	 * 	
	 * @return PDOStatement prepared statement to be executed
     */
	public function prepare($sql) {
		$stmt = null;
		if($this->database instanceof PDO) {
			try {
				$stmt = $this->database->prepare($sql);
				foreach ($this->params as $k=>$v) {
					if(isset($this->filters[$k])) {
						$stmt->bindValue(':'.$k,$v,$this->types[$k]);
					}
				}
				if(count($this->data)>0) {
					foreach($this->data[0] as $k=>$v) {
						$stmt->bindValue(':'.$k,$v,$this->meta[$k][2]);
					}
				}	
			} catch(PDOException $e) {
				$this->errors['debug'][$e->getCode()] = $e->getMessage();
				$this->errors['info'][$e->getCode()] = 'STATUS_NOT_PREPARED';
			}	
		} if($this->database instanceof PDOException) {
			$this->errors['debug'][$this->database->getCode()] = $this->database->getMessage();
			$this->errors['info'][$this->database->getCode()] = 'STATUS_NOT_CONNECTED';
		}
		return $stmt;
	}
	
	/**
     * Count records in database that meets query conditions.
     * Set model parameters for number of records, current page and number of pages.
	 * Save debug and info messages in model errors array for later use.
     */
	public function count() {
		$stmt = $this->prepare($this->getCountSQL());
		if(isset($stmt)) {
			try {
				$stmt->execute();
				$numrows = $stmt->fetchColumn();
				$numpages = ceil($numrows/(float)$this->params['recs']);
				if($numpages==0) { $numpages=1; }
				$this->setParams(['cnt'=>$numrows,'pgs'=>$numpages]);
				if(!(in_array($this->params['pg'],range(1,$this->params['pgs'])))) {
					$this->params['pg'] = 1;
				}
			} catch(PDOException $e) {
				$this->errors['debug'][$e->getCode()] = $e->getMessage();
				$this->errors['info'][$e->getCode()] = 'STATUS_NOT_SELECTED';
			}
		}	
	}
	
	/**
     * Select records from database that meets query conditions.
     * Load records in model data array.
	 * Save debug and info messages in model errors array for later use.
     */
	public function select() {
		$stmt = $this->prepare($this->getSelectSQL());
		if(isset($stmt)) {
			try {
				$stmt->execute();
				$records = $stmt->fetchAll();
				if($records) $this->data = [];
				for($i=0,$cntr=count($records);$i<$cntr;$i++) {
					$this->setData($records[$i]);
				}
			} catch(PDOException $e) {
				$this->errors['debug'][$e->getCode()] = $e->getMessage();
				$this->errors['info'][$e->getCode()] = 'STATUS_NOT_SELECTED';
			}
		}	
	}

	/**
     * Insert record in database.
	 * Save debug and info messages in model errors array for later use.
	 *
     * @return number last inserted id or 0 if query execution failed.
     */
	public function insert() {
		$stmt = $this->prepare($this->getInsertSQL());
		if(isset($stmt)) {
			try {
				if($stmt->execute()) {
					return $this->database->lastInsertId();
				}
			} catch(PDOException $e) {
				$this->errors['debug'][$e->getCode()] = $e->getMessage();
				$this->errors['info'][$e->getCode()] = 'STATUS_NOT_INSERTED';
			}	 
		}	
		return 0;
	}
	
	/**
     * Update record in database.
	 * Save debug and info messages in model errors array for later use.
	 *
     * @return number rows affected or 0 if query execution failed.
     */
	public function update() {
		$stmt = $this->prepare($this->getUpdateSQL());
		if(isset($stmt)) {
			try {
				if($stmt->execute()) {
					return $stmt->rowCount();
				}	
			} catch(PDOException $e) {
				$this->errors['debug'][$e->getCode()] = $e->getMessage();
				$this->errors['info'][$e->getCode()] = 'STATUS_NOT_UPDATED';
			}	 
		}	
		return 0;
	}
	
	/**
     * Delete record in database.
	 * Save debug and info messages in model errors array for later use.
	 *
     * @return number rows affected or 0 if query execution failed.
     */
	public function delete() {
		$stmt = $this->prepare($this->getDeleteSQL());
		if(isset($stmt)) {
			try {
				if($stmt->execute()) {
					return $stmt->rowCount();
				}	
			} catch(PDOException $e) {
				$this->errors['debug'][$e->getCode()] = $e->getMessage();
				$this->errors['info'][$e->getCode()] = 'STATUS_NOT_DELETED';
			}	 	
		}	
		return 0;
	}

	/**
     * Create SELECT COUNT query calling functions for select involved tables and defined conditions query parts.
	 * 
     * @return string $sqlcnt SQL query with named parameters.
     */
	protected function getCountSQL() {
		$sqlcnt = "SELECT COUNT(DISTINCT ".$this->id.") FROM ".$this->getSelectTables()." ".$this->getSelectConditions();
		return $sqlcnt;
	}
	
	/**
     * Create query to SELECT data from model database table and parent tables. 
	 * For each record count child tables related records number.
	 * Calling functions for select involved tables and defined conditions query parts
	 *
     * @return string $sql SQL query with named parameters.
     */
	protected function getSelectSQL() {
		$sql="SELECT ".$this->table.".*";
		for($i=0,$cntb=count($this->belongs);$i<$cntb;$i++) {
			foreach($this->belongs[$i] as $parent=>$terms) {
				$sql.=",".$terms[1];
			}
		}
		$sql.=",0";
		foreach($this->hasmany as $child=>$terms) {
			$sql.="+COUNT(".$terms[1].")";
		}		
		$sql.=" AS related FROM ".$this->getSelectTables();
		foreach($this->hasmany as $child=>$terms) {
			$sql.="LEFT JOIN $child ON ".$terms[0]." ";
		}		
		$sql.=$this->getSelectConditions()." GROUP BY ".$this->id." ".$this->getSelectParams();
		return $sql;
	}
	
	/**
     * Create query to INSERT data in model database table. 
	 * List of fields to be inserted by default are those saved in model data array.
	 *
     * @return string $sql SQL query with named parameters for data values.
     */
	protected function getInsertSQL() {
		$fields = array_keys($this->data[0]);
		$sql = "INSERT INTO ".$this->table."(".join(",",$fields).") VALUES (:".join(",:",$fields).")";
		return $sql;
	}
	
	/**
     * Create query to UPDATE data in model database table and related tables. 
	 * List of fields to be updated by default are those saved in model data array.
	 *
     * @return string $sql SQL query with named parameters for data values.
     */
	protected function getUpdateSQL() {
		$fields = array_keys($this->data[0]);
		$fieldsvalues = array_map(function($f){return $f.'=:'.$f;}, $fields);
		$sql = "UPDATE ".$this->getSelectTables()." SET ".join(",",$fieldsvalues).' '.$this->getSelectConditions();
		return $sql;
	}
	
	/**
     * Create query to DELETE data in model database table. 
	 * Calling functions for select involved tables and defined conditions query parts
	 *
     * @return string SQL query with named parameters.
     */
	protected function getDeleteSQL() {
		return "DELETE ".$this->table.".* FROM ".$this->getSelectTables()." ".$this->getSelectConditions();
	}

	/**
     * Loop through all belongs array routes to define SQL query part with all joined tables. 
	 *
     * @return string JOIN part of the SQL query .
     */
	protected function getSelectTables() {
		$alltables = [$this->table];
		$sql = $this->table." ";
		for($i=0,$cntb=count($this->belongs);$i<$cntb;$i++) {
			$previous = $this->table;
			foreach($this->belongs[$i] as $parent=>$terms) {
				if(!(in_array($parent,$alltables))) {
					$sql.="INNER JOIN $parent ON ".$terms[0]." ";
				}
				$alltables[] = $parent;
			}
		}
		return $sql;
	}
	
	/**
     * Loop through all filters array elements to define SQL query conditions. 
	 *
     * @return string WHERE part of the SQL query .
     */
	protected function getSelectConditions() {
		$cond = 'WHERE 1=1 ';
		foreach ($this->params as $k=>$v) {
			if(isset($this->filters[$k])) {
				$cond.='AND '.$this->filters[$k].' ';
			}
		}
		return $cond;
	}
	
	/**
     * Loop through all params array elements to define SQL query part with sorting and paging. 
	 *
     * @return string part of the SQL query with sorting ang paging.
     */
	protected function getSelectParams() {
		$sql_params = ' ';
		if(in_array($this->params['sc'],range(1,20))) {
			$sql_params.= " ORDER BY ".$this->params['sc']." ".(($this->params['so']=='a')?'ASC':'DESC');
		}
		if(is_numeric($this->params['pg'])) {
			$sql_params.= " LIMIT ".($this->params['pg']-1)*$this->params['recs'].",".$this->params['recs'];
		} 
		return $sql_params;
	}
}
?>