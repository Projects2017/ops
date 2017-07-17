<?php
// cellprovider.class.php
// class for storage/usage of cell provider information
class cellProvider
{
	private $provname;
	private $code;
	private $email;
	
	public function __construct($code = 'oth')
	{
		if($code=='') $code = 'oth';
		$this->code = $code;
		$this->setInfo();
	}
	
	private function setInfo()
	{
		// based on code info, get the name & email needed
		// assume DB functions have been added to this point
		$sql = "SELECT * FROM cell_providers WHERE code = '".$this->code."'";
		$que = mysql_query($sql);
		checkDBerror($sql);
		$res = mysql_fetch_assoc($que);
		$this->provname = $res['name'];
		$this->email = $res['email'];
	}
	
	public function getCode()
	{
		return $this->code;
	}
	
	public function getName()
	{
		return $this->provname;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
}