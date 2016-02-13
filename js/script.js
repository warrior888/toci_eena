///==============
function selectAll(obj)
{
       var tab_os = document.getElementsByName("id_osoby_checkbox[]");
       if (obj.checked)
       {
           for (i = 0; i < tab_os.length; i++)
           {
               tab_os[i].checked = true;
           }
       }
       else
       {
           for (i = 0; i < tab_os.length; i++)
           {
               tab_os[i].checked = false;
           }
       }
}
        
function PokazStrone(ctrl, div_el, hid_el, td_el, nr)
{
    var div = document.getElementById(div_el + nr);
    var widoczny = document.getElementById(hid_el);
    var div_schowaj = document.getElementById(div_el + widoczny.value);
    
    ctrl.style.color = 'yellow';
    
    if (div.style.display == 'none')
    {
        div.style.display = '';
        div_schowaj.style.display = 'none'; 
        var pop_td = document.getElementById(td_el + widoczny.value);
        pop_td.style.color = 'black';
        widoczny.value = nr;
    }
}

//boxObj is a checkbox to click, htmlCtrl is a div or else where button to dissapear or appear is
function PassValsToOpener(controls, values)
{
	var ctrl = controls.split(",");
	var vals = values.split(",");
	for (var i = 0; i < vals.length; i++)
	{
		opener.window.document.getElementById(ctrl[i]).value = vals[i];
    }
} 
function showHideButton(boxName, htmlCtrlName)
{
	boxObj = document.getElementById(boxName);
	htmlCtrl = document.getElementById(htmlCtrlName);
	if (boxObj.checked == true)
	{
		htmlCtrl.style.display = '';
	}
	else
	{
		htmlCtrl.style.display = 'none';
	}
}
function ClientRequiredFields(obj1, obj2)
{
	if (obj1.value == "" || obj2.value == "")
	{
		alert('Nie wype³niono wszystkich pól.');
		event.returnValue = false;
		return false;
	}
}
function testMonth(month)
{
	month = month + 1;
	if (month < 10)
	{
		return '0' + month;		
	}
	else
	{
		return month;
	}
}
function testDay(day)
{
	//month = month + 1;
	if (day < 10)
	{
		return '0' + day;		
	}
	else
	{
		return day;
	}
}
function DateYesterday(obj, date)
{
	var currentDate = new Date();
	var year = currentDate.getFullYear() - 14;
	var curDateString = year + '-' + testMonth(currentDate.getMonth()) + '-' + testDay(currentDate.getDate());
	//alert(curDateString);
	
	if (date > curDateString)
	{
		alert('Podano datê osoby zbyt m³odej.');
		obj.value = '';
	}
	
}
function DateTomorrow(obj, date)
{
	var currentDate = new Date();
    
	var curDateString = currentDate.getFullYear() + '-' + testMonth(currentDate.getMonth()) + '-' + testDay(currentDate.getDate());
	//alert(curDateString);
	
	if (date < curDateString && obj.value.length > 0)
	{
		alert('Podano datê z przesz³o¶ci.');
		obj.value = '';
	}
	
}

	function wpis_rok (obj)
	{
		if (obj.value.length < 4 && obj.value.length > 0)
		{
			obj.value="";
			obj.focus();
			alert("B³êdnie wprowadzone dane!");
		}
		if (obj.value.length == 4)
		{
			if(obj.value < 1900 || obj.value > 2006)
			{
				alert("B³êdnie wprowadzone dane!");
			        obj.value="";
			        obj.focus();
			}
			if (isNaN (obj.value))
			{
				obj.value="";
				obj.focus();
				alert("B³êdnie wprowadzone dane!");
			}
		}
	}
	function wpis_miesiac (obj)
	{
		if (obj.value.length < 2 && obj.value.length > 0)
		{
			obj.value="";
			obj.focus();
			alert("B³êdnie wprowadzone dane!");
		}
		if (obj.value.length == 2)
		{
			if(obj.value < 1 || obj.value > 12)
			{
				alert("B³êdnie wprowadzone dane!");
			        obj.value="";
			        obj.focus();
			}
			if (isNaN (obj.value))
			{
				obj.value="";
				obj.focus();
				alert("B³êdnie wprowadzone dane!");
			}
		}
	}
	function wpis_dzien (obj)
	{
		if (obj.value.length < 2 && obj.value.length > 0)
		{
			obj.value="";
			obj.focus();
			alert("B³êdnie wprowadzone dane!");
		}
		if (obj.value.length == 2)
		{
			if(obj.value < 1 || obj.value > 31)
			{
				alert("B³êdnie wprowadzone dane!");
			        obj.value="";
			        obj.focus();
			}
			if (isNaN (obj.value))
			{
				obj.value="";
				obj.focus();
				alert("B³êdnie wprowadzone dane!");
			}
		}
	}
	function telefon_kom (ob)
    {
    	var wzor = /^(50|51|53|60|66|69|72|78|79|88){1}[0-9]{7}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
				alert("Podaj telefon komórkowy polskiego operatora w formacie 9 cyfr!");
		       	ob.focus();
	            ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
    }
	function telefon_stacj (ob)
    {
        var wzor = /^[1-9]{1}[0-9]{8}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
	            alert("Podaj telefon stacjonarny w formacie 9 cyfr!");
		       	ob.focus();
	            ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function telefon_inny (ob)
    {
        var wzor = /^[1-9]{1}[0-9]{8,12}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
		    {
            	alert("Podaj telefon bez zer z przodu (max 13 cyfr)!");
	       	    ob.focus();
              	ob.value = "";
			    return 0;
		    }
        }
        else
        {
            return ob.value;
        }
    }
    function EmailValidate (ob)
    {
        var wzor = /^[0-9,A-Z,a-z,.,\,,_,-]{1,35}(\@){1}[0-9,A-Z,a-z,.,-]{1,35}(.){1}[A-Z,a-z]{1,5}$/;
        if (!wzor.test(ob.value))
        {
            if(ob.value !== "")
            {
                alert("Podany E-mail wygl±da na nieprawid³owy.");
                ob.focus();
                ob.value = "";
                return 0;
            }
        }
        else
        {
            return ob.value;
        }
    }
	function sprawdz_nazwisko (ob)
    {
        var wzor = /^[A-Z,Ó,¦,£,¯,¬,Æ,Ñ,¡,Ê,a-z,\-,\ ,ê,ó,±,¶,³,¿,¼,æ,ñ]{2,30}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
            	alert("Podany ci±g znaków nie przypomina nazwiska.");
	       		ob.focus();
            	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function sprawdz_ulica (ob)
    {
        var wzor = /^[A-Z,0-9,Ó,¦,£,¯,¬,Æ,Ñ,¡,Ê,a-z,\-,\ ,ê,ó,±,¶,³,¿,¼,æ,ñ,\.,\/]{5,50}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
              	alert("Podany ci±g znaków nie przypomina ulicy.");
	       		ob.focus();
              	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function sprawdz_kod (ob)
    {
        var wzor = /^[0-9]{2}(\-){1}[0-9]{3}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
              	alert("B³êdnie podany kod pocztowy.");
	       		ob.focus();
              	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function sprawdz_tygodnie (ob)
    {
        var wzor = /^[1-9]{1}[0-9]{0,1}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
              	alert("Niew³a¶ciwa liczba tygodni.");
	       		ob.focus();
              	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function sprawdz_obuwie (ob)
    {
        var wzor = /^[2-5]{1}[0-9]{1}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
              	alert("Nie jest to numer obuwia.");
	       		ob.focus();
              	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function soffi (ob)
    {
        var wzor = /^[1-9]{1}[0-9]{8}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
              	alert("B³êdnie podany numer soffi.");
	       		ob.focus();
              	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function sprawdz_klienta (ob)
    {
        var wzor = /^[A-Z,Ó,¦,£,¯,¬,Æ,Ñ]{1}[A-Z,a-z,\.,\ ,&,ê,ó,±,¶,³,¿,¥,æ,ñ]{2,44}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
              	alert("Podany ci±g znaków nie przypomina klienta.");
	       		ob.focus();
              	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function sprawdz_stawka (ob)
    {
        var wzor = /^[1-9]{1}[0-9,\,,]{2,4}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
              	alert("B³êdnie podana stawka.");
	       		ob.focus();
              	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function sprawdz_stanowisko (ob)
    {
        var wzor = /^[A-Z,Ó,¦,£,¯,¬,Æ,Ñ]{1}[a-z,\ ,ê,ó,±,¶,³,¿,¥,æ,ñ]{2,29}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
              	alert("Podany ci±g znaków nie przypomina stanowiska pracy.");
	       		ob.focus();
              	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
	function sprawdz_ilosc_osob (ob)
    {
        var wzor = /^[0-9]{1,2}$/;
        if (!wzor.test(ob.value))
        {
			if(ob.value !== "")
			{
            	alert("Niew³a¶ciwa ilo¶æ osób.");
	       		ob.focus();
            	ob.value = "";
				return 0;
			}
        }
        else
        {
            return ob.value;
        }
	}
function zamien(tekst)
{
	var wynik = "", znak;
	var zrodlo = tekst.value;
	for (var i = 0; i <= zrodlo.length; i++)
	{
        znak = zrodlo.substring(i,i + 1);
		if (znak == '*')
		{
			wynik += '%';
		}
		else
		{
			wynik += znak;
		}
	}
	tekst.value = wynik;
	return wynik;
}
function ustaw_hist_zatr()
{
    var f = document.getElementById("statusy");
    var e = opener.window.document.getElementById("1");
    e.selectedIndex = f.selectedIndex - 1;
    f = document.getElementById("tygodnie");
    e = opener.window.document.getElementById("3");
    e.value = f.value;
}
function umow_pasywnego()
{
    var f = document.getElementById("status");
    var e = opener.window.document.getElementById("1");
    e.selectedIndex = f.selectedIndex - 1;
    f = document.getElementById("ilosc_tyg");
    e = opener.window.document.getElementById("3");
    e.value = f.value;
}
function podm_hist_zatr()
{
    var f = document.getElementById("status");
    var e = opener.window.document.getElementById("1");
    e.selectedIndex = f.selectedIndex;
    f = document.getElementById("ilosc_tyg");
    e = opener.window.document.getElementById("3");
    e.value = f.value;
}
function podm_status()
{
    var f = document.getElementById("status");
    var e = opener.window.document.getElementById("1");
    e.selectedIndex = f.selectedIndex;
}

function markRow (row, css) {
    
    if (row.className != 'markedRow')
        row.className = css;
}

function pointRow (row, css) {
    
    var checkbox; // = row.getElementsByType('checkbox');
    var inputs = row.getElementsByTagName('input');
    //id_osoby_checkbox[]
    for (var i = 0; i < inputs.length; i++) {
        
        if (inputs[i].getAttribute('type') == 'checkbox')
            checkbox = inputs[i];
    }
    
    if (row.className == css) {
        row.className = 'hoveredRow';//nativeCss;
        if (checkbox)
            checkbox.checked = false;
    } else {
        row.className = css;
        if (checkbox)
            checkbox.checked = true;
    }  
}

function policz (obj)
{
	var ile_widocznych = 0;
	tabela = new Array ("imie","nazwisko","plec","data_urodzenia","msc_urodzenia","msc","ulica","kod","wyksztalcenie","zawod","prawo_jazdy","umiejetnosci","konsultant","data_zgloszenia","status","charakter","paszport","termin_wyjazdu","ilosc_tyg","ostatni_kontakt","os_dzwoniaca","telefon","komorka","inny","email","jezyk","pref","antyp","znani_klienci","biuro","msc_odjazdu","data_powrotu","zadania_dnia","zad_problem","korespondencja","rodz_kor","data_rekl","pop_prac","branza","stanowisko","rok_jaar","klient_jaar","ankieta","zrodlo");
	for (var j=0; j<44; j++)
	{
		box = eval("document.formwidok." + tabela[j]);
		if (box.checked == true)
		{
			ile_widocznych++;
			if (ile_widocznych > 15)
			{
				obj.checked = false;
			}
		}
	}
}

	function wroc()
	{
		var n = parent.frames[0].document.getElementById("nazwisko");
		var d = parent.frames[0].document.getElementById("data");
		var s = parent.frames[0].document.getElementById("szukaj_button");
		var s1 = parent.frames[0].document.getElementById("szukaj_form");
		var k = parent.frames[0].document.getElementById("kwerenda");
		var k1 = parent.frames[0].document.getElementById("kwerendy");
		var wi = parent.frames[0].document.getElementById("widok");
		var wi1 = parent.frames[0].document.getElementById("widoki_select");
		var wa = parent.frames[0].document.getElementById("wakat");
		var wa1= parent.frames[0].document.getElementById("wakaty_select");
		if ((n.value != "") || (d.value != ""))
		{
			s.click();
		}
		if (k.selectedIndex > 0)
		{
			k1.submit();
		}
		if (wi.selectedIndex > 0)
		{
			wi1.submit();
		}
		if (wa.selectedIndex > 0)
		{
			wa1.submit();
		}
	}
// Create scrolling variable if it doesn't exist
if (!Scrolling) {var Scrolling = {};}

//Scroller constructor
Scrolling.Scroller = function (o, w, h, t) 
{
	//get the container
	var list = o.getElementsByTagName("div");
	for (var i = 0; i < list.length; i++) 
	{
		if (list[i].className.indexOf("Scroller-Container") > -1) 
		{
			o = list[i];
		}
	}
	
	//private variables
	var self  = this;
	var _vwidth   = w;
	var _vheight  = h;
	var _twidth   = o.offsetWidth
	var _theight  = o.offsetHeight;
	var _hasTween = t ? true : false;
	var _timer, _x, _y;
	
	//public variables
	this.onScrollStart = function (){};
	this.onScrollStop  = function (){};
	this.onScroll      = function (){};
	this.scrollSpeed   = 20;
	
	//private functions
	function setPosition (x, y) 
	{
		if (x < _vwidth - _twidth) 
			x = _vwidth - _twidth;
		if (x > 0) x = 0;
		if (y < _vheight - _theight) 
			y = _vheight - _theight;
		if (y > 0) y = 0;
		
		_x = x;
		_y = y;
		
		o.style.left = _x +"px";
		o.style.top  = _y +"px";
	};
	
	//public functions
	this.scrollBy = function (x, y) 
	{ 
		setPosition(_x - x, _y - y);
		//this.onScroll();
	};
	
	this.scrollTo = function (x, y) 
	{ 
		setPosition(-x, -y);
		this.onScroll();
	};
	
	this.startScroll = function (x, y) 
	{
		//this.stopScroll();
		//this.onScrollStart();
		_timer = window.setInterval(function ()	{ self.scrollBy(x, y);}, this.scrollSpeed);
	};
		
	this.ScrollOnce = function (x, y) 
	{
		//this.stopScroll();
		//this.onScrollStart();
		self.scrollBy(x, y);
	};
	this.stopScroll  = function () 
	{ 
		if (_timer) window.clearInterval(_timer);
		//this.onScrollStop();
	};
	
	this.reset = function () 
	{
		_twidth  = o.offsetWidth;
		_theight = o.offsetHeight;
		_x = 0;
		_y = 0;
		
		o.style.left = "0px";
		o.style.top  = "0px";
		
		if (_hasTween) t.apply(this);
	};
	
	this.swapContent = function (c, w, h) 
	{
		o = c;
		var list = o.getElementsByTagName("div");
		for (var i = 0; i < list.length; i++) 
		{
			if (list[i].className.indexOf("Scroller-Container") > -1) 
			{
				o = list[i];
			}
		}
		
		if (w) _vwidth  = w;
		if (h) _vheight = h;
		reset();
	};
	
	this.getDimensions = function () 
	{
		return {
			vwidth  : _vwidth,
			vheight : _vheight,
			twidth  : _twidth,
			theight : _theight,
			x : -_x, y : -_y
		};
	};
	
	this.getContent = function () 
	{
		return o;
	};
	
	this.reset();
};

function handle(ev)
{
	if (ev.wheelDelta > 0)
	{
		scroller.ScrollOnce(0, -25);
	}
	else
	{
		scroller.ScrollOnce(0, 25);
	}
}
function scrolldiagonal_b()
{
	//alert(event.wheelDelta);
	if (event.wheelDelta > 0)
	{
		scroller.ScrollOnce(-15, 0);
		//setTimeout( function () { scroller.stopScroll(); },40);
	}
	else
	{
		scroller.ScrollOnce(15, 0);
		//setTimeout( function () { scroller.stopScroll(); },40);
	}
}

// Create scrolling variable if it doesn't exist
if (!scrolling_center) var scrolling_center = {};

//Scroller constructor
scrolling_center.scroller_center = function (o, w, h, t) 
{
	//get the container
	var list = o.getElementsByTagName("div");
	for (var i = 0; i < list.length; i++) 
	{
		if (list[i].className.indexOf("main-container") > -1) 
		{
			o = list[i];
		}
	}
	
	//private variables
	var self  = this;
	var _vwidth   = w;
	var _vheight  = h;
	var _twidth   = o.offsetWidth;
	var _theight  = o.offsetHeight;
	var _hasTween = t ? true : false;
	var _timer, _x, _y;
	
	//public variables
	this.onScrollStart = function (){};
	this.onScrollStop  = function (){};
	this.onScroll      = function (){};
	this.scrollSpeed   = 20;
	
	//private functions
	function setPosition (x, y) {
		if (x < _vwidth - _twidth) 
			x = _vwidth - _twidth;
		if (x > 0) x = 0;
		if (y < _vheight - _theight) 
			y = _vheight - _theight;
		if (y > 0) y = 0;
		
		_x = x;
		_y = y;
		
		o.style.left = _x +"px";
		o.style.top  = _y +"px";
	};
	
	//public functions
	this.scrollBy = function (x, y) { 
		setPosition(_x - x, _y - y);
		//this.onScroll();
	};
	
	this.scrollTo = function (x, y) { 
		setPosition(-x, -y);
		this.onScroll();
	};
	
	this.startScroll = function (x, y) {
		//this.stopScroll();
		//this.onScrollStart();
		_timer = window.setInterval(
			function () { self.scrollBy(x, y); }, this.scrollSpeed
		);
	};
		
	this.ScrollOnce = function (x, y) {
		//this.stopScroll();
		//this.onScrollStart();
		self.scrollBy(x, y);
	};
	this.stopScroll  = function () { 
		if (_timer) window.clearInterval(_timer);
		//this.onScrollStop();
	};
	
	this.reset = function () {
		_twidth  = o.offsetWidth;
		_theight = o.offsetHeight;
		_x = 0;
		_y = 0;
		
		o.style.left = "0px";
		o.style.top  = "0px";
		
		if (_hasTween) t.apply(this);
	};
	
	this.swapContent = function (c, w, h) {
		o = c;
		var list = o.getElementsByTagName("div");
		for (var i = 0; i < list.length; i++) {
			if (list[i].className.indexOf("main-container") > -1) {
				o = list[i];
			}
		}
		
		if (w) _vwidth  = w;
		if (h) _vheight = h;
		reset();
	};
	
	this.getDimensions = function () {
		return {
			vwidth  : _vwidth,
			vheight : _vheight,
			twidth  : _twidth,
			theight : _theight,
			x : -_x, y : -_y
		};
	};
	
	this.getContent = function () {
		return o;
	};
	
	this.reset();
};

function handle_central()
{
	//alert(event.wheelDelta);
	if (event.wheelDelta > 0)
	{
		scroller_center.ScrollOnce(0, -200);
		//setTimeout( function () { scroller_center.stopScroll(); },40);
	}
	else
	{
		scroller_center.ScrollOnce(0, 200);
		//setTimeout( function () { scroller_center.stopScroll(); },40);
	}
}

	function wiek_stawki (ob)
        {
            var wzor = /^(16|17|18|19|20|21|22){1}$/;
            if (!wzor.test(ob.value))
            {
		if(ob.value != "")
		{
              		alert("B³êdnie podano wiek: "+ob.value+".\nOczekiwano liczby z zakresu od 16 do 22 lat.");
              		ob.value = "";
		}
            }
            else
            {
              return ob.value;
            }
	}
	function stawki_wiek (ob)
        {
            var wzor = /^[1-9]{0,1}[0-9]{1}(,){1}[0-9]{2}$/;
            if (!wzor.test(ob.value))
            {
		if(ob.value != "")
		{
              		//alert("B³êdnie podano stawkê: "+ob.value+".\nOczekiwano stawki w formacie xx,xx lub x,xx.");
              		alert("B³êdnie podano stawkê: "+ob.value+".\nOczekiwano stawki w formacie xx,xx lub x,xx ,\ngdzie co najmniej jeden x > 0.");
              		ob.value = "";
		}
            }
            else
            {
		if (ob.value == "0,00")
		{
              		alert("B³êdnie podano stawkê: "+ob.value+".\nOczekiwano stawki w formacie xx,xx lub x,xx ,\ngdzie co najmniej jeden x > 0.");
              		ob.value = "";
		}
		else
		{
              		return ob.value;
		}
            }
	}


function MailValidate (cntl, ev)
{
    /*var text = cntl.value;
    var len = text.length;
    var k = ev.keyCode;
    var oneChar, result="", sel, r2, rng, cPos, shift;
    sel=document.selection;
    r2=sel.createRange();
    rng=cntl.createTextRange();
    rng.setEndPoint("EndToStart", r2);
    cPos=rng.text.length;
    if ((k < 33 || k > 40) && (k < 65 || k > 90) && (k < 16 || k > 18) && (k < 48 || k > 57))
    {
    
    shift = 0;
    for (var i = 0; i < len; i++)
    {
        oneChar = text.substring(i,i + 1);
        if ((oneChar >= 'a' && oneChar <= 'z') || (oneChar >= '0' && oneChar =< '9') || (oneChar >= 'A' && oneChar <= 'Z') || (oneChar == ' ') || (oneChar == ',') || (oneChar == '%') || (oneChar == '*') || (oneChar == '@'))
        {
            if ((oneChar == '*'))
            {oneChar = '%';}
            result += oneChar;
        }
        else
        {
            result += '';
            shift = 1;
            //if (cPos < len)
            //{cPos--;}
        }
    }
    cntl.value = result;
    rng.move("character", cPos-shift);
    rng.select();
    } */
}
function CheckDate (cntl, date)
{
       //regular expresion pattern checking the date propriety allowing :
       /*
       year range: 1000 - 1199; 1900 - 2199; 2900 - 2999
       month range: 1 - 12
       day range: 1 - 31
       year range is quite unfortunate, that's why later an if will be required
       */
       var pattern;
       var year, month, day;
       var culYearB, culYearE, culMonthB, culMonthE, culDayB, culDayE;
       
       culYearB = 0;
       culYearE = 4;
       culMonthB = 5;
       culMonthE = 7;
       culDayB = 8;
       culDayE = 10;
       pattern = /^(1|2){1}(0|1|9){1}[0-9]{2}(\-){1}(01|02|03|04|05|06|07|08|09|10|11|12){1}(\-){1}(01|02|03|04|05|06|07|08|09|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31){1}$/;
       
       //test the date provided by a user with the regular expresion
       if (!pattern.test(date))
       {
           infoData(cntl);
       }
       //the date went through the regular expresion test
       else
       {    
            //find a year of a date
            year = date.substring(culYearB, culYearE);
            //find a month
            month = date.substring(culMonthB, culMonthE);
            //find a day
            day = date.substring(culDayB, culDayE);
            //cut off nonsense years (to far in the future or in the past)
            if (year < 1900 || year > 2200)
            {
                infoData (cntl);
            }
            //if the month is february, april or june (below 7 and even number)
            if (month < 7 && (month % 2) == 0)
            {
                //there is no 31 in those months
                if (day > 30)
                {
                    infoData (cntl);
                    return 0;
                }
                //if we happen to have february as a month
                if (month == 2)
                {
                    //if the year is a leap year
                    if (year % 4 == 0)
                    {
                        //allow only 29 of february
                        if (day > 29)
                        {
                            infoData (cntl);
                        }
                    }
                    //the year is not a leap year
                    else
                    {
                        //day is not higher than 28
                        if (day > 28)
                        {
                            infoData (cntl);
                        }
                    }
                }
            }
            //months above august and odd so september and november
            if (month > 8 && (month % 2) == 1)
            {
                //september and november has no 31
                if (day > 30)
                {
                    infoData (cntl);
                }
            }
       }
}
//function throwing an info the date is wrong, throwing it away and leaving focus in control to allow
//repeatition of an attempt to input proper date
function info (item)
{
    alert("Podano b³êdne dane.");
    item.focus();
    item.value = "";
    return 0;
}
function infoData(item)
{
    alert('Podaj datê w formacie RRRR-MM-DD.');
    item.focus();
    item.value = "";
    return 0;
}
function focusItem (item) 
{
    var element = document.getElementById(item);
    if (element !== null && element != 'undefined') 
    {
        element.focus();
    }
}
function regpatterntest(cntl)
{
    pattern = /^[0-9]{1,2}(\ ){1}[0-9]{1,2}$/;
    if (cntl.value !== 0)
    if (!pattern.test(cntl.value))
    {
        info(cntl);
    }
}
function CheckDL(cntl)
{
    if (cntl.value.length !== 21 && cntl.value.length !== 0)
    {
        info(cntl);
    }
}
function CheckLength(cntl)
{
    var bool = 0;
    if (cntl.value.length !== 10 && cntl.value.length !== 0)
    {
        infoData(cntl);
        bool = 1;
    }
    if (cntl.value.length !== 0 && bool == 0)
	{
		CheckDate(cntl, cntl.value);
	}
}

function DateKeyUp (cntl)
{
    if (cntl.value.length == 10)
    {
        CheckDate(cntl, cntl.value);
    }
}
function DoubleDateKeyUp (cntl)
{
    if (cntl.value.length == 21)
    {
        CheckDate(cntl, cntl.value.substring(0, 10));
        CheckDate(cntl, cntl.value.substring(11, 21));
    }
}
function DoubleDateValidate (cntl, ev)
{
    var k = ev.keyCode;
    var ch = String.fromCharCode(k);
    var oneChar, result="", sel, r2, rng, cPos, shift=0;
    sel=document.selection;
    r2=sel.createRange();
    rng=cntl.createTextRange();
    rng.setEndPoint("EndToStart", r2);
    cPos=rng.text.length;
    // k == 8 > backspace; 46 > delete; 37 > left arrow; 38 > up arrow; 39 > down arrow; 40 > right arrow
    if (k != 8 && k != 46 && (k < 8 || k > 9) && (k < 33 || k > 40) && (k < 16 || k > 18))
    {
        var text = cntl.value;
        shift = 0;
        for (var i = 0; i <= text.length; i++)
        {
            oneChar = text.substring(i,i + 1);
            if ((oneChar < '0' || oneChar > '9') && (oneChar != " ") && (oneChar != "-"))
            {
                result += '';
                //shift = 1;
            }
            else
            {
                result += oneChar;
            }
        }
        cntl.value = result;
        rng.move("character", cPos-shift);
        rng.select();
    }
    if(result)
    {
        if (result.length < 10)
        {
            info(cntl);
        }
        if (result.length == 10)
        {
            CheckDate(cntl, result.substring(0, 10));
        }
        if ((result.length < 21) && (result.length != 10))
        {
            info(cntl);
        }
        if (result.length == 21)
        {
            CheckDate(cntl, result.substring(0, 10));
            CheckDate(cntl, result.substring(11, 21));
        } 
        if (result.length > 21)
        {
            info(cntl);
        } 
    }
}
function spr_os(nr)
{
	//funkcja uzupelnia hiddeny informacja z ptaszkow - lista osob wybranych sposrod grupy
	var tab_os = parent.frames[2].document.getElementsByName("id_osoby_checkbox[]");
	var hidden_os = parent.frames[nr].document.getElementById("h_os");
	var hidden_osb = parent.frames[nr].document.getElementById("h_osb");
	var hidden_osb_ankiety = parent.frames[nr].document.getElementById("id_h_lista_osoby_ankieta");
    var resultValue = '';

	for (i = 0; i < tab_os.length; i++)
	{
		if (tab_os[i].checked)
		{
			resultValue += tab_os[i].value + "|";
		}
	}

    if (hidden_os)
        hidden_os.value = resultValue;

    if (hidden_osb_ankiety)
	    hidden_osb_ankiety.value = resultValue;
        
    if (hidden_osb)
	    hidden_osb.value = resultValue;
        
}
function DlugoscSms (cntl, ev)
{
	var k = (ev.keyCode) ? ev.keyCode : ev.which;
	if (k > 128)
	{
		alert('U¿yto niedozwolonego znaku.');
		return false;
	}
    if (cntl.value.length >= 160)
    {
        return false;
    }
}
function CutTooLong (cntl)
{
    cntl.value = cntl.value.substring(0, 160);
}
function HideShowSections()
{

	var combo = document.getElementById("statelem");
    var section = document.getElementById('periodOptions');
    var przewoznik = document.getElementById('przewoznik');
	var optDay = document.getElementById('opt1');
    
    if (null === combo)
        return false;
    
	    
    //TODO
    //switch (combo.options[combo.selectedIndex].value)
    
	if (combo.options[combo.selectedIndex].id == "----")
	{
		document.getElementById("potwierdz").disabled = true;

	}
	else
	{
		document.getElementById("potwierdz").disabled = false;
	}
    
	if (combo.options[combo.selectedIndex].id == "punkt 1")
	{
		section.style.display = '';
        optDay.style.display = '';
        przewoznik.style.display = 'none';
	}
    
	if (combo.options[combo.selectedIndex].id == "punkt 2")
	{
	    section.style.display = 'none';
        przewoznik.style.display = 'none';
	}
	if (combo.options[combo.selectedIndex].id == "punkt 3")
	{
		optDay.style.display = 'none';
		section.style.display = '';
        przewoznik.style.display = 'none';
    }
    if (combo.options[combo.selectedIndex].id == "punkt 4")
    {
        section.style.display = 'none';
        przewoznik.style.display = '';
    }
    if (combo.options[combo.selectedIndex].id == "punkt 5")
    {
        section.style.display = 'none';
        przewoznik.style.display = '';
    }
    
}
function checkdates(ev)
{
	var dateFrom = document.getElementById("dateFrom");
	var dateTo = document.getElementById("dateTo");
	if (dateFrom.value.length < 10 || dateTo.value.length < 10)
	{
		//we have some missing information, cancel postback
		ev.returnValue = false;
		alert("Bez podania zakresu dat nie mo¿na kontynuowaæ.");
        
        return false;
	}
	else
	{
		if (dateFrom.value >= dateTo.value)
		{
			//we have some missing information, cancel postback
			ev.returnValue = false;
			alert("Zakres dat jest b³êdnie sformu³owany.");
            
            return false;
		}
	}
}
//onscroll zapamietywac pod danym url wartosc
function SaveScroll (url_str, pos_str)
{
	//alert(url_str);
	//alert(pos_str);
	//if (!(navigator.userAgent.indexOf('MSIE') > -1))
	//{
		Set_Cookie(hex_md5(url_str), pos_str, 1, '', '', '');
	//}
}
//onload odczytywac - jak jest uzyc
//function AutoScrollDown (url_str)
function AutoScrollDown (div_arr)
{
	//if (!(navigator.userAgent.indexOf('MSIE') > -1))
	for (var i = 0;i < div_arr.length; i++)
	{
		var position_top = Get_Cookie(hex_md5(div_arr[i]));
		if (position_top != null)
		{
			document.getElementById(div_arr[i]).scrollTop = position_top;
		}
	}
}
function AutoScrollDownCentral (url_str)
{
	var position_top = Get_Cookie(hex_md5(url_str));
	//alert(position_top);
	if (position_top != null)
	{
		parent.frames[2].document.body.scrollTop = position_top;
	}
}

function Set_Cookie( name, value, expires, path, domain, secure ) 
{
	// set time, it's in milliseconds
	var today = new Date();
	today.setTime( today.getTime() );
	
	/*
	if the expires variable is set, make the correct 
	expires time, the current script below will set 
	it for x number of days, to make it for hours, 
	delete * 24, for minutes, delete * 60 * 24
	*/
	if ( expires )
	{
	expires = expires * 1000 * 60 * 60; // * 24
	}
	var expires_date = new Date( today.getTime() + (expires) );
	
	document.cookie = name + "=" +escape( value ) +
	( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) + 
	( ( path ) ? ";path=" + path : "" ) + 
	( ( domain ) ? ";domain=" + domain : "" ) +
	( ( secure ) ? ";secure" : "" );
}

function Get_Cookie( check_name ) 
{
	// first we'll split this cookie up into name/value pairs
	// note: document.cookie only returns name=value, not the other components
	var a_all_cookies = document.cookie.split( ';' );
	var a_temp_cookie = '';
	var cookie_name = '';
	var cookie_value = '';
	var b_cookie_found = false; // set boolean t/f default f
	
	for ( i = 0; i < a_all_cookies.length; i++ )
	{
		// now we'll split apart each name=value pair
		a_temp_cookie = a_all_cookies[i].split( '=' );
		
		
		// and trim left/right whitespace while we're at it
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');
	
		// if the extracted name matches passed check_name
		if ( cookie_name == check_name )
		{
			b_cookie_found = true;
			// we need to handle case where cookie has no value but exists (no = sign, that is):
			if ( a_temp_cookie.length > 1 )
			{
				cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
			}
			// note that in cases where cookie is initialized but no value, null is returned
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = '';
	}
	if ( !b_cookie_found )
	{
		return null;
	}
}
var hexcase = 0;  /* hex output format. 0 - lowercase; 1 - uppercase        */
var b64pad  = ""; /* base-64 pad character. "=" for strict RFC compliance   */
var chrsz   = 8;  /* bits per input character. 8 - ASCII; 16 - Unicode      */

/*
 * These are the functions you'll usually want to call
 * They take string arguments and return either hex or base-64 encoded strings
 */
function hex_md5(s){ return binl2hex(core_md5(str2binl(s), s.length * chrsz));}
function b64_md5(s){ return binl2b64(core_md5(str2binl(s), s.length * chrsz));}
function str_md5(s){ return binl2str(core_md5(str2binl(s), s.length * chrsz));}
function hex_hmac_md5(key, data) { return binl2hex(core_hmac_md5(key, data)); }
function b64_hmac_md5(key, data) { return binl2b64(core_hmac_md5(key, data)); }
function str_hmac_md5(key, data) { return binl2str(core_hmac_md5(key, data)); }

/*
 * Perform a simple self-test to see if the VM is working
 */
function md5_vm_test()
{
  return hex_md5("abc") == "900150983cd24fb0d6963f7d28e17f72";
}

/*
 * Calculate the MD5 of an array of little-endian words, and a bit length
 */
function core_md5(x, len)
{
  /* append padding */
  x[len >> 5] |= 0x80 << ((len) % 32);
  x[(((len + 64) >>> 9) << 4) + 14] = len;

  var a =  1732584193;
  var b = -271733879;
  var c = -1732584194;
  var d =  271733878;

  for(var i = 0; i < x.length; i += 16)
  {
    var olda = a;
    var oldb = b;
    var oldc = c;
    var oldd = d;

    a = md5_ff(a, b, c, d, x[i+ 0], 7 , -680876936);
    d = md5_ff(d, a, b, c, x[i+ 1], 12, -389564586);
    c = md5_ff(c, d, a, b, x[i+ 2], 17,  606105819);
    b = md5_ff(b, c, d, a, x[i+ 3], 22, -1044525330);
    a = md5_ff(a, b, c, d, x[i+ 4], 7 , -176418897);
    d = md5_ff(d, a, b, c, x[i+ 5], 12,  1200080426);
    c = md5_ff(c, d, a, b, x[i+ 6], 17, -1473231341);
    b = md5_ff(b, c, d, a, x[i+ 7], 22, -45705983);
    a = md5_ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
    d = md5_ff(d, a, b, c, x[i+ 9], 12, -1958414417);
    c = md5_ff(c, d, a, b, x[i+10], 17, -42063);
    b = md5_ff(b, c, d, a, x[i+11], 22, -1990404162);
    a = md5_ff(a, b, c, d, x[i+12], 7 ,  1804603682);
    d = md5_ff(d, a, b, c, x[i+13], 12, -40341101);
    c = md5_ff(c, d, a, b, x[i+14], 17, -1502002290);
    b = md5_ff(b, c, d, a, x[i+15], 22,  1236535329);

    a = md5_gg(a, b, c, d, x[i+ 1], 5 , -165796510);
    d = md5_gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
    c = md5_gg(c, d, a, b, x[i+11], 14,  643717713);
    b = md5_gg(b, c, d, a, x[i+ 0], 20, -373897302);
    a = md5_gg(a, b, c, d, x[i+ 5], 5 , -701558691);
    d = md5_gg(d, a, b, c, x[i+10], 9 ,  38016083);
    c = md5_gg(c, d, a, b, x[i+15], 14, -660478335);
    b = md5_gg(b, c, d, a, x[i+ 4], 20, -405537848);
    a = md5_gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
    d = md5_gg(d, a, b, c, x[i+14], 9 , -1019803690);
    c = md5_gg(c, d, a, b, x[i+ 3], 14, -187363961);
    b = md5_gg(b, c, d, a, x[i+ 8], 20,  1163531501);
    a = md5_gg(a, b, c, d, x[i+13], 5 , -1444681467);
    d = md5_gg(d, a, b, c, x[i+ 2], 9 , -51403784);
    c = md5_gg(c, d, a, b, x[i+ 7], 14,  1735328473);
    b = md5_gg(b, c, d, a, x[i+12], 20, -1926607734);

    a = md5_hh(a, b, c, d, x[i+ 5], 4 , -378558);
    d = md5_hh(d, a, b, c, x[i+ 8], 11, -2022574463);
    c = md5_hh(c, d, a, b, x[i+11], 16,  1839030562);
    b = md5_hh(b, c, d, a, x[i+14], 23, -35309556);
    a = md5_hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
    d = md5_hh(d, a, b, c, x[i+ 4], 11,  1272893353);
    c = md5_hh(c, d, a, b, x[i+ 7], 16, -155497632);
    b = md5_hh(b, c, d, a, x[i+10], 23, -1094730640);
    a = md5_hh(a, b, c, d, x[i+13], 4 ,  681279174);
    d = md5_hh(d, a, b, c, x[i+ 0], 11, -358537222);
    c = md5_hh(c, d, a, b, x[i+ 3], 16, -722521979);
    b = md5_hh(b, c, d, a, x[i+ 6], 23,  76029189);
    a = md5_hh(a, b, c, d, x[i+ 9], 4 , -640364487);
    d = md5_hh(d, a, b, c, x[i+12], 11, -421815835);
    c = md5_hh(c, d, a, b, x[i+15], 16,  530742520);
    b = md5_hh(b, c, d, a, x[i+ 2], 23, -995338651);

    a = md5_ii(a, b, c, d, x[i+ 0], 6 , -198630844);
    d = md5_ii(d, a, b, c, x[i+ 7], 10,  1126891415);
    c = md5_ii(c, d, a, b, x[i+14], 15, -1416354905);
    b = md5_ii(b, c, d, a, x[i+ 5], 21, -57434055);
    a = md5_ii(a, b, c, d, x[i+12], 6 ,  1700485571);
    d = md5_ii(d, a, b, c, x[i+ 3], 10, -1894986606);
    c = md5_ii(c, d, a, b, x[i+10], 15, -1051523);
    b = md5_ii(b, c, d, a, x[i+ 1], 21, -2054922799);
    a = md5_ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
    d = md5_ii(d, a, b, c, x[i+15], 10, -30611744);
    c = md5_ii(c, d, a, b, x[i+ 6], 15, -1560198380);
    b = md5_ii(b, c, d, a, x[i+13], 21,  1309151649);
    a = md5_ii(a, b, c, d, x[i+ 4], 6 , -145523070);
    d = md5_ii(d, a, b, c, x[i+11], 10, -1120210379);
    c = md5_ii(c, d, a, b, x[i+ 2], 15,  718787259);
    b = md5_ii(b, c, d, a, x[i+ 9], 21, -343485551);

    a = safe_add(a, olda);
    b = safe_add(b, oldb);
    c = safe_add(c, oldc);
    d = safe_add(d, oldd);
  }
  return Array(a, b, c, d);

}

/*
 * These functions implement the four basic operations the algorithm uses.
 */
function md5_cmn(q, a, b, x, s, t)
{
  return safe_add(bit_rol(safe_add(safe_add(a, q), safe_add(x, t)), s),b);
}
function md5_ff(a, b, c, d, x, s, t)
{
  return md5_cmn((b & c) | ((~b) & d), a, b, x, s, t);
}
function md5_gg(a, b, c, d, x, s, t)
{
  return md5_cmn((b & d) | (c & (~d)), a, b, x, s, t);
}
function md5_hh(a, b, c, d, x, s, t)
{
  return md5_cmn(b ^ c ^ d, a, b, x, s, t);
}
function md5_ii(a, b, c, d, x, s, t)
{
  return md5_cmn(c ^ (b | (~d)), a, b, x, s, t);
}

/*
 * Calculate the HMAC-MD5, of a key and some data
 */
function core_hmac_md5(key, data)
{
  var bkey = str2binl(key);
  if(bkey.length > 16) bkey = core_md5(bkey, key.length * chrsz);

  var ipad = Array(16), opad = Array(16);
  for(var i = 0; i < 16; i++)
  {
    ipad[i] = bkey[i] ^ 0x36363636;
    opad[i] = bkey[i] ^ 0x5C5C5C5C;
  }

  var hash = core_md5(ipad.concat(str2binl(data)), 512 + data.length * chrsz);
  return core_md5(opad.concat(hash), 512 + 128);
}

/*
 * Add integers, wrapping at 2^32. This uses 16-bit operations internally
 * to work around bugs in some JS interpreters.
 */
function safe_add(x, y)
{
  var lsw = (x & 0xFFFF) + (y & 0xFFFF);
  var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
  return (msw << 16) | (lsw & 0xFFFF);
}

/*
 * Bitwise rotate a 32-bit number to the left.
 */
function bit_rol(num, cnt)
{
  return (num << cnt) | (num >>> (32 - cnt));
}

/*
 * Convert a string to an array of little-endian words
 * If chrsz is ASCII, characters >255 have their hi-byte silently ignored.
 */
function str2binl(str)
{
  var bin = Array();
  var mask = (1 << chrsz) - 1;
  for(var i = 0; i < str.length * chrsz; i += chrsz)
    bin[i>>5] |= (str.charCodeAt(i / chrsz) & mask) << (i%32);
  return bin;
}

/*
 * Convert an array of little-endian words to a string
 */
function binl2str(bin)
{
  var str = "";
  var mask = (1 << chrsz) - 1;
  for(var i = 0; i < bin.length * 32; i += chrsz)
    str += String.fromCharCode((bin[i>>5] >>> (i % 32)) & mask);
  return str;
}

/*
 * Convert an array of little-endian words to a hex string.
 */
function binl2hex(binarray)
{
  var hex_tab = hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
  var str = "";
  for(var i = 0; i < binarray.length * 4; i++)
  {
    str += hex_tab.charAt((binarray[i>>2] >> ((i%4)*8+4)) & 0xF) +
           hex_tab.charAt((binarray[i>>2] >> ((i%4)*8  )) & 0xF);
  }
  return str;
}

/*
 * Convert an array of little-endian words to a base-64 string
 */
function binl2b64(binarray)
{
  var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
  var str = "";
  for(var i = 0; i < binarray.length * 4; i += 3)
  {
    var triplet = (((binarray[i   >> 2] >> 8 * ( i   %4)) & 0xFF) << 16)
                | (((binarray[i+1 >> 2] >> 8 * ((i+1)%4)) & 0xFF) << 8 )
                |  ((binarray[i+2 >> 2] >> 8 * ((i+2)%4)) & 0xFF);
    for(var j = 0; j < 4; j++)
    {
      if(i * 8 + j * 6 > binarray.length * 32) str += b64pad;
      else str += tab.charAt((triplet >> 6*(3-j)) & 0x3F);
    }
  }
  return str;
}

function htmlentities(str) {
    var textarea = document.createElement("textarea");
    textarea.innerHTML = str;
    return textarea.innerHTML;
}
function htmlentities_decode(str) {
    var textarea = document.createElement("textarea");
    textarea.innerHTML = str;
    return textarea.value;
}