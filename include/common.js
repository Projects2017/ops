function getElement (id) {
	/* return document.getElementById(id); */
	if ( document.getElementById ) {
		return document.getElementById(id);
	} else if (document.all) {
		return document.all[id];
	} else if (document.layers) {
		return document.layers[id];
	} else {
		alert("Incompatible browser!!");
		return false;
	}
}

function getParent(el, pTagName) {
	if (el == null) return null;
	else if (el.nodeType == 1 && el.tagName.toLowerCase() == pTagName.toLowerCase())	// Gecko bug, supposed to be uppercase
		return el;
	else
		return getParent(el.parentNode, pTagName);
}

function deleteTagContents(el) {
    try {
		var cs = new Array();
		for (var child in el.childNodes) {
			cs.push(el.childNodes[child]);
		}
		var l = el.childNodes.length;
		for (var x = 0; x < l; x++) {
			el.removeChild(cs[x]);
		}
	} catch(e) { // IE throws an error, so let's catch it and do it the 'impropper' way.
		el.innerHTML = '';
	}
}

function addEvent(elm, evType, fn, useCapture)
// addEvent and removeEvent
// cross-browser event handling for IE5+,  NS6 and Mozilla
// By Scott Andrew
{
	if (elm.addEventListener){
		elm.addEventListener(evType, fn, useCapture);
		return true;
	} else if (elm.attachEvent){
		var r = elm.attachEvent("on"+evType, fn);
		return r;
	} else {
		alert("Handler could not be removed");
	}
} 

function getHTTPObject() {
  var xmlhttp;
  /*@cc_on
  @if (@_jscript_version >= 5)
    try {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (E) {
        xmlhttp = false;
      }
    }
  @else
  xmlhttp = false;
  @end @*/
  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
    try {
      xmlhttp = new XMLHttpRequest();
    } catch (e) {
      xmlhttp = false;
    }
  }
  return xmlhttp;
}

function debugout(text) {
	 // text = String(text);
	 // document.getElementById("debugout").innerHTML += text + "<br />";
}

function findrow(tag) {
	return getParent(tag, 'tr');
	/* Old
	while (tag.tagName.toLowerCase() != "tr") {
		tag = tag.parentNode;
	}
	return tag;
	*/
}


function findform(tag) {
	return getParent(tag, 'form');
	/* Old
	while (tag.tagName.toLowerCase() != "form") {
		tag = tag.parentNode;
	}
	return tag;
	*/
}


function getIdorName(tag) {
	if (!tag) {
		return "";
	}
	if (tag.id != null) {
		return String(tag.id);
	} else {
		if (tag.name != null) {
			return String(tag.name);
		} else {
			return "";
		}
	}
}


function MakeThisLookLikeMoney(n_value) {

	// validate input
	if (isNaN(Number(n_value)))
	return 'ERROR';

	// save the sign
	var b_negative = Boolean(n_value < 0);
	n_value = Math.abs(n_value);

	// round to 1/100 precision, add ending zeroes if needed
	var s_result = String(Math.round(n_value*1e2)%1e2 + '00').substring(0,2);

	// separate all orders
	var b_first = true;
	var s_subresult;
	while (n_value > 1) {
		s_subresult = (n_value >= 1e3 ? '00' : '') + Math.floor(n_value%1e3);
		s_result = s_subresult.slice(-3) + (b_first ? '.' : ',') + s_result;
		b_first = false;
		n_value = n_value/1e3;
	}
	// add at least one integer digit
	if (b_first)
		s_result = '0.' + s_result;

	// apply formatting and return
	return b_negative
		? '(-$' + s_result + ')'
		: '$' + s_result;
}

function getInnerText(el) {
	if (typeof el == "string") return el;
	if (typeof el == "undefined") { return ''; };
	//alert(typeof el);
	//if (el.innerText) return el.innerText;	//Not needed but it is faster
	var str = "";
	
	var cs = el.childNodes;
	var l = cs.length;
	for (var i = 0; i < l; i++) {
		switch (cs[i].nodeType) {
			case 1: //ELEMENT_NODE
				str += ts_getInnerText(cs[i]);
				break;
			case 3:	//TEXT_NODE
				str += cs[i].nodeValue;
				break;
		}
	}
	return str;
}

