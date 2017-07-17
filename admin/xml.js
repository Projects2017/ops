// JavaScript Document

xml_docs = new Array();

function zip_file() {
	this.name = "";
	this.docs = new Array();
}
		
function checkSelect() {
	if(document.sourceForm.chooseType[1].checked) {
		document.sourceForm.upload_file.disabled=false;
	} else {
		document.sourceForm.upload_file.disabled=true;
	}
}
	
function chooseAll(zip) {
	var zipfile = document.getElementsByName('all_' + zip);
	var hit = false;
	if(zipfile[0].checked) {
		for(j=0; j < xml_docs.length; j++) {
			if(xml_docs[j].name == zip) {
				hit = true;
			}
		}
		if(!hit) {
			xml_docs.push(new zip_file());
			xml_docs[xml_docs.length - 1].name = zip;
		}
		var xmls = document.getElementById(zip)
		for (var i = 0; i < xmls.childNodes.length; i++) {
			if(xmls.childNodes[i].nodeName.toLowerCase() == 'input') {
				var an_xml = xmls.childNodes[i];
				xml_docs[xml_docs.length - 1].docs[i]=an_xml.checked;
				an_xml.checked=true;
				an_xml.disabled=true;
			}
		}
	} else {
		for(j=0; j< xml_docs.length; j++) {
			if(xml_docs[j].name == zip) {
				var read = j;
			}
		}
		var xmls = document.getElementById(zip);
		for (var i = 0; i < xmls.childNodes.length; i++) {
			if(xmls.childNodes[i].nodeName.toLowerCase() == 'input') {
				var an_xml = xmls.childNodes[i];
				an_xml.checked=xml_docs[read].docs[i];
				an_xml.disabled=false;
			}
		}
	}
}