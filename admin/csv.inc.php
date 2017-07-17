<?php
// csv.inc.php
// csv class definition file

/*	Methods

	setArray(bigarray)		loads the initial array resource data using the array passed into the method
	addArray(bigarray)		loads a new array resource, removes the fieldname toggle & runs Build()
	setMySQL(resource)		loads the initial MySQL resource using the resource passed into the method
	addMySQL(resource)		loads a new MySQL resource, removes teh fieldname toggle & runs Build()
	useMySQL()			sets the class to use the MySQL resource as the data source when Build() is run
	useArray()			sets the class to use the array resource as the data source when Build() is run
	setOutputToString()		sets the output target to a string variable
	setOutputToFile(file)	sets the output target to the provided filename (file path = doc/csv/)
	setOutputToScreen()		sets the output target to the screen/page
	getOutput()			returns the output target
	includeFieldnames()		sets a bool value to include fieldnames in the resulting output
	excludeFieldnames()		sets a bool value to exclude fieldnames in the resulting output
	getFieldnames(array)	returns the fieldnames of the passed array
	
	Build methods
	buildFile(array)		render a CSV as a file; name passed in the class filename var; data must be an array of arrays
	buildScreen(array)		render CSV to the screen; data must be an array of arrays
	buildString(array)		render CSV to a variable; data must be an array of arrays; return value = string
	Build()				renders the CSV; more of a redirector than anything; returns whatever the target build method returns
	
	Variables
	
	sql					mysql result resource
	array				array resource
	output				output destination: "string" = string; "file" = file; "screen" = screen (more may be added later)
	filename				if output = "file", filename of the result
	fieldnames			bool - whether to include fieldnames in the resulting CSV
	source				sets where the data will come from: either an array or a MySQL resource
	outstring				output string for the 'string' output type
	init_fieldnames		initial fieldnames setting
	filepath				path CSV files are stored in (default = $docdir.'csv/')
	std_filename			standard filename
	
*/
class CSV
{
	var $sql; // mysql result resource
	var $array; // array resource
	var $output; // output destination: "string" = string; "file" = file; "screen" = screen
	var $filename; // if output = "file", filename of the result
	var $fieldnames; // bool - whether to include fieldnames in the resulting CSV
	var $source; // sets where the data will come from: either an array or a MySQL resource
	var $outstring; // output string for the 'string' output type
	var $init_fieldnames; // initial fieldnames setting
	var $filepath; // path CSV files are stored in
	var $std_filename; // standard filename
	
	function CSV()
	{
		global $docdir;
		$this->output = 'file'; // default output target is to a file
		$this->source = 'mysql'; // default source is a MySQL query result resource
		$this->filepath = $docdir.'csv/'; // usually /doc/csv
		$this->std_filename = "csv_output_".date('Y-m-d').".csv"; // e.g. csv_output_2007-09-30.csv
	}
	
	function setArray($bigarray) // load the initial bigarray data
	{
		if(!is_array($bigarray[0])) // if the array as sent is just a single row, add it to a dummy array so the logic doesn't break
		{
			$big_array[] = $bigarray;
		}
		else
		{
			$big_array = $bigarray;
		}
		$this->array = $big_array; // add the array to the class instance variable
	}
	
	function addArray($bigarray) // load new array data, set the fieldnames to false, and build
	{
		$this->setArray($bigarray); // loads the new array datasource
		if(!$this->reset) $this->fieldnames = false; // does not add the fieldnames
		$this->Build(); // run it
	}
	
	function setMySQL($result) // sets the MySQL resource used to $result
	{
		$this->sql = $result;
	}
	
	function addMySQL($result) // load new MySQL data, set the fieldnames to false, and build
	{
		$this->sql = $result;
		if(!$this->reset) $this->fieldnames = false;
		$this->Build();
	}
	
	function useMySQL() // the data source is MySQL
	{
		$this->source = 'mysql';
	}
	
	function useArray() // the data source is an array
	{
		$this->source = 'array';
	}
	
	function setOutputToString()  // output target is a string
	{
		$this->output = "string";
	}
	
	function setOutputToFile($file = '') // output target is a file whose name = $file
	{
		$this->output = "file";
		$this->filename = $file == '' ? $this->std_filename : $file;
	}
	
	function setOutputToScreen() // output target is the screen/page
	{
		$this->output = "screen";
	}
	
	function getOutput() // what is our output target?
	{
		return $this->output;
	}
	
	function includeFieldNames() // when running the result, add the fieldnames to the result
	{
		$this->fieldnames = true;
		$this->init_fieldnames = true;
	}
	
	function excludeFieldNames() // when running the result, don't add the fieldnames to the result
	{
		$this->fieldnames = false;
		$this->init_fieldnames = false;
	}
	
	function getFieldnames($array) // return the fieldnames of the array
	{
		$array_row1 = $array[0]; // we just need the first row to get the names, so use it only
		foreach($array_row1 as $k => $v) // getting the row names
		{
			$fields[] = $k;
		}
		return $fields;
	}
	
	function buildFile($array) // build the CSV to an output file
	{
		$file = fopen($this->filepath.$this->filename, 'a'); // open the file for appending (for later additions)
		if($this->fieldnames) // ...and if we want to use include the fieldnames
		{
			$fields = $this->getFieldnames($array);
			fputcsv($file, $fields); // write the fieldnames to the CSV
		}
		foreach($array as $array_row)
		{
			fputcsv($file, $array_row); // write the rows
		}
		fclose($file);	// close and done
	}

	function buildScreen($array) // build the CSV to the screen
	{
		if($this->fieldnames) // ...and if we want to use include the fieldnames
		{
			$fields = $this->getFieldnames($array);
			$outfields = '"'.implode('","', $fields).'"'; // must include open & close quotes because implode doesn't surround the output'd string with the separators
			echo $outfields."\r\n"; // echo the fieldnames
		}
		foreach($array as $array_row)
		{						
			$outdata = '"'.implode('","', $array_row).'"'; // see note above about implode
			echo $outdata."\r\n"; //echo the data
		}
	}
	
	function buildString($array) // build the CSV to a string
	{
		if($this->fieldnames) // ...and if we want to use include the fieldnames
		{
			$fields = $this->getFieldnames($array);
			$outfields = '"'.implode('","', $fields).'"'; // must include open & close quotes because implode doesn't surround the output'd string with the separators
			$outstring .= $outfields."\r\n"; // append the fieldnames to the string
		}
		foreach($array as $array_row)
		{						
			$outdata = '"'.implode('","', $array_row).'"'; // see note above about implode
			$outstring .= $outdata."\r\n"; // append the data
		}
		return $outstring; // return the string
	}

	function Build()
	{
		switch($this->source)
		{
			case "mysql":
				while($result = mysql_fetch_assoc($this->sql))
				{
					$results[] = $result;
				}
				return call_user_func(array($this,'build'.ucfirst($this->output)), $results);
				break;
			case "array":
				return call_user_func(array($this,'build'.ucfirst($this->output)), $this->array);
			break;
		}
	}	
}

?>