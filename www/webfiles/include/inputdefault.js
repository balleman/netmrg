function set_defaults(parentid)
{
	/*
	SHOULD RUN ON LOAD
	This function looks for a special attribute on input tags - default.
	If it finds this attribute inside an input field inside a given parentid,
	it loads the default attribute values for the current values,
	and then tries to style the box to make it look "grayed out".
	Finally, it sets an event to trigger the removal of the defaults onclick
	*/
	var x = document.getElementById(parentid);
	if (!x) return;
	var y = x.getElementsByTagName('input');
	for (var i = 0; i < y.length; i++)
	{
		if (y[i].getAttribute('default') && y[i].getAttribute('type') == 'text' && y[i].value == '')
		{
			y[i].value = y[i].getAttribute('default');
			y[i].style.color = '#808080';
			y[i].style.backgroundColor = '#eeeeee';
			y[i].onmouseover = remove_defaults;
			y[i].onmouseout = function () { if (!this.value) { this.value = this.getAttribute('default'); this.style.color = '#808080'; this.style.backgroundColor='#eeeeee'; } }
		} // end if correct input type
	} // end foreach input item
} // end set_defaults();

function remove_defaults()
{
	/*
	This function is family with the set_defaults() function, but does the opposite.
	It takes any input fields that had defaults applied to them,
	then returns them to normal by blanking out the value and resetting the bgcolor.
	*/
	if (this.getAttribute('type') == 'text' && this.value == this.getAttribute('default'))
	{
		this.value = '';
		this.style.color = '';
		this.style.backgroundColor = '';
	}
}

function set_defaults_search()
{
	set_defaults('search');
}

addOnloadEvent(set_defaults_search);
