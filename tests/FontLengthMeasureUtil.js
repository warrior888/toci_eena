
var FontLengthMeasure = function () {

    var charsList = Array(
        'a',
        '�',
        'A',
        '�',
        'b',
        'B',
        'c',
        '�',
        'C',
        '�',
        'd',
        'D',
        'e',
        '�',
        'E',
        '�',
        'f',
        'F',
        'g',
        'G',
        'h',
        'H',
        'i',
        'I',
        'j',
        'J',
        'k',
        'K',
        'l',
        '�',
        'L',
        '�',
        'm',
        'M',
        'n',
        '�',
        'N',
        '�',
        'o', 
        '�', 
        'O', 
        '�', 
        'p', 
        'P', 
        'q',
        'Q',
        'r', 
        'R', 
        's', 
        '�', 
        'S',
        '�',
        't', 
        'T', 
        'u', 
        'U', 
        'v',
        'V',
        'w', 
        'W',
        'x',
        'X',
        'y', 
        'Y', 
        'z', 
        '�', 
        '�', 
        'Z', 
        '�', 
        '�', 
        '0', 
        '1', 
        '2', 
        '3', 
        '4', 
        '5', 
        '6', 
        '7', 
        '8', 
        '9', 
        '.',
        '-',
        ',',
        '/',
        '|',
        '(',
        ')',
        '[',
        ']',
        '{',
        '}',
        ' ',
        '\\'
    );
    
    var results = Array();
    
    this.measure = function (workContainer, compContainer, longestChar, outputPlaceHolder) {
    
        var contWorkNode = document.getElementById(workContainer);
        var contCompNode = document.getElementById(compContainer);
        var output = document.getElementById(outputPlaceHolder);
        
        contCompNode.innerHTML = longestChar;
        var longestCharLength = contCompNode.offsetWidth;
        console.log('longest char length', longestCharLength);
        
        var workCharLength = 0;
        for (var character in charsList) {
        
            contWorkNode.innerHTML = charsList[character];
            
            /*for (var i = 0; i < 9; i++) {
                
                contWorkNode.innerHTML += charsList[character];
            } */
            
            workCharLength = contWorkNode.offsetWidth; // / 10;
            
            //console.log('length of ' + charsList[character], workCharLength);
            
            //output.innerHTML +=  charsList[character] + ' => ' + (workCharLength / longestCharLength * 100) + ',<br />';
            output.innerHTML +=  "'" + charsList[character] + '\' => ' + workCharLength + ',<br />';
        }
    }
}

var fontMeasure = new FontLengthMeasure();