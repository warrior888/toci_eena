var EmploymentHistory = function() {

	var utils = new Utils();
	
	// customHtml - html to add inside placeHolder when status matched
	this.addUnsuitableReasonHtml = function (rowId, idStatus, idStatusNieodpowiedni) {
		
		if (idStatus == idStatusNieodpowiedni)
		{
			var row = utils.getElementById(rowId);
			row.style.display = '';
		}
		else
		{
			var row = utils.getElementById(rowId);
			row.style.display = 'none';
		}
	};
        
        this.getDestinations = function (wakatId, container, defaultVal, type) {
            $.ajax({
                type: "GET",
                data: "dataType=destinations&wakatId=" + wakatId + "&type=" + type,
                url: "historia_zatrudnienia_support.php",
                timeout: 45000,
                error: function() {
                  console.log("GET failed");
                },
                success: function(result) {
                    $(container).html(result);
                    if(defaultVal > 0) {
                        $('#id_destination_' + type + ' option[id='+defaultVal+']').attr('selected', 'selected');
                    }
                }
            })
        };
        
        this.getContactPersons = function (wakatId, container, defaultVal, type) {
            $.ajax({
                type: "GET",
                data: "dataType=contactPersons&wakatId=" + wakatId + "&type=" + type,
                url: "historia_zatrudnienia_support.php",
                timeout: 45000,
                error: function() {
                  console.log("GET failed");
                },
                success: function(result) {
                    $(container).html(result);
                    if(defaultVal > 0) {
                        $('#id_contactPerson_' + type + ' option[id='+defaultVal+']').attr('selected', 'selected');
                    }
                }
            })
        };
        
        this.setWakatDropdowns = function(selector) {
            var dataObj = {};
            if(typeof selector == 'undefined') {
                dataObj = employmentHistoryData;
            } else {
                dataObj[selector] = employmentHistoryData[selector];
            }
            
            for (var entry in dataObj) {
                var selectorPrefix = '#' + entry + ' ';
                var wakatId = $(selectorPrefix + dataObj[entry].wakatDropdownsSelector + ' option:selected').attr("id");
                
                employmentHistory.getDestinations(wakatId, selectorPrefix + "#msc_docelowe_container", dataObj[entry].destinationId, entry);
                employmentHistory.getContactPersons(wakatId, selectorPrefix + "#osoba_kontaktowa_container", dataObj[entry].contactPerson, entry);
            }
            
            
            
        };
};

var employmentHistory = new EmploymentHistory();

$(document).ready(function() {
    employmentHistory.setWakatDropdowns();
});