'use_stric'
$(document).ready(function(){
	var columnIndex = 0;
	var addColumnBtn = $("#add-column");
	var columnTableTemplate = (remove = true) => {
		var html = "";
		html += "<tr>";
			html += "<td>";
				html += '<div><input data-column="colum_name" class="form-control input-sm" type="text" name="colum_name['+columnIndex+']"></div>';
			html += "<td>";
				html += '<select data-column="colum_type" class="form-control input-sm colum_type" name="colum_type['+columnIndex+']">';
                    html += '<option value="string">String</option>';
                    html += '<option value="integer">Integer</option>';
                    html += '<option value="text">Text</option>';
                    html += '<option value="longtext">Longtext</option>';
                html += '</select>';
			html += "</td>";
			html += "</td>";
			html += "<td>";
				html += '<input data-column="length" class="form-control input-sm" type="number" name="length['+columnIndex+']">';
			html += "</td>";
			html += "<td>";
				html += '<input class="input-sm" data-column="primary" type="checkbox" name="primary['+columnIndex+']">';
			html += "</td>";
			html += "<td>";
				html += '<input class="input-sm" data-column="auto_increment" type="checkbox" name="auto_increment['+columnIndex+']" disabled>';
			html += "</td>";
			html += "<td>";
				html += '<input class="input-sm relation" data-column="relation" type="checkbox" name="relation['+columnIndex+']"><br>';
			html += "</td>";
			html += "<td>";
				html += '<input class="form-control input-sm" data-column="relation_table" type="text" disabled placeholder="Table Name" name="relation_table['+columnIndex+']"><br>';
			html += "</td>";
			html += "<td>";
				html += '<input class="form-control input-sm" data-column="key" type="text"  disabled placeholder="key" onChange="changeRelation()" name="relation_key['+columnIndex+']">';
			html += "</td>";
			html += "<td>";
				html += '<input class="input-sm" data-column="nullable" type="checkbox" name="nullable['+columnIndex+']">';
			html += "</td>";
			html += "<td>";
				html += '<input class="input-sm" data-column="unique" type="checkbox" name="unique['+columnIndex+']">';
			html += "</td>";
			html += "<td>";
				html += '<input class="input-sm" data-column="visible" type="checkbox" name="visible['+columnIndex+']">';
			html += "</td>";
			html += "<td>";
				html += '<input class="input-sm" data-column="searchable" type="checkbox" name="searchable['+columnIndex+']">';
			html += "</td>";
			if(remove == true){
				html += "<td>";
					html += '<button type="button" class="btn btn-sm btn-danger">Remove</button>';
				html += "</td>";
			}
		html += "</tr>";
		columnIndex++;
		
		return html;
	}
	var myForm = $("#form").validate({
		rules : {
			table_name : {required : true},
			controller_name : {required : true},
			controller_path : {required : true},
			route_path : {required : true},
		},
		submitHandler : function(form){
			generate(form);
			return false;
		}
	});
	function generate(form){
		var form = $(form);
		$.ajax({
			type : "POST",
			data : form.serialize(),
			success : function(res){

			}, complete : function(res){

			},
			error : function(xhr, text){
				alert(text);
			}
		})
	}
	addColumnTable(false);
	addColumnBtn.click(() => {
		addColumnTable();
	})
	async function addColumnTable(remove = true){
		var table = $("#table-column");
		var rows = $(columnTableTemplate(remove));
		await $("#table-column tbody").append(rows);
		addValidation($(rows));

	}
	
	function addValidation(rows){
		var inputs = rows.find("td input, td select");
		inputs.map((idx, val) => {
			var data_column = $(val).attr("data-column");
			var rules = {};
			if(data_column == "colum_name"){
				rules["required"] = true;
			}
			$(val).rules("add", rules)
		})
	}
	function addRule(el, rules){
		$(el).rules("add", rules);
	}
	function removeRule(el, rules){
		// $(el).ruremoveles("add", rules);
	}
	$("body").off("change", ".relation").on("change", ".relation", function(){
		var relation = $(this).prop("checked");
		var td = $(this).parent("td");
		var tdRelationTable = td.next();
		var tdRelationKey = tdRelationTable.next();
		var relationTable = tdRelationTable.find("input")[0];
		var relationKey = tdRelationKey.find("input")[0];
		if(relation){
			$(relationKey).prop("disabled", false);
			$(relationTable).prop("disabled", false);
			addRule(relationKey, {required : true});
			addRule(relationTable, {required : true});
		}else{
			$(relationKey).prop("disabled", true).val("").removeClass("error");
			$(relationTable).prop("disabled", true).val("").removeClass("error");
			$(relationKey).next().remove();
			$(relationTable).next().remove();
		}
	})

	$("body").off("change", ".colum_type").on("change", ".colum_type", function(){
		var colum_type = $(this).val();
		var tr = $(this).parents("tr")[0];
		var auto_increment = $(tr).find("input[name*=auto_increment]")[0];
		var nullable = $(tr).find("input[name*=nullable]")[0];
		if(colum_type == "integer"){
			$(auto_increment).prop("disabled", false);
			$(nullable).prop("disabled", true);
			$(nullable).prop("checked", false);
		}else{
			$(auto_increment).prop("disabled", true);
			$(nullable).prop("disabled", false);
			$(nullable).prop("checked", true);
		}
		
	});
	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});
});