function getInnerTextNode(el) {
	if (typeof el == "undefined") { return ''; };
	//if (el.innerText) return el.innerText;	//Not needed but it is faster
	var str = "";
	
	var cs = el.childNodes;
	var l = cs.length;
	var rt = false;
	for (var i = 0; i < l; i++) {
		switch (cs[i].nodeType) {
			case 1: //ELEMENT_NODE
				ret = ts_getInnerText(cs[i]);
				if (ret) return ret;
				break;
			case 3:	//TEXT_NODE
				return cs[i];
				break;
		}
	}
	return false;
}

function parseMoney(money) {
	num = parseFloat(money.replace(/[^0-9.]/g,''));
	if (money.match(/^\(-[�$]/)) num = - num; // Make Negative
	if (isNaN(num)) num = 0;
    return num;
}

function rmrow(tag) {
	tag = findrow(tag);
	tag.parentNode.deleteRow(tag.rowIndex);
}


function in_array(obj, search_val) {
	for (key in obj) {
		if (obj[key] == search_val) {
			return true;
		}
	}
	return false;
}

// Refreshlessly Posts a Form
var gFormInPost = false;
var gFormPreRequest = function(tagName) {}
var gFormPostProcess = function(tagName) {}
var gFormAlertResult = function(text) { alert(text); }

function postForm( thisTag )
{
	if (gFormInPost) {
		alert('Form submission already in progress, please wait for the response.');
		return;
	}
	gFormInPost = thisTag;
	thisTag.disabled = true;
	var theForm = findform(thisTag);
	query = '';
	for( i=0; i < theForm.length; i++ )
	{
		if (theForm.elements[i].type == "checkbox" && !theForm.elements[i].checked)
			continue;
		query = query + theForm.elements[ i ].name +'='+ theForm.elements[ i ].value +'&';
	}

	// Send identifier that we're doing this as part of postForm function
	query = query + 'special_xmlhttprequest=Y&';
	
	req = getHTTPObject();
	req.onreadystatechange = function()
	{
		if( req.readyState == 4 )
		{
			gFormInPost.disabled = false;
			reponse = req.responseText;
			gFormPostProcess(gFormInPost);
			gFormPostProcess = function(gFormInPost) {}
			gFormInPost = false;
			gFormAlertResult( reponse );
			gFormAlertResult = function(text) { alert(text); }
		}
	}
	var action = theForm.action; // Load Action in case form is destroyed for gFormPreRequest
	gFormPreRequest(thisTag);
	gFormPreRequest = function(thisTag) {}
	req.open( 'POST', action, true );
	req.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
	req.setRequestHeader( 'Content-Length', query.length );
	req.send( query );
}

function postArr( tag, action, post_arr) {
	if (gFormInPost) {
		alert('Form submission already in progress, please wait for the response.');
		return;
	}
	gFormInPost = tag;
	var query = '';
	for(var idx in post_arr )
	{
		query = query + idx +'='+ post_arr[ idx ] +'&';
	}
	query = query + 'special_xmlhttprequest=Y&';

	req = getHTTPObject();
	req.onreadystatechange = function()
	{
		if( req.readyState == 4 )
		{
			reponse = req.responseText;
			gFormPostProcess(gFormInPost);
			gFormPostProcess = function(gFormInPost) {}
			gFormInPost = false;
			gFormAlertResult( reponse );
			gFormAlertResult = function(text) { alert(text); }
		}
	}
	gFormPreRequest(tag);
	gFormPreRequest = function(tag) {}
	req.open( 'POST', action, true );
	req.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
	req.setRequestHeader( 'Content-Length', query.length );
	req.send( query );
}

// Returns String of date like 01/31/2008
function getToday() {
	var today = new Date();
	var month = today.getMonth() + 1;
	if (month.toString().length < 2) month = "0" + month;
	var day = today.getDate();
	if (day.toString().length < 2) day = "0" + day;
	var year = today.getFullYear();
	return month + "/"  + day + "/" + year;
}


function toggleHelp(page) {
    var helppage = document.getElementById('help_'+page);
    if (helppage.style.display == 'none') {
        helppage.style.display = '';
    } else {
        helppage.style.display = 'none';
    }
    return false;
}

/*
 *  document.write(calcItemDiscount('$20.10',2,'25%;2:5:10%;6:35%').toSource());
 *  function debugout (label, content) {
 *      document.write(label + ": " + content.toSource() + "<br />");
 *  }
 */

function calcDiscount(amount, qty, discount) {
    return amount - calcItemDiscount(amount, qty, discount);
}

/**
 * Keep in sync with PHP method in inc_database.php
 */
function calcItemDiscount(price, qty, discount) {
        stack = compileDiscount(discount);
        var applicable;
        for (var x in stack) {
            applicable = false;
            if (stack[x].to == 0 && stack[x].from == 0) {
                applicable = true;
            } else if (stack[x].from <= qty) {
                if ((stack[x].to >= qty)||(stack[x].to == 0)) {
                    applicable = true;
                }
            }
            if (applicable) {
                discount = stack[x].value;
            }
        }
        if (isNaN(price))
            price = price.replace('$', '');
        if (isNaN(price))
            return 0; // Discount or not, if we can't figure out the price, return 0
        price = parseFloat(price);
        if (price == 0)
            return 0;
	// Parse out the field...
	var amount = 0;
	var type = '$';
	if (!isNaN(discount)) {
		// Just a #######
		amount = parseFloat(discount);
		type = '$';
	} else if (discount[0] == '$'&&!isNaN(discount.substring(1))) {
		// Is $######.##
		amount = parseFloat(discount.substring(1));
		type = '$';
	} else if (discount.substring(0,2) == 'i$'&&!isNaN(discount.substring(2))) {
		// Is $######.##
		amount = parseFloat(discount.substring(2));
		type = 'i';
	} else if (discount.substring(discount.length-1) == '%'&&!isNaN(discount.substring(0,discount.length-1))) {
		// Is ####%
		amount = parseFloat(discount.substring(0,discount.length-1));
		type = '%';
	} else if (discount.substring(0,2) == '($'&&!isNaN(discount.substring(2,discount.length-3))) {
		// Is ($####.##) Negative Monetary
		amount = parseFloat(discount.substring(2,discount.length-1))*-1;
		type = '$';
	} else if (discount.substring(0,3) == '(-$'&&!isNaN(discount.substring(3,discount.length-4))) {
		// Is (-$####.##) Negative Monetary
		amount = parseFloat(discount.substring(3,discount.length-1))*-1;
		type = '$';
	} else if (discount.substring(0,2) == '-$'&&!isNaN(discount.substring(2,discount.length-2))) {
		// Is -$####.## Negative Monetary
		amount = parseFloat(discount.substring(2))*-1;
		type = '$';
        } else {
            // No discount for this level, return price
            return price;
        }
        if (isNaN(amount)) {
            amount = 0;
        }

	// Do the math...
	var result = price;
	if (type == '$') {
		result = price - amount;
	} else if (type == '%') {
		discount = price * (amount / 100);
		result = price - discount;
	} else if (type == 'i') {
                result = price - (amount * qty);
        }
        
	return result;
}

/**
 * Keep in sync with PHP method in inc_database.php
 */
function compileDiscount(content) {
        // Decompile String into an array
    var stack = content.split(';');
    var order = 0;
    var new_stack = new Array();
    for (var x in stack) {
        var temp = stack[x].split(':');
        var new_discount = new Object();
        if (temp.length == 1) {
            new_discount.from = 0;
            new_discount.to = 0;
            new_discount.value = temp[0];
            new_discount.order = order;
        } else if (temp.length == 2) {
            new_discount.from = parseInt(temp[0]);
            new_discount.to = 0;
            new_discount.value = temp[1];
            new_discount.order = order;
        } else {
            new_discount.from = parseInt(temp[0]);
            new_discount.to = parseInt(temp[1]);
            new_discount.value = temp[2];
            new_discount.order = order;
        }
        new_stack[order] = new_discount;
        ++order;
    }
    return new_stack;
}

function formatCurrency(num) {
	num = num.toString().replace(/\$|\,/g,'');
	if(isNaN(num))
		num = "0";
	sign = (num == (num = Math.abs(num)));
	num = Math.floor(num*100+0.50000000001);
	cents = num%100;
	num = Math.floor(num/100).toString();
	if(cents<10)
	cents = "0" + cents;
	for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
	num = num.substring(0,num.length-(4*i+3))+','+
	num.substring(num.length-(4*i+3));
	return (((sign)?'':'(-') + '$' + num + '.' + cents + ((sign)?'':')'));
}

/* Example use:
 * onchange="checkAll(this.checked, document.forms['frm'])"
 */
function checkAll(check, theArray) {
	if (check ) {
		for (i = 0; i < theArray.elements.length; i++) {
			try{
				theArray.elements[i].checked = true ;
			}catch(e){}
		}
	} else {
		for (i = 0; i < theArray.elements.length; i++) {
			try{
				theArray.elements[i].checked = false ;
			}catch(e){}
		}
	}
}