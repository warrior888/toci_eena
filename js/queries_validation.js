var QueriesValidations = function () {

    var commonChars = Array(8, 9, 32, 35, 36, 37, 38, 39, 40, 44, 46); //44 - , ;  
        
    var intAllowed = new Object();
    var stringAllowed = new Object();
    var dateAllowed = new Object();

    function prepareValidations ()
    {
        stringAllowed['c_45'] = dateAllowed['c_45'] = 45; //-
        dateAllowed['c_32'] = 32; //--> <--
        
        for (var i = 48; i < 58; i++)
        {
            intAllowed['c_' + i.toString()] = i;
            dateAllowed['c_' + i.toString()] = i;
            stringAllowed['c_' + i.toString()] = i;
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
    }

    prepareValidations();

    this.queriesValidateInt = function  (obj, event)
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
    }
    //to de facto validuje date range, poniewaz nigdzie nie bedzie dedykowanego pola date raczej
    this.queriesValidateDate = function  (obj, event)
    {
        var k = utils.getEventKey(event);
        //alert(k);
        if (dateAllowed['c_' + k.toString()])
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    //check if it is necessary
    this.PostCodeValidate = function (cntl, ev)
    {
        var k = utils.getEventKey(ev);
        //alert(k);
        if ((k < 48 || k > 57) && (k != 37) && (k != 44) && (k != 32) && (k != 42) && (k != 45) && (k != 95)  && (k != 46) && (k != 8) && (k != 9) && (k != 39))
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    this.textValidate = function (cntl, ev) 
    {
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
    }
}

var queriesValidations = new QueriesValidations();