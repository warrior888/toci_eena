var target;
var targetId;



function GetCity (element)
{
    if (element.value.length == 6)
    {
        target = document.getElementById('Rcity');
        targetId = document.getElementById('id_Rcity_id');
        $.get ('pobierz_miejscowosc.php?kod=' + element.value, '', FillCity, 'html');
    }
}

function FillCity (miejscowosc)
{
    var popupDiv = document.getElementById('popup');
    if (miejscowosc.length < 1) {
        popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="600" height="600" frameborder="0" src="wybor_msc.php"></iframe></div>';
        popupDiv.style.display = ''; 
    }
    else {
        eval ('var obj = ' + miejscowosc);
        if (obj.length > 0) 
        {
            target.value = obj[0].miejscowosc;
            targetId.value = obj[0].id;
        }
        else {
            popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="600" height="600" frameborder="0" src="wybor_msc.php"></iframe></div>';
            popupDiv.style.display = '';
        }
    }
}

function FillLanguages () 
{
    document.getElementById("jezykiObce").innerHTML = '';
    var popupDiv = document.getElementById('popup'); 
    popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="600" height="600" frameborder="0" src="znane_jezyki_ankieta.php"></iframe></div>';
    popupDiv.style.display = '';
}

function FillDriversLicense () 
{
    document.getElementById("prawoJazdy").innerHTML = '';
    var popupDiv = document.getElementById('popup'); 
    popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="600" height="600" frameborder="0" src="prawo_jazdy_ankieta.php"></iframe></div>';
    popupDiv.style.display = '';
}

function FillFormerEmployees () 
{
    document.getElementById("poprzedniPracodawca").innerHTML = '';
    var popupDiv = document.getElementById('popup'); 
    popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="800" height="600" frameborder="0" src="poprzedni_pracodawca_ankieta.php"></iframe></div>';
    popupDiv.style.display = '';
}

function FillAdditionalSkills () 
{
    document.getElementById("DUmiejetnosci").innerHTML = '';
    var popupDiv = document.getElementById('popup'); 
    popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="800" height="600" frameborder="0" src="wybor_umiejetnosci.php"></iframe></div>';
    popupDiv.style.display = '';
}
function klawisz_ankieta()
        {       
            var tekst = document.getElementById("tekst_ankieta");                   
            var k = event.keyCode;
            if (event.altKey)
            {
                tekst.value += convertToASCIIPl(k);
            }
            else
            {
                if (k > 32)
                {
                    tekst.value += convertToASCII(k);
                }
                else if (k == 8)
                {
                    tekst.value = tekst.value.substring(0, tekst.value.length - 1);
                }
            }
        }
        function checkName()
        {
            //alert("a");   
        }
        function checkAll()
        {
            var all = true;
            var i;
            var tab = document.getElementsByTagName("input");
            for (i = 0; i < tab.length; i++)
            {
                if (tab[i].name.substring(0, 1) == "R")
                {
                    if (tab[i].value == "")
                    {
                        all = false;
                        break;   
                    }
                }
            }
            if (all)
            {
                all = checkTel();
            }
            if (all)
            {
                all = checkZgoda();
            }
            if (all)
            {
                document.getElementById("add").disabled = false;   
            }
            else
            {
                //alert(tab[i].name);
                document.getElementById("add").disabled = true;   
            }
            //alert(tab.length);
        }
        function checkZgoda()
        {
            if (document.getElementById("zgoda").checked)
            {
                return true;
            }   
            else
            {
                return false;   
            }
        }
        function checkTel()
        {
            if ((document.getElementById("tel_stac").value != "") || (document.getElementById("tel_kom").value != ""))   
            {
                return true;   
            }
            else
            {
                return false;
            }
        }
        function showHint(text)
        {
            document.getElementById("HintContainer").innerHTML = text;
        }