function addOption(selectId,txt,val)
{
    var objOption = new Option(txt,val);
    document.getElementById(selectId).options.add(objOption);
}

function clearSelect(selectId)
{
    document.getElementById(selectId).options.length = 0;
}

function pickSelect(selectId, avalue)
{
	for (var i = 0; i < document.getElementById(selectId).options.length; i++)
	{
		if (document.getElementById(selectId).options[i].value == avalue)
		{
			document.getElementById(selectId).selectedIndex = i;
			break;
		}
	}
}
