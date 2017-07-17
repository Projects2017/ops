function setupaddexp(prefix) {
	var temp;
	var catagory;
	var subcatagory;
	var note;
	var row;
	var show = document.getElementById("new_" + prefix + "exp_show");
	temp = resetaddexp(prefix);
	show.style.display = "none";
	row = temp[0];
	catagory = temp[1];
	subcatagory = temp[2];
	note = temp[3];
	temp = document.createElement("option");
	temp.appendChild(document.createTextNode(""));
	catagory.appendChild(temp);
	for (ele in gExpenses) {
		temp = document.createElement("option");
		temp.value = gExpenses[ele].id;
		temp.appendChild(document.createTextNode(gExpenses[ele].name));
		catagory.appendChild(temp);
	}
	row.style.display = "";
}

function remexp(prefix, id) {
	var exprm = document.getElementById("removeexp"+prefix);
	if (exprm.value) exprm.value = exprm.value + "," + String(id);
	else exprm.value = String(id);
}


function addexp(prefix) {
	var catagory = document.getElementById("new_" + prefix + "exp_catagory");
	var subcatagory = document.getElementById("new_" + prefix + "exp_subcatagory");
	var note = document.getElementById("new_" + prefix + "exp_note");
	var form = findform(catagory);
	var form2 = document.forms[form.id];
	debugout("=Adding New Expense=");
	var cat;
	var subcat;
	var before = Array();
	var subbefore = Array();
	var expenses = Array();
	var lastexpense;
	var temp;
	var temp2;
	for (exp in gExpenses) {
		before.push(gExpenses[exp].name);
		if (gExpenses[exp].id == catagory.value) {
			cat = gExpenses[exp];
			break;
		}
	}
	if (cat.subcats.length > 0) {
		for (exp in cat.subcats) {
			subbefore.push(cat.subcats[exp].name);
			if (cat.subcats[exp].id == subcatagory.value) {
				subcat = cat.subcats[exp];
				break;
			}
		}
	}
	if (!subcat) {
		debugout('No Subcatagory');
		subcat = Array();
		subcat.id = '0';
		subcat.name = '';
	}
	for (ele in form.elements) {
		temp2 = getIdorName(form2.elements[ele]);
		if (temp2 == "") {
			continue;
		}
		if (temp2.substr(0, 4) == prefix + "exp") {
			temp = findrow(form.elements[ele]);
			debugout("Name: " + temp2 + " - Cat: " + temp.cells[1].innerHTML + " - Subcat: " + temp.cells[2].innerHTML + " - Note: " + temp.cells[3].innerHTML);
			if (in_array(before, temp.cells[1].innerHTML) ||
				(temp.cells[1].innerHTML == cat.name)) {
				debugout("Before or At Catagory");
				if (temp.cells[1].innerHTML == cat.name) {
					debugout("At Catagory");
					if (in_array(subbefore, temp.cells[2].innerHTML) ||
						temp.cells[2].innerHTML == subcat.name) {
						debugout("At or Before SubCatagory");
						if (temp.cells[2].innerHTML == subcat.name) {
							debugout("At SubCatagory");
							debugout("\"" + temp.cells[3].innerHTML.toLowerCase() + "\" <= \"" + note.value.toLowerCase() + "\"");
							if (temp.cells[3].innerHTML.toLowerCase() <= note.value.toLowerCase()) {
								debugout("Before Note");
								lastexpense = temp;
							} else {
								debugout("After Note");
							}
						} else {
							debugout("Before SubCatagory");
							lastexpense = temp;
						}
					}
				} else {
					debugout("Before Catagory");
					lastexpense = temp;
				}
			}
		}
	}
	addexpraw(lastexpense, prefix, cat, subcat, note.value);
	resetaddexp(prefix);
}


