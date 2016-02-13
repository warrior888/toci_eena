//dodac metode utilowa w php headers czy cos i tam dodanie js do kazdej strony
//zeby dodawac calosc, a nie po 10 razy to samo powielac
var Utils = function () {

    var getElementById = this.getElementById = function getElementById (elementId) {
        var element = document.getElementById(elementId);
        if (element !== null && element != 'undefined') {
            return element;
        }
        return null;
    };

    this.setElementValue = function (elementId, elValue) {
        var element = getElementById(elementId);
        if (element !== null) {
            element.value = elValue;
        }
    };
    
    this.getElementValue = function (elementId) {
        var element = getElementById(elementId);
        if (element !== null) {
            return element.value;
        }
        return null;
    };
    
    this.focusElement = function (elementId) {
        var element = getElementById(elementId);
        if (element !== null) {
            element.focus();
        }
    };
    //zastosowane w walidacjach
    this.getEventKey = function (event) {
        return (event.keyCode) ? event.keyCode : event.which;
    };
    
    //create onload function preventing any hidden from being empty
    this.setHiddenOnLoad = function(hidArray, selArray)
    {
        var hiddens = hidArray.split(",");
        var selects = selArray.split(",");
        for (var i = 0; i < hiddens.length; i++)
        {
            if ((hidEl = getElementById(hiddens[i])) !== null) {
                var selEl =  getElementById(selects[i]);
                hidEl.value = selEl.options[selEl.selectedIndex].id;
            }
        }
    };
    
    this.setHiddenFromSelect = function(select, hiddenId)
    {
        var hidden = getElementById(hiddenId);
        if (select.selectedIndex >= 0)
            hidden.value = select.options[select.selectedIndex].id;
            
        //clearAutoFill();
    };
    
    this.setHiddenFromSelectLabel = function(select, hiddenId)
    {
        var hidden = getElementById(hiddenId);
        if (select.selectedIndex >= 0)
            hidden.value = select.options[select.selectedIndex].title;
    };
    
    this.setHiddenValue = function(value, hiddenId)
    {
        var hidden = getElementById(hiddenId);
        hidden.value = value;
    };
    
    this.resetSelect = function (selectId, hiddenId)
    {
        var hidden = getElementById(hiddenId);
        var select = getElementById(selectId);
        
        select.selectedIndex = 0;
        hidden.value = '';
    };
    
    var fieldAutoFillHelper = 'id_temp_sel_helper';       //obiekt ktory zawiera wpisane litery
    var fieldAutoFillObjIdent = 'id_sel_autofill';  //obiekt identyfikujacy pole dla ktorego odbywa sie autofill
    
    //klawisz
    this.selectAutoFill = function (parametr, evt, extraDiv)
    {
    //refactor bzdurnych ifow, stabilnosc dzialania samej metody zapewniona
        var tab = new Array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"); 
        var tab_p = new Array("¡", "Æ", "Ê", "£", "Ó", "¦", "¬", "¯", "Ñ");
        var tekst = getElementById(fieldAutoFillHelper); //obiekt ktory zawiera wpisane litery
        var pole = getElementById(fieldAutoFillObjIdent); 
        if (pole.value != parametr.name)
        {
            tekst.value = "";
            pole.value = parametr.name;
        }
        var i = 0;
        var k = this.getEventKey(evt); //kod nacisnietego klawisza
        if (k != 9 && (k < 16 || k > 18) && (k < 37 || k > 40))
        {
            if (k == 46)
            {
                tekst.value = tekst.value.substring(0, tekst.value.length - 1); //kasowanie ost wpisanego znaku po nacisnieciu "."
            }
            else if (k == 61)
            {
                tekst.value = tekst.value.substring(0, tekst.value.length); //nic nie jest kasowane, explorer nie wynajduje nic co by sie zaczynalo na =, wiec zostaje to co zaznaczyl skrypt i tyle
            }
            else if ((k == 260) || (k == 261))
            {
                tekst.value += tab_p[0];
            }
            else if ((k == 262) || (k == 263))
            {
                tekst.value += tab_p[1];
            }
            else if ((k == 280) || (k == 281))
            {
                tekst.value += tab_p[2];
            }
            else if ((k == 321) || (k == 322))
            {
                tekst.value += tab_p[3];
            }
            else if ((k == 211) || (k == 243))
            {
                tekst.value += tab_p[4];
            }
            else if ((k == 346) || (k == 347))
            {
                tekst.value += tab_p[5];
            }
            else if ((k == 377) || (k == 378))
            {
                tekst.value += tab_p[6];
            }
            else if ((k == 379) || (k == 380))
            {
                tekst.value += tab_p[7];
            }
            else if ((k == 323) || (k == 324))
            {
                tekst.value += tab_p[8];
            }
            else if (k != 13)
            {
                if (k >= 97)
                {
                    k -= 97;
                }
                else
                {
                    k -= 65;
                  }
                tekst.value += tab[k];
            }
            var sel = parametr; //combo dla ktorego zostal wywolany skrypt

            var t = ""; //zmienna pomocnicza w ktorej trzymamy kolejne wartosci combobox'a
            for (i = 0; i < sel.length; i++)
            {
                t = sel.options[i].value;
                t = t.toLowerCase(); //scigamy do malych liter
                tekst.value = tekst.value.toLowerCase();
                //find the first match
                if (tekst.value == t.substring(0, tekst.value.length)) //spr czy poczatek aktualenj wartosci z combo jest taki sam jak wpisany tekst
                {
                    tekst.value = tekst.value.toUpperCase(); //zwiekszamy tekst wpisany
                    if (extraDiv)
                        document.getElementById(extraDiv).innerHTML = sel.options[i].innerHTML;// do diva, jesli jest
                    
                    sel.options[i].innerHTML = t.substring(0, tekst.value.length).toUpperCase() + sel.options[i].value.substring(tekst.value.length, sel.options[i].value.length); //zamieniamy wartosc combo 
                    //sel.options[i].innerHTML = 'test' + i;
                    sel.selectedIndex = i; //ustawiamy wartosc na znaleziona
                    
                    //finish loop
                    i = sel.length; 
                }
            }
        }
    };
    
    this.trim = function (textToTrim)
    {
        var newText = textToTrim;
        var textLength = textToTrim.length;
        var i = 0;
        newText = textToTrim;
        //allow iteration to a number of the sting length
        //while ((newText.length > 1) && (newText.substring(newText.length - 2, newText.length - 1) == " "))
        while (i <= textLength)
        {
            if ((newText.substring(newText.length - 1, newText.length) == " "))
            {
                newText = newText.substring(0, newText.length - 1);
            }
            else
            {
                break;
            }
            i++;
        }
        textLength = newText.length;
        i = 0;
        //while ((newText.length > 1) && (newText.substring(0, 1) == " "))
        while (i <= textLength)
        {
            if ((newText.substring(0, 1) == " "))
            {
                newText = newText.substring(1, newText.length);
            }
            else
            {
                break;
            }
            i++;
        }
        //region get rid of middle spaces in text
        var arrPieces = newText.split(" ");
        var and = 0;
        var result = "";
        for (var i = 0; i < arrPieces.length; i++)
        {
            if (arrPieces[i] != "")
            {
                if (and == 0)
                {
                    and = 1;
                }
                else
                {
                    result += " ";
                }
                result += arrPieces[i];
            }
        }
        return result;
    };
    
    //wyczysc hidden podpowiedzi
    this.clearAutoFill = function () //var
    {
        var tekst = getElementById(fieldAutoFillHelper);
        if (tekst)
            tekst.value = '';
    };
}

var utils = new Utils();