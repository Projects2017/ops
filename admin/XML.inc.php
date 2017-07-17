<?php
/*
* XML.inc.php
*
* Class to convert an XML file into an object
*
* Copyright (C) 2006  Oliver Strecke <oliver.strecke@browsertec.de>
*
*   This library is free software; you can redistribute it and/or
*   modify it under the terms of the GNU Lesser General Public
*   License as published by the Free Software Foundation; either
*   version 2 of the License, or (at your option) any later version.
*
*   This library is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
*   Lesser General Public License for more details.
*
*   You should have received a copy of the GNU Lesser General Public
*   License along with this library; if not, write to the Free Software
*   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
*/ 

class XML{
	var $_parser;
	var $_xml_data;
	var $_actual_tag;
	/* this set of vars was added 12/6/06 to cope with entities
	var $tag_pos = -1;
	var $curr_tag;
	var $ent_c = 0;
	var $prev_ent_c = 0;
	var $repeat;
	end additional vars */
	
	
	//Constructor...
	function xml(){
        $this->_parser=xml_parser_create("ISO-8859-1");
        $this->_xml_data="";
        $this->_actual_tag=$this;
		/* Added from another XML class to cope with entities
		$this->stack = array();
		*/
        xml_set_object($this->_parser,$this);
        xml_parser_set_option($this->_parser,XML_OPTION_CASE_FOLDING,false);
        xml_set_element_handler($this->_parser,"tag_open","tag_close");
        xml_set_character_data_handler($this->_parser,"tag_data");
        xml_set_default_handler($this->_parser,"tag_data");
	}
	
	//get XML data from file...
	function file_read($xml_file){
		if(file_exists($xml_file)){
			$this->_xml_data=implode("",file($xml_file));
		}
		return 1;
	}
	
	//parse XML data...
	function parse($xml_data=0){
		if($xml_data)$this->_xml_data=$xml_data;
		xml_parse($this->_parser,$this->_xml_data);
	    xml_parser_free($this->_parser);
	    return 1;
	}

    function tag_open($parser,$name,$attrs){
		/* this set of statements added 12/6/06 to cope with entities
		$this->ent_c++;
		$this->tag_pos++;
		$this->stack[] = $name;
		$this->curr_tag = $this->stack[$this->tag_pos];
			 end addition */
		
		
    	//create new tag...
    	$tag=new XML_TAG(&$this->_actual_tag);
    	$tag->_name=$name;
    	$tag->_param=$attrs;

    	//add tag object to parent/actual tag object...
    	if(is_a($this->_actual_tag,"XML_TAG")){
    		if(is_object($this->_actual_tag->$name) || is_array($this->_actual_tag->$name)){
    			//same child objects -> Array...
    			$last_index=$this->_actual_tag->new_child_array($tag,$name);
    			$this->_actual_tag=&$this->_actual_tag->{$name}[$last_index];
    		}else{
    			//add new child object to actual tag...
    			$this->_actual_tag->new_child($tag,$name);
    		    $this->_actual_tag=&$this->_actual_tag->$name;
    		}
    	}else{
    		//copy first tag object in this object...
    		$this->$name=$tag;
    		$this->_actual_tag=&$this->{$name};
    	}
    	return 1;
    }

    function tag_data($parser,$string){
    	if(strlen(trim($string))>0)$this->_actual_tag->_value .= $string;
        return 1;
    }

    function tag_close($parser,$name){
        $this->_actual_tag=&$this->_actual_tag->_parent;
        return 1;
    }
    
	//Debug...
	function debug($exit=0){
		echo "<pre>";
		print_r($this);
		echo "</pre>";
		if($exit)exit;
	}
}

class XML_TAG{
	var $_parent;
	var $_name;
	var $_value;
	var $_param;
	
	//Constructor...
	function xml_tag($parent){
        $this->_parent=&$parent;
		$this->_name="";
		$this->_value=false;
		$this->_param=Array();
		return 1;
	}
	
	//simply add ne child to this object...
	function new_child($child,$child_name){
  		$this->$child_name=&$child;
	}
	
	//add child array for more same childs to this object...
	function new_child_array($child,$child_name){
		//create array and set old child object to the first array element...
		if(is_object($this->$child_name)){
			$tmp_obj=$this->$child_name;
			$this->$child_name=Array();
			$this->new_child_array($tmp_obj,$child_name);
		}
		//push child reference into child array...
		$this->{$child_name}[]=&$child;
		$last_index=count($this->$child_name)-1;
		return $last_index;
	}
	
	//Debug...
	function debug(){
	  echo "<pre>";
	  print_r($this);
	  echo "</pre>";
	}
}
?>