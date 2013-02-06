/**
 *  Alex Russinov alexrussinov@gmail.com
 */

function numbersinput(input) 
{ 
    var value = input.value; 
    var rep = /[-\.;":'a-zA-Z]/; 
    if (rep.test(value)) 
       { 
        value = value.replace(rep, ''); 
        input.value = value; 
        } 
} 


function check(ctrl)
{

	if (ctrl.checked == true) {

		switch (ctrl.id)
		{
		case 't2_1':
		    document.getElementById("tva2_1").type='text';
		    break;
		case 't5_5':
			document.getElementById("tva5_5").type='text';
			break;
		case 't7_0':
			document.getElementById("tva7_0").type='text';
			break;
		case 't19_6':
			document.getElementById("tva19_6").type='text';
			break;
		}
	} 
	else 
	{

		switch (ctrl.id)
		{
		case 't2_1':
		    document.getElementById("tva2_1").type='hidden';
		    document.getElementById("tva2_1").value='';
		    break;
		case 't5_5':
			document.getElementById("tva5_5").type='hidden';
			document.getElementById("tva5_5").value='';
			break;
		case 't7_0':
			document.getElementById("tva7_0").type='hidden';
			document.getElementById("tva7_0").value='';
			break;
		case 't19_6':
			document.getElementById("tva19_6").type='hidden';
			document.getElementById("tva19_6").value='';
			break;
		}
	}
}

 
/* Recive value of a type for a choice of a company */
function getcompany ()
{
	var type;
	type=document.getElementById("type").value;
	//alert(type);
	showList(type);
	}


// New

var xmlhttp;

function showList(type)
{
var param="type_filter="+type;
//var param2="origintype="+x;
xmlhttp=GetXmlHttpObject()
if (xmlhttp==null)
  {
  alert ("Your browser does not support XML HTTP Request");
  return;
  }
var url="./getsoc.php";

xmlhttp.onreadystatechange=stateChanged ;
xmlhttp.open("GET",url+"?"+param,true);
xmlhttp.send(null);
}

function stateChanged()
{
if (xmlhttp.readyState==4)
  {
  document.getElementById("company").innerHTML=xmlhttp.responseText;
  //document.getElementById("livesearch").style.border="1px solid #A5ACB2";
  }
}

function GetXmlHttpObject()
{
if (window.XMLHttpRequest)
  {
  // code for IE7+, Firefox, Chrome, Opera, Safari
  return new XMLHttpRequest();
  }
if (window.ActiveXObject)
  {
  // code for IE6, IE5
  return new ActiveXObject("Microsoft.XMLHTTP");
  }
return null;
}

function showInput()
{
	if (document.getElementById("CrNewCom").selected)
	{
	document.getElementById("soc_text").type='text';
	document.getElementById("soc").disabled='disabled';
	}
	
	}