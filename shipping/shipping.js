// Javascript for Shipping queue

function changeType() {
	var typeselect = document.getElementById('type_select');
	if(document.getElementById('type_select').value == 'open') {
		var groupit = document.getElementById('grouptext');
		document.getElementById('grouptext').innerHTML = "Group Multi-PO Capable Orders";
	} else {
		var groupit = document.getElementById('grouptext');
		document.getElementById('grouptext').innerHTML = "Group by Dealer";
	}
	//document.settings_form.submit();
}

function chooseVendor(vend, selected_form) {
	var formSet = document.settings_form;
	formSet.chosen_vendor.value = vend != 'all' ? vend : '';
	formSet.vendor_entry.value = 'true';
	formSet.form_entry.value = 'false';
	if(vend == null || vend == '') return;
	//formSet.dealer_entry.value = 'false';
	showForms(vend, selected_form);
	//formSet.chosen_form.value = 'all';
	//formSet.chosen_dealer.value = 'all';
	//formSet.submit();
}

function showForms(vend, selected_form)
{
	if(vend == null || vend == '') return;
	var select_form = document.getElementById('select_form');
	select_form.style.display = vend != 'all' && vend != 'n/a' ? "" : "none";
	select_form = document.getElementById('choose_form');
	// add the options to the select
	// clear the old ones
	// first, store the length in a var...otherwise, if we compare to the length it'll end prematurely!
	var optmax = select_form.options.length;
	for(var i = 1; i<optmax; i++)
	{
		select_form.options[1] = null;
	}
	if(vend != 'all' && vend != 'n/a')
	{
		for(var i = 0; i<form_names[vend].length; i++)
		{
			select_form.options[i+1] = new Option(form_names[vend][i], form_ids[vend][i]);
			if(form_ids[vend][i] == selected_form) select_form.options[i+1].selected = true;
		}
	}
}

function chooseForm(frm) {
	var formSet = document.settings_form;
	formSet.form_entry.value = 'true';
	//formSet.dealer_entry.value = 'false';
	formSet.chosen_form.value = frm;
	//formSet.chosen_dealer.value = 'all';
	//formSet.submit();
}
	
function chooseDealer(deal) {
	var formSet = document.settings_form;
	formSet.dealer_entry.value = 'true'
	formSet.chosen_dealer.value = deal;
	//formSet.submit();
}

function runQuery() {
	var formSet = document.settings_form;
	formSet.vendor_entry.value = 'true';
	formSet.form_entry.value = 'true';
	formSet.dealer_entry.value = 'true';
	formSet.submit();
}

function ediBol(val)
{
	document.queue.chosen.value = val;
	document.queue.edi.value = 1;
	document.queue.submit();
}

function setAgentMode(mode)
{
	switch(mode)
	{
		case 'add':
			// first hide the agent select dropdown
			var agent_select = document.getElementById('agent_select');
			agent_select.style.visibility = "hidden";
			var select_agent = document.getElementById('select_agent');
			select_agent.options.selectedValue = 'n';
			window.location = 'manageagents.php';
			break;
		case 'modify':
			var agent_select = document.getElementById('agent_select');
			agent_select.style.visibility = "visible";
			var select_agent = document.getElementById('select_agent');
			select_agent.options.selectedValue = 'n';
			break;
		case 'delete':
			var agent_select = document.getElementById('agent_select');
			agent_select.style.visibility = "visible";
			var select_agent = document.getElementById('select_agent');
			select_agent.options.selectedValue = 'n';
			break;
		case 'view':
			document.getElementById('agentmgt').submit();
			break;
	}
}

function csvExport()
{
	var csvexport = document.getElementById('csvexport');
	csvexport.setAttribute('value',1);
	document.getElementById('agentmgt').submit();
}

function printWalmartPacking(bol)
{
	
	alert('Printing walmart packing list on bol '+bol);	
	window.location = 'viewbol.php?id='+bol; // returns to a view of the BOL in question
}