function selectcat(prefix) {
	var catagory = document.getElementById("new_" + prefix + "exp_catagory");
	var subcatagory = document.getElementById("new_" + prefix + "exp_subcatagory");
	var note = document.getElementById("new_" + prefix + "exp_note");
	var temp;
	subcatagory.style.display = "none";
	note.style.display = "none";
	subcatagory.options.length = 0;
	note.value = "";
	if (!catagory.value) {
		return false;
	}
	for (ele in gExpenses) {
		if (gExpenses[ele].id == catagory.value) {
			if (gExpenses[ele].needsub) {
				temp = document.createElement("option");
				temp.appendChild(document.createTextNode(""));
				subcatagory.appendChild(temp);
				for (subs in gExpenses[ele].subcats) {
					temp = document.createElement("option");
					temp.value = gExpenses[ele].subcats[subs].id;
					temp.appendChild(document.createTextNode(gExpenses[ele].subcats[subs].name));
					subcatagory.appendChild(temp);
				}
				subcatagory.style.display = "";
			} else {
				note.style.display = "";
			}
			break;
		}
	}
}


function selectsubcat(prefix) {
	var catagory = document.getElementById("new_" + prefix + "exp_catagory");
	var subcatagory = document.getElementById("new_" + prefix + "exp_subcatagory");
	var note = document.getElementById("new_" + prefix + "exp_note");
	note.value = "";
	if (!subcatagory.value) {
		note.style.display = "none";
	} else {
		note.style.display = "";
	}
}


function resetaddexp(prefix) {
	var catagory = document.getElementById("new_" + prefix + "exp_catagory");
	var subcatagory = document.getElementById("new_" + prefix + "exp_subcatagory");
	var note = document.getElementById("new_" + prefix + "exp_note");
	var show = document.getElementById("new_" + prefix + "exp_show");
	var row = findrow(catagory);
	var temp = Array();
	row.style.display = "none";
	subcatagory.style.display = "none";
	note.style.display = "none";
	show.style.display = "";
	catagory.options.length = 0;
	subcatagory.options.length = 0;
	note.value = "";
	temp[0] = row;
	temp[1] = catagory;
	temp[2] = subcatagory;
	temp[3] = note;
	return temp;
}


function addexpraw(after, prefix, cat, subcat, note) {
	if (after === undefined) {
		after = document.getElementById("new_" + prefix + "exp_row");
	}
	var tr_0 = document.createElement("tr");
	var td_0 = document.createElement("td");
	tr_0.appendChild(td_0);
	var td_1 = document.createElement("td");
	td_1.appendChild(document.createTextNode(cat.name));
	tr_0.appendChild(td_1);
	var td_2 = document.createElement("td");
	if (subcat != undefined) {
		td_2.appendChild(document.createTextNode(subcat.name));
	}
	tr_0.appendChild(td_2);
	var td_3 = document.createElement("td");
	td_3.appendChild(document.createTextNode(note));
	tr_0.appendChild(td_3);
	var td_4 = document.createElement("td");
	tr_0.appendChild(td_4);
	var td_5 = document.createElement("td");
	var input = document.createElement("input");
	input.type = "hidden";
	if (subcat == undefined) {
		input.value = cat.id + ",0," + note;
	} else {
		input.value = cat.id + "," + subcat.id + "," + note;
	}
	input.id = "cat_" + prefix + "expnew" + gNew[prefix];
	input.name = input.id;
	td_5.appendChild(input);
	var input_0 = document.createElement("input");
	input_0.value = "0.00";
	input_0.id = prefix + "expnew" + gNew[prefix];
	input_0.name = input_0.id;
	input_0.size = 7;
	td_5.appendChild(input_0);
	tr_0.appendChild(td_5);
	var td_6 = document.createElement("td");
	var a_0 = document.createElement("a");
	a_0.href = "#";
	a_0.onclick = (function () {if (confirm("Are you sure you want to permanently drop this expense?")) {rmrow(this);}return false;});
	var img_0 = document.createElement("img");
	img_0.src = "images/button_drop.png";
	img_0.border = 0;
	a_0.appendChild(img_0);
	td_6.appendChild(a_0);
	tr_0.appendChild(td_6);
	after.parentNode.insertBefore(tr_0, after.nextSibling);
	gNew[prefix] = gNew[prefix] + 1;
}