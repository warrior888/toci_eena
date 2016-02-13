	function wpis_rok (obj){
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
	function wpis_miesiac (obj){
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
	function wpis_dzien (obj){
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
	function test (co)
        {
            var wzor = /^(1|2){1}(0|9){1}[0-9]{2}(\-){1}(01|02|03|04|05|06|07|08|09|10|11|12){1}(\-){1}(01|02|03|04|05|06|07|08|09|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31){1}$/;
            if (!wzor.test(co.value))
            {
		if(co.value != "")
		{
              		alert("Podaj date w formacie \"RRRR-MM-DD\"");
	       		co.focus();
              		co.value = "";
			return 0;
		}
            }
            else
            {
              return co.value;
            }
        }
	function telefon_kom (ob)
        {
            var wzor = /^(50|51|60|66|88){1}[0-9]{7}$/;
            if (!wzor.test(ob.value))
            {
		if(ob.value != "")
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
		if(ob.value != "")
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
		if(ob.value != "")
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
	function sprawdz_nazwisko (ob)
        {
            var wzor = /^[A-Z,Ó,¦,£,¯,¬,Æ,Ñ]{1}[a-z,\-,\ ,ê,ó,±,¶,³,¿,¼,æ,ñ]{2,29}$/;
            if (!wzor.test(ob.value))
            {
		if(ob.value != "")
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
            var wzor = /^[A-Z,0-9,Ó,¦,£,¯,¬,Æ,Ñ,a-z,\-,\ ,ê,ó,±,¶,³,¿,¼,æ,ñ,\.,\/]{5,30}$/;
            if (!wzor.test(ob.value))
            {
		if(ob.value != "")
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
		if(ob.value != "")
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
		if(ob.value != "")
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
		if(ob.value != "")
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
            var wzor = /^(1|2){1}[0-9]{8}$/;
            if (!wzor.test(ob.value))
            {
		if(ob.value != "")
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
            var wzor = /^[A-Z,Ó,¦,£,¯,¬,Æ,Ñ]{1}[A-Z,a-z,\.,\ ,ê,ó,±,¶,³,¿,¼,æ,ñ]{2,44}$/;
            if (!wzor.test(ob.value))
            {
		if(ob.value != "")
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
		if(ob.value != "")
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
            var wzor = /^[A-Z,Ó,¦,£,¯,¬,Æ,Ñ]{1}[a-z,\ ,ê,ó,±,¶,³,¿,¼,æ,ñ]{2,29}$/;
            if (!wzor.test(ob.value))
            {
		if(ob.value != "")
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
		if(ob.value != "")
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
	var wynik = "";
	var zrodlo = tekst.value;
	for (var i = 0; i <= zrodlo.length; i++)
	{
		//alert(zrodlo.substring(i,i + 1));
		if (zrodlo.substring(i,i + 1) == '*')
		{
			wynik += '%';
		}
		else
		{
			wynik += zrodlo.substring(i,i + 1);
		}
	}
	tekst.value = wynik;
	//alert(wynik);
	return wynik;
}
function ustaw_hist_zatr()
    {
        var f = document.getElementById("statusy");
        var e = opener.window.document.getElementById("1");
        e.selectedIndex = f.selectedIndex - 1;
        var f = document.getElementById("tygodnie");
        var e = opener.window.document.getElementById("3");
        e.value = f.value;
        //window.close();
    }
function umow_pasywnego()
    {
        var f = document.getElementById("status");
        var e = opener.window.document.getElementById("1");
        e.selectedIndex = f.selectedIndex - 1;
        var f = document.getElementById("ilosc_tyg");
        var e = opener.window.document.getElementById("3");
        e.value = f.value;
        //window.close();
    }
function podm_hist_zatr()
    {
        var f = document.getElementById("status");
        var e = opener.window.document.getElementById("1");
        e.selectedIndex = f.selectedIndex;
        var f = document.getElementById("ilosc_tyg");
        var e = opener.window.document.getElementById("3");
        e.value = f.value;
        //window.close();
    }
function podm_status()
    {
        var f = document.getElementById("status");
        var e = opener.window.document.getElementById("1");
        e.selectedIndex = f.selectedIndex;
        //window.close();
    }
function policz (obj)
{
	var ile_widocznych = 0;
	//box = new object;
	tabela = new Array ("imie","nazwisko","plec","data_urodzenia","msc_urodzenia","msc","ulica","kod","wyksztalcenie","zawod","prawo_jazdy","umiejetnosci","konsultant","data_zgloszenia","status","charakter","paszport","termin_wyjazdu","ilosc_tyg","ostatni_kontakt","os_dzwoniaca","telefon","komorka","inny","email","jezyk","pref","antyp","znani_klienci","biuro","msc_odjazdu","bilet","zadania_dnia","korespondencja","rodz_kor","data_rekl","rok_jaar","klient_jaar","ankieta","zrodlo");
	//alert(tabela[25]);
	//alert(obj.name);
	for (var j=0; j<38; j++)
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
		//alert("a");
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
			//alert(s.name);
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
		//alert(wa.name);
	}
// Create scrolling variable if it doesn't exist
if (!Scrolling) var Scrolling = {};

//Scroller constructor
Scrolling.Scroller = function (o, w, h, t) {
	//get the container
	var list = o.getElementsByTagName("div");
	for (var i = 0; i < list.length; i++) {
		if (list[i].className.indexOf("Scroller-Containertop") > -1) {
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
		_twidth  = o.offsetWidth
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
			if (list[i].className.indexOf("Scroller-Containertop") > -1) {
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

function handle()
{
	//alert(event.wheelDelta);
	if (event.wheelDelta > 0)
	{
		scroller.ScrollOnce(0, -15);
		//setTimeout( function () { scroller.stopScroll(); },40);
	}
	else
	{
		scroller.ScrollOnce(0, 15);
		//setTimeout( function () { scroller.stopScroll(); },40);
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

