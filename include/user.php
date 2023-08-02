<?PHP

class User{

/*	---- VARIABLES ---- */
	var $id;
	var $acc_id;
	var $username;
	var $type;
	var $status;
	var $last_active;
	var $date_added;	

/*	---- CONSTRUCTORS ---- */
	function __construct($_id){
	  	$this->id = $_id;

	  	$sql    = "SELECT * FROM tbluser WHERE id=". $this->get_id();
		$rs    	= mysql_query($sql) or die('Error fetching user details.'); // DEBUG: . mysql_error());
		
		if(mysql_num_rows($rs) > 0){
			$rw  = mysql_fetch_assoc($rs);

			$this->set_acc_id($rw['acc_id']);
			$this->set_name(ltrim(rtrim($rw['u2'])));
			$this->set_type($rw['type']);
			$this->set_status($rw['status']);
			$this->set_date_added($rw['added']);
			$this->set_last_active($rw['last_active']);
		}
	}
	
	function __toString(){
	  	return $this->name;
	}

/*	---- ACCESSORS ---- */
	function get_id(){
		return $this->id;
	}
	function get_acc_id(){
		return $this->acc_id;
	}
	function get_name(){
		return $this->name;
	}
	function get_type(){
		return $this->type;
	}
	function get_status(){
		return $this->status;
	}
	function get_date_added(){
		return $this->date_added;
	}
	function get_last_active(){
		return $this->last_active;
	}

/*	---- MUTATORS ---- */
	function set_acc_id($new_acc_id){
		$this->acc_id = $new_acc_id;
	}
	function set_name($new_name){
		$this->name = $new_name;
	}
	function set_type($new_type){
		$this->type = $new_type;
	}
	function set_status($new_status){
		$this->status = $new_status;
	}
	function set_date_added($new_date){
		$this->date_added = $new_date;
	}
	function set_last_active($new_date){
		$this->last_active = $new_date;
	}		
}

?>