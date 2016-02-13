//walidacje
var Validations = function () {
     //private variables and methods
     var commonChars = Array(8, 9, 13, 35, 36, 37, 39, 46); //40 - strzalka w dol i (, 38 - strzalka w gore i &, //35 - end i #, 36 - $ i home, 37 - strzalka w lewo i %, 
    
     var intAllowed = new Object();
     var stringAllowed = new Object();
     var dateAllowed = new Object();
     
     var checkDatePieces = function (year, month, day)
     {
        if ((year < 1900 || year > 2200) || (month > 12) || (day > 31))
        {
            return false;
        }
        //if the month is february, april or june (below 7 and even number)
        if (month < 7 && (month % 2) == 0)
        {
            //there is no 31 in those months
            if (day > 30)
            {
                return false;
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
                        return false;
                    }
                }
                //the year is not a leap year
                else
                {
                    //day is not higher than 28
                    if (day > 28)
                    {
                        return false;
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
                return false;
            }
        }
        
        return true;
     };
     
     var getDate = function (dateString)
     {
        var date = new Date(dateString);
        //Invalid Date
        //if (isNaN(date.getFullYear()) && date.toString() !== "Invalid Date")
        //{

            var dateParts = dateString.split('-');
            if (dateParts.length !== 3)
            {
                return 'Invalid Date';
            }
            
            var year = dateParts[0];
            var month = dateParts[1];
            var day = dateParts[2];
            
            
            var validDate = checkDatePieces(year, month, day);
            
            if (false === validDate)
                return 'Invalid Date';
                
            var date = new Date(year, month, day);
            
            //if (year != date.getFullYear() || month != (date.getMonth() + 1))
            //    return 'Invalid Date';
        //}
        
        return date;
     };
             
     var prepareData = function ()
     {        
        stringAllowed['c_32'] = 32;
        stringAllowed['c_45'] = dateAllowed['c_45'] = 45; //-
        stringAllowed['c_44'] = 44;
        dateAllowed['c_32'] = 32; //--> <--
        
        for (var i = 48; i < 58; i++)
        {
            intAllowed['c_' + i.toString()] = i;
            dateAllowed['c_' + i.toString()] = i;
        }
        
        for (var i = 65; i < 91; i++)
        {  
            stringAllowed['c_' + i.toString()] = i;
        }
        for (var i = 97; i < 123; i++)
        {
            stringAllowed['c_' + i.toString()] = i;
        }
            
        for (var i = 0; i < commonChars.length; i++)
        {
            
            dateAllowed['c_' + commonChars[i].toString()] = stringAllowed['c_' + commonChars[i].toString()] = intAllowed['c_' + commonChars[i].toString()] = commonChars[i];
        }

        //return {'int': intAllowed, 'string': stringAllowed, 'date': dateAllowed};
    };
    prepareData();
    
    //public
    this.OnlyNumber = function(obj, event)
    {
        var k = utils.getEventKey(event);
        //alert(k);
        if (intAllowed['c_' + k.toString()])
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    this.DateValidate = function (cntl, ev)
    {
        var k = utils.getEventKey(ev);
        //alert(k);
        if (dateAllowed['c_' + k.toString()])
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    this.TextValidate = function  (cntl, ev)
    {
        //321, 322 Å‚ Å; 260, 261 Â± Â±; 280, 281 Ä˜ Ä™; 211, 243 Ã“ Ã³; 346, 347 Â¦ Â¶, 379, 380 Å» Å¼; 377, 378 Â¬ Ä½; 262, 263 Ä† Ä‡; 323, 324 Åƒ Å„;
        var k = utils.getEventKey(ev);
        //alert(k);
        if (stringAllowed['c_' + k.toString()] || k > 191)
        {
            return true;
        }
        else
        {
            return false;
        }
    };
    
    
    //blur checks
    
    this.PostCodeCheck = function (cntl, event)
    {
        var wzor = /^[0-9]{2}\-[0-9]{3}$/;
        if (!wzor.test(cntl.value))
        {
            if(cntl.value !== "")
            {
                alert("B³êdnie podany kod pocztowy.");
                cntl.focus();
                cntl.value = "";
                return false;
            }
            
            return true;
        }
        else
        {
            return cntl.value;
        }
    };
    
    this.DateCheck = function (cntl, event)
    {
        if (cntl.value.length < 10)
            return false;
            
        var provDate = getDate(cntl.value); 
        
        if ("Invalid Date" === provDate.toString())
        {
            return false;
        }
        
        return true;
    };
    
    this.FutureDateCheck = function (cntl, event)
    {
        if (false === this.DateCheck(cntl, event))
        {
            alert('Podano niew³a¶ciw± datê.');
            cntl.value = '';
            return false;
        }
        
        var currentDate = new Date();
        var date = getDate(cntl.value);

        if (cntl.value.length > 0 && date.getTime() < currentDate.getTime())
        {
            alert('Podano datê z przesz³o¶ci.');
            cntl.value = '';
            return false;
        }
        
        return true;
    };
    
    this.PastDateCheck = function (cntl, event)
    {
        if (false === this.DateCheck(cntl, event))
        {
            alert('Podano niew³a¶ciw± datê.');
            cntl.value = '';
            return false;
        }
        
        var currentDate = new Date();
        var date = getDate(cntl.value);

        if (cntl.value.length > 0 && date.getTime() > currentDate.getTime())
        {
            alert('Podano datê z przysz³o¶ci.');
            cntl.value = '';
            return false;
        }
        
        return true;
    };
    
    this.BirthDateCheck = function (cntl, event)
    {
        if (false === this.DateCheck(cntl, event))
        {
            alert('Podano niew³a¶ciw± datê.');
            cntl.value = '';
            return false;
        }
        
        var currentDate = new Date();
        var date = getDate(cntl.value);

        if (cntl.value.length > 0 && (date.getFullYear() + 14) > currentDate.getFullYear())
        {
            alert('Podano z³± datê urodzenia.');
            cntl.value = '';
            return false;
        }
        
        return true;
    };
    
    this.EmailCheck = function (cntl, event)
    {
        var wzor = /^[0-9,A-Z,a-z,.,\,,_,-]{1,35}(\@){1}[0-9,A-Z,a-z,.,-]{1,35}(\.){1}[A-Z,a-z]{1,5}$/;
        if (!wzor.test(cntl.value))
        {
            if(cntl.value !== "")
            {
                alert("Podany E-mail wygl±da na nieprawid³owy.");
                cntl.focus();
                cntl.value = "";
                return 0;
            }
        }
        else
        {
            return cntl.value;
        }
    };
    
    this.PhoneValidate = function (cntl, event, isCell)
    {
        var wzor = /^[1-9]{1}[0-9]{8}$/;
        
        if (isCell !== undefined)
            var wzor = /^[5-8]{1}\d{8}$/;
        
        if (!wzor.test(cntl.value))
        {
            if(cntl.value !== "")
            {
                alert("Podany telefon jest nieprawid³owy.");
                cntl.focus();
                cntl.value = "";
                return 0;
            }
        }
        else
        {
            return cntl.value;
        }
    };
    
    this.ExtraPhoneValidate = function (cntl, event)
    {
        var wzor = /^[0-9]{8,16}$/;
        
        if (!wzor.test(cntl.value))
        {
            if(cntl.value !== "")
            {
                alert("Podany telefon jest nieprawid³owy.");
                cntl.focus();
                cntl.value = "";
                return 0;
            }
        }
        else
        {
            return cntl.value;
        }
    };
}

var validations = new Validations();