var target;
var targetId;
var targetLabel;


function GetCity (element, cityNameId, CityIdId, cityLabelId)
{
    if (validations.PostCodeCheck(element))//.length == 6
    {
        target = document.getElementById(cityNameId);
        targetId = document.getElementById(CityIdId);
        targetLabel = document.getElementById(cityLabelId);
        $.get ('pobierz_miejscowosc.php?kod=' + element.value, '', FillCity, 'html');
    }
}

function FillCity (miejscowosc)
{
    var popupDiv = document.getElementById('popup');
    if (miejscowosc.length < 1) {
        popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="600" height="500" frameborder="0" src="wybor_msc.php"></iframe></div>';
        popupDiv.style.display = ''; 
    }
    else {
        eval ('var obj = ' + miejscowosc);
        if (obj.length > 0) 
        {
            targetLabel.innerHTML = target.value = obj[0].miejscowosc;
            targetId.value = obj[0].id;
        }
        else {
            popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="600" height="500" frameborder="0" src="wybor_msc.php"></iframe></div>';
            popupDiv.style.display = '';
        }
    }
}

function FillLanguages () 
{
    document.getElementById("jezykiObce").innerHTML = '';
    var popupDiv = document.getElementById('popup'); 
    popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="600" height="500" frameborder="0" src="znane_jezyki_ankieta.php"></iframe></div>';
    popupDiv.style.display = '';
}

function FillDriversLicense () 
{
    document.getElementById("prawoJazdy").innerHTML = '';
    var popupDiv = document.getElementById('popup'); 
    popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="600" height="500" frameborder="0" src="prawo_jazdy_ankieta.php"></iframe></div>';
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
    document.getElementById("dodatkoweUmiejetnosci").innerHTML = '';
    var popupDiv = document.getElementById('popup'); 
    popupDiv.innerHTML = '<div class="inPopup" id="custom_popup" name="custom_popup"><iframe width="800" height="600" frameborder="0" src="wybor_umiejetnosci.php"></iframe></div>';
    popupDiv.style.display = '';
}
function CheckAll ()
{
    var inputs = document.getElementsByTagName('input');
    var psrequired = false;
    
    for (var field in inputs)
    {
        if (typeof inputs[field].getAttribute !== 'undefined')
        {        
            var classes = inputs[field].className;

            if (classes && classes.match('required'))
            {
                if (classes.match('psrequired'))
                if (classes.match('psrequired')[0] === 'psrequired')
                {
                    if (inputs[field].value.length > 0)
                        psrequired = psrequired | true;
                        
                    continue;
                }
                
                if (classes.match('required')[0] === 'required')
                {

                    if (inputs[field].value.length === 0)
                    {
                        //console.log('field empty', field, inputs[field]);
                        alert('Formularz nie zosta³ wype³niony.');
                        return false;
                    }
                }
            }
        }
    }
    
    if (false === psrequired)
    {
        alert('Uzupe³nij przynajmniej jedno zielone pole.');
        return false;
    }
    
    if (document.getElementById('zgoda').checked === false)
    {
        alert('Zgoda na przetwarzanie danych jest konieczna.');
        return false;
    }
    
    return true;
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
function checkCookiePresence (cookieName)
{
    var cookies = document.cookie;
    
    console.log('cookies', cookies);
    
    var isCookie = cookies.indexOf(cookieName);
    
    console.log('cookies', isCookie);
    
    return isCookie >= 0;
}

function alertRegFormCookie(cookieName, messg, placeHolder, hideElements)
{
    var isCookie = checkCookiePresence(cookieName);
    
    if (isCookie)
    {
        return true;
    }
    
    //console.log('wtf', cookieName, messg, placeHolder);
    var placeMsg = utils.getElementById(placeHolder);
    
    var elements = hideElements.split(',');
    for (element in elements)
    {
        console.log('wtf', elements[element]);
        utils.getElementById(elements[element]).style.display = 'none';
    }
    
    placeMsg.innerHTML = messg;
    
    return false;
}