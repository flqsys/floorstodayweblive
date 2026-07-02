
/**
 * @package    Google Analytics by Lara - Pro
 * @author     Amr M. Ibrahim <mailamr@gmail.com>
 * @link       https://www.xtraorbit.com/
 * @copyright  Copyright (c) XtraOrbit Web development SRL 2016 - 2020
 */

window.gauthWindow = function (url) {
      var newWindow = window.open(url, 'name', 'height=600,width=450');
      if (window.focus) {
        newWindow.focus();
      }
}

window.debugWindow = function () {
      var newWindow = window.open('', 'Debug', 'height=600,width=600,scrollbars=yes');
	  newWindow.document.write("<pre>"+JSON.stringify(lrgawidget_debug, null, " ")+"</pre>");
      if (window.focus) {
        newWindow.focus();
      }
}

window.lrgawidget_debug;

(function($) {

	
var dateRange = {};
var systemTimeZone;
var lrsessionStorageReady = false;
var setup = false;
var debug = false;


function isObject(val) {
    if (val === null) { return false;}
    return ( (typeof val === 'function') || (typeof val === 'object') );
}

function reloadCurrentTab(){
   var $link = $('#lrgawidget li.active a[data-toggle="tab"]');
   $link.parent().removeClass('active');
   var tabLink = $link.data('target');
   $('#lrgawidget a[data-target="' + tabLink + '"]').tab('show');	
}

function lrgaErrorHandler(err){
	var error;
	var error_description;
	var error_code;
	var error_debug;
	var message;
	if (typeof err === 'object'){
		error = ((err.error != null) ? "["+err.error+"]" : "");
		error_description = ((err.error_description != null) ? err.error_description : "");
		error_code = ((err.code != null) ? "code ["+err.code+"]" : "");	
		if (err.debug != null){
			error_debug = "<a href='javascript:debugWindow();'>debug</a>";
			lrgawidget_debug = err.debug;
		}
        message = "Error "+error_code+" "+error_debug+":<br> "+error+" "+error_description;
	}else {
		message = err;
	}
    $("#lrgawidget_error").html('<h4><i class="icon fas fa-exclamation-triangle"></i> '+message+'</h4>');
	$("#lrgawidget_error").removeClass("hidden");	
}

function lrWidgetSettings(arr){
	$("#lrgawidget_error").html("").addClass("hidden");
	$("#lrgawidget_mode").html("");
	$("#lrgawidget_loading").html('<i class="fas fa-spinner fa-pulse"></i>');

	if (arr[0]){
		arr[0].value = "lrgawidget_"+arr[0].value;
	}else{
		arr['action'] = "lrgawidget_"+arr['action'];
	}
	
	if (typeof arr === 'object'){
		try {
			arr.push({name: 'start', value: dateRange.start});
			arr.push({name: 'end', value: dateRange.end});
		}catch(e){
			arr['start'] = dateRange.start;
			arr['end'] = dateRange.end;
		}
	}

	if (debug){console.log(arr)};
	return $.ajax({
		method: "POST",
		url: lrgawidget_ajax_object.lrgawidget_ajax_url,
		data: arr,
		dataType: 'json'
	})
	.done(function (data, textStatus, jqXHR) {
		if (debug){console.log(data)};
		if (data.status != "done"){
			lrgaErrorHandler(data);
		}
		
		if (data.setup){
			setup = true;
			if ($("#lrgawidget a[data-target='#lrgawidget_settings_tab']").is(":visible")){
				$("#lrgawidget a[data-target='#lrgawidget_settings_tab']").tab('show');
				if (data.setup === 2){selectDataStream();}
			}else{
				lrgaErrorHandler(lrwidgetenLang.setuprequired);
			}
		}
		
		if (data.status == "done"){
			if (data.cached){ $("#lrgawidget_mode").attr( "class", "label label-success").html(lrwidgetenLang.cached);}
			if (data.system_timezone){ systemTimeZone = data.system_timezone;}
		}		
	})
	.fail(function (jqXHR, textStatus, errorThrown) {
		lrgaErrorHandler(errorThrown);
		if (debug){
			console.log(jqXHR);
			console.log(textStatus);
			console.log(errorThrown);
		}
	})		
	.always(function (dataOrjqXHR, textStatus, jqXHRorErrorThrown) {
		$("#lrgawidget_loading").html("");
		$("#lrgawidget_loading_big").hide();
	});
}


var lrgaAccountSummaries;
var lrgaaccountID; 
var propertyID;
var dataStreamID;
var propertyUrl;
var lrgaLockSettings;
var lrgaForceRefresh;
var lrgascpurls;
var lrgascpurl;

function enableSettingsInput(mode){
	$("#lrgawidget-save-settings").prop('disabled',!mode);
	$('#lrgawidget-setMeasurementID input').prop('disabled', !mode);
	$('#lrgawidget-setMeasurementID select').prop('disabled', !mode);		
}

function populateViews(){
	$('#lrgawidget-accounts').html("");
	$('#lrgawidget-properties').html("");
	$('#lrgawidget-dataStream').html("");
	$("#lrgawidget-accname").html("");
	$("#lrgawidget-propname").html("");
	$("#lrgawidget-dsrl").html("");
	$("#lrgawidget-dsname").html("");
	$("#lrgawidget-dstype").html("");
	$("#lrgawidget-ptimezone").html("");
	$("#lrgawidget-timezone-show-error").hide();
	$("#lrgawidget-timezone-error").hide();
	$("#shopify_add_universal_tracking").hide();
	$('#lrgawidget-scpurl-message').removeClass("hidden");
	$('#lrgawidget-scp-url').html("");
	$("#lrgawidget-scpurl-error").addClass("hidden");
    
	$.each(lrgaAccountSummaries, function( index, account ) {
		if (account.id){
			if (!lrgaaccountID){lrgaaccountID = account.id;}
			$('#lrgawidget-accounts').append($("<option></option>").attr("value",account.id).text(account.displayName)); 
			if (account.id == lrgaaccountID){
				$("#lrgawidget-accname").html(account.displayName);
				$('#lrgawidget-properties').append($("<option></option>").attr("value","").text("-- "+lrwidgetenLang.selectproperty+" --"));
				if (account.properties){
					$.each(account.properties, function( index, property ) {
						$('#lrgawidget-properties').append($("<option></option>").attr("value",property.id).text(property.displayName));
						if (property.id == propertyID){
							$("#lrgawidget-propname").html(property.displayName);
							propertyUrl = property.websiteUrl;
							if (property.dataStreams){
								$.each(property.dataStreams, function( index, dataStream ) {
									if (!dataStreamID){dataStreamID = dataStream.id;}
									$('#lrgawidget-dataStream').append($("<option></option>").attr("value",dataStream.id).text(dataStream.displayName + " - [ " + dataStream.measurementId + " ]"));
									if (dataStream.id == dataStreamID){
										$("#lrgawidget-dsrl").html(dataStream.defaultUri+ " - <b>[ " + dataStream.measurementId + " ]</b> ");
										$("#lrgawidget-dsname").html(dataStream.displayName);
										$("#lrgawidget-dstype").html(dataStream.type);
										$("#lrgawidget-ptimezone").html(property.timeZone);
										$(".lrgawidget-datastream-measurementid").html(dataStream.measurementId);
										$("#shopify_add_universal_tracking").show();
										if (property.timeZone != systemTimeZone){
											$("#lrgawidget-tz-error-vtimezone").html(property.timeZone);
											$("#lrgawidget-tz-error-stimezone").html(systemTimeZone);
											$("#lrgawidget-timezone-show-error").show();
										}else{
											$("#lrgawidget-timezone-show-error").hide();
											$("#lrgawidget-timezone-error").hide();
										}
									}
								});
							}
						}											 
					});
				}
			}
		}
	});

	$('#lrgawidget-scp-url').append($("<option></option>").attr("value","").text("-- "+lrwidgetenLang.selectpropertyurl+" --"));
	if (lrgascpurls){
		$.each(lrgascpurls, function( i, scpurl ) {
			if (scpurl.siteUrl){
				$('#lrgawidget-scp-url').append($("<option></option>").attr("value",scpurl.siteUrl).text(scpurl.siteUrl));
				if (lrgascpurl == scpurl.siteUrl ){
					$('#lrgawidget-scp-url').val(lrgascpurl);
					$("#lrgawidget-scpurl").html(lrgascpurl);
				}
			}
		});		
	}
	
	if (!$('#lrgawidget-scp-url').val()){
		$("#lrgawidget-scpurl").html("");
		$("#lrgawidget-scpurl-error").removeClass("hidden");
		
		
	}

	$('#lrgawidget-accounts').val(lrgaaccountID);
	$('#lrgawidget-properties').val(propertyID);
	$('#lrgawidget-dataStream').val(dataStreamID);
	
}

function getAccountSummaries(pid){
	enableSettingsInput(false);
	lrWidgetSettings({action: "getAccountSummaries", pid: pid, purge: lrgaForceRefresh }).done(function (data, textStatus, jqXHR) {
		if (data.status == "done"){
			lrgaaccountID = data.current_selected.account_id;
			propertyID = data.current_selected.property_id;
			dataStreamID = data.current_selected.datastream_id;
			lrgaAccountSummaries = data.accountSummaries;
			lrgaLockSettings = data.current_selected.lock_settings;
			lrgascpurl = data.current_selected.scp_url;
			lrgascpurls = data.web_master_sites;
			if(lrgaLockSettings !== "on"){
				enableSettingsInput(true);
				$('.lrgawidget-lock-settings input[type=checkbox]').prop("checked", false);
			}else{
				$('.lrgawidget-lock-settings input[type=checkbox]').prop("checked", true);
			}

			populateViews();
			lrgaForceRefresh = false;
			setup = false;
		}
	})
	
}

$(document).ready(function(){
	
    $("#lrgawidget-credentials").submit(function(e) {
        e.preventDefault();
		lrWidgetSettings($("#lrgawidget-credentials").serializeArray()).done(function (data, textStatus, jqXHR) {
			$('#lrga-wizard').wizard('selectedItem', {step: "lrga-getCode"});
			$('#lrga-wizard #code-btn').attr('href','javascript:gauthWindow("'+data.url+'");');
			$('#lrgawidget-code input[name="client_id"]').val($('#lrgawidget-credentials input[name="client_id"]').val());
			$('#lrgawidget-code input[name="client_secret"]').val($('#lrgawidget-credentials input[name="client_secret"]').val());
		})
	});
	
	
    $("#lrgawidget-code").submit(function(e) {
        e.preventDefault();
		lrWidgetSettings($("#lrgawidget-code").serializeArray()).done(function (data, textStatus, jqXHR) {
			if (data.status == "done"){
				$('#lrga-wizard').wizard('selectedItem', {step: "lrga-datastream"});
			}
		})
	});	
	
    $("#express-lrgawidget-code").submit(function(e) {
        e.preventDefault();
		lrWidgetSettings($("#express-lrgawidget-code").serializeArray()).done(function (data, textStatus, jqXHR) {
			if (data.status == "done"){
				$('#lrga-wizard').wizard('selectedItem', {step: "lrga-datastream"});
			}
		})
	});		
	
	
    $("#lrgawidget-setMeasurementID").submit(function(e) {
        e.preventDefault();
		enableSettingsInput(true);
		lrWidgetSettings($("#lrgawidget-setMeasurementID").serializeArray()).done(function (data, textStatus, jqXHR) {
			if (data.status == "done"){
				$("#lrgawidget a[data-target^='#lrgawidget_']:eq(0)").click();
				graphOptions = {};
			}
		})	
	});		
	
	
	$('#lrga-wizard').on('changed.fu.wizard', function (evt, data) {
		if ($("[data-step="+data.step+"]").attr("data-name") == "lrga-datastream"){
			getAccountSummaries();
		}
	});
	
	$('#lrgawidget-accounts').on('change', function() {
		lrgaaccountID = this.value;
		propertyID = "";
		dataStreamID = "";
		populateViews();
	});

	$('#lrgawidget-properties').on('change', function() {
		getAccountSummaries(this.value);
		propertyID = this.value;
		dataStreamID = "";
		populateViews();
	});	

	$('#lrgawidget-dataStream').on('change', function() {
		dataStreamID = this.value;
		populateViews();
	});	
	$('#lrgawidget-scp-url').on('change', function() {
		lrgascpurl = this.value;
		populateViews();		
	});	

	$('#lrgawidget-timezone-show-error').on('click', function(e) {
		 e.preventDefault();
		 $("#lrgawidget-timezone-error").toggle();
	});

	$('a[data-reload="lrgawidget_reload_tab"]').on('click', function(e) {
		 lrgaForceRefresh = true;
		 e.preventDefault();
		 reloadCurrentTab();
	});
	
	$('a[data-reload="lrgawidget_go_advanced"]').on('click', function(e) {
		 e.preventDefault();
		 $("#lrgawidget_express_setup").hide();
		 $("#lrgawidget_advanced_setup").show();
		 $("[data-reload='lrgawidget_go_express']").show();
		 
	});	
	
	$('[data-reload="lrgawidget_go_express"]').on('click', function(e) {
		 e.preventDefault();
		 $("#lrgawidget_error").html("").addClass("hidden");
		 $("#lrgawidget_advanced_setup").hide();
		 $("[data-reload='lrgawidget_go_express']").hide();
		 $('#lrga-wizard').wizard('selectedItem', {step: 1});
		 $("#lrgawidget_express_setup").show();
	});

	$('.lrgawidget-lock-settings input[type=checkbox]').click(function(){
		if($(this).is(":checked")){
			enableSettingsInput(false);
		}else if($(this).is(":not(:checked)")){
			enableSettingsInput(true);
		}
		$(this).prop('disabled', false);
		$("#lrgawidget-save-settings").prop('disabled',false);
	});	
	
});


function drawRegionsMap(countriesMapData) {
	$('#lrgawidget_countries_chartDiv').empty();
	if ($('#lrgawidget_countries_chartDiv').is(":visible")){
		$('#lrgawidget_countries_chartDiv').vectorMap({
			map: 'world_mill_en',
			backgroundColor: "#5FA3CA",
			regionStyle: {
			  initial: {
				fill: '#fff',
				"fill-opacity": 1,
				stroke: 'none',
				"stroke-width": 0,
				"stroke-opacity": 1
			  }
			},
			series: {
			  regions: [{
				values: countriesMapData,
				scale: [ "#ebf4f9", "#92c1dc"],
				normalizeFunction: 'polynomial'
			  }]
			},
			onRegionLabelShow: function (e, el, code) {
			  el.html("<img src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' class='flag flag-"+code.toLowerCase()+"'><b>"+el.html()+"</b>");	
			  if (typeof countriesMapData[code] != "undefined"){
				  el.html(el.html() + '<br>' + countriesMapData[code] + ' ' + lrwidgetenLang.activeusers);
			  }
			}
		});
	}
}

var pieColors = ['#8a56e2','#cf56e2','#e256ae','#e25668','#e28956','#e2cf56','#aee256','#68e256','#56e289','#56e2cf','#56aee2','#a6cee3'];
pieColors.reverse(); 



var pieObjects = {};

function tooltipFunction(v, pieData, legendHeader){
	var percent;
	var tip;
	$.each(pieData, function( i, obj ){
		if (v.value == obj.value){
			percent = obj.percent;
			return false;
		}
	});
	if (percent){
		tip = v.label+" : "+percent+" %";
	}else{
		tip = v.label+" : "+v.value;
	}
	return tip;
}
	
function drawDPieChart (tabName, pieData, legendHeader , iconName , iconColor, iconType, iconHex ) {
	var chartName = "#lrgawidget_"+tabName+"_chartDiv";
	var legendName = "#lrgawidget_"+tabName+"_legendDiv";
	
	$(legendName).empty();
	 if(pieObjects[tabName]!=null  && !$.isEmptyObject(pieObjects[tabName])){
		pieObjects[tabName].destroy();
		pieObjects[tabName] = {};
	}
		
	if ($(chartName).is(":visible")){
		var helpers = Chartv1.helpers;
		var options = { animateRotate : true,
						animationSteps: 100,
						segmentShowStroke : true, 
						animationEasing: 'easeInOutQuart',
						middleIconName: iconName,
						middleIconColor: iconColor,
						middleIconType: iconType,
						middleIconHex: iconHex,
						legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><i class=\"far fa-circle fa-fw\" style=\"color:<%=segments[i].fillColor%>\"></i>  <%if(segments[i].label){%><%if(segments[i].label.length > 18){%><%=segments[i].label.substring(0, 18)%><%=\" ...\"%><%}else{%><%=segments[i].label%><%}%><%}%>   </li><%}%></ul>",
						tooltipTemplate: function(v) {return tooltipFunction(v, pieData, legendHeader);}
						 };
		var ctx = $(chartName).get(0).getContext("2d");

		var moduleDoughnut  = new Chartv1(ctx).DoughnutWithMiddleIcon(pieData,options);

		pieObjects[tabName] = moduleDoughnut;
		
			var legendHolder = document.createElement('div');
			legendHolder.innerHTML = moduleDoughnut.generateLegend();
			helpers.each(legendHolder.firstChild.childNodes, function(legendNode, index){
				helpers.addEvent(legendNode, 'mouseover', function(){
					var activeSegment = moduleDoughnut.segments[index];
					activeSegment.save();
					activeSegment.fillColor = activeSegment.highlightColor;
					moduleDoughnut.showTooltip([activeSegment]);
					activeSegment.restore();
				});
			});
			helpers.addEvent(legendHolder.firstChild, 'mouseout', function(){
				moduleDoughnut.draw();
			});
			
			$(legendName).append(legendHolder.firstChild);
	}
	
}

var browsersIcons = {"chrome":{"hex": "\uf268", "icon" : "fab fa-chrome", "color" : "#4587F3", "type": "Font Awesome 5 Brands"},
                     "firefox":{"hex": "\uf269", "icon" : "fab fa-firefox", "color" : "#e66000", "type": "Font Awesome 5 Brands"},
					 "safari":{"hex": "\uf267", "icon" : "fab fa-safari", "color" : "#1B88CA", "type": "Font Awesome 5 Brands"},
					 "safari (in-app)":{"hex": "\uf179", "icon" : "fab fa-apple", "color" : "#979797", "type": "Font Awesome 5 Brands"},
					 "internet explorer":{"hex": "\uf26b", "icon" : "fab fa-internet-explorer", "color" : "#1EBBEE", "type": "Font Awesome 5 Brands"},
					 "edge":{"hex": "\uf282", "icon" : "fab fa-edge", "color" : "#55acee", "type": "Font Awesome 5 Brands"},
					 "opera":{"hex": "\uf26a", "icon" : "fab fa-opera", "color" : "#cc0f16", "type": "Font Awesome 5 Brands"},
					 "opera mini":{"hex": "\uf26a", "icon" : "fab fa-opera", "color" : "#cc0f16", "type": "Font Awesome 5 Brands"},
					 "android browser":{"hex": "\uf17b", "icon" : "fab fa-android", "color" : "#a4c639", "type": "Font Awesome 5 Brands"},
					 "mozilla compatible agent":{"hex": "\uf136", "icon" : "fab fa-maxcdn", "color" : "#FF6600", "type": "Font Awesome 5 Brands"},
					 "default_icon":{"hex": "\uf022", "icon" : "far fa-list-alt", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"}
					 };					 
						  
var osIcons = {"chrome os":{"hex": "\uf268", "icon" : "fab fa-chrome", "color" : "#4587F3", "type": "Font Awesome 5 Brands"},
               "ios":{"hex": "\uf179", "icon" : "fab fa-apple", "color" : "#979797", "type": "Font Awesome 5 Brands"},
			   "windows":{"hex": "\uf17a", "icon" : "fab fa-windows", "color" : "#1EBBEE", "type": "Font Awesome 5 Brands"},
			   "linux":{"hex": "\uf17c", "icon" : "fab fa-linux", "color" : "#000000", "type": "Font Awesome 5 Brands"},
			   "macintosh":{"hex": "\uf179", "icon" : "fab fa-apple", "color" : "#979797", "type": "Font Awesome 5 Brands"},
			   "windows phone":{"hex": "\uf17a", "icon" : "fab fa-windows", "color" : "#1EBBEE", "type": "Font Awesome 5 Brands"},
			   "android":{"hex": "\uf17b", "icon" : "fab fa-android", "color" : "#a4c639", "type": "Font Awesome 5 Brands"},
			   "default_icon":{"hex": "\uf108", "icon" : "fas fa-desktop", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"}
			   };			   

var devicesIcons = {"desktop":{"hex": "\uf108", "icon" : "fas fa-desktop", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"},
					"mobile":{"hex": "\uf3cd", "icon" : "fas fa-mobile-alt", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"},
					"tablet":{"hex": "\uf3fa", "icon" : "fas fa-tablet-alt", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"},
					"default_icon":{"hex": "\uf108", "icon" : "fas fa-desktop", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"}
			   };				   

var languagesIcons = {"default_icon":{"hex": "\uf031", "icon" : "fas fa-font", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"}};
var screenresIcons = {"default_icon":{"hex": "\uf31e", "icon" : "fas fa-expand-arrows-alt", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"}};
var pagesIcons = {"default_icon":{"hex": "\uf15b","icon" : "far fa-file", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"}};
var sourcesIcons = {"default_icon":{"hex": "\uf360", "icon" : "fas fa-external-link-square-alt", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"}};
var keywordsIcons = {"default_icon":{"hex": "\uf002","icon" : "fas fa-search", "color" : "#1EBBEE", "type": "Font Awesome 5 Free"}};

var dataTableDefaults = { "paging": true,
						  "pagingType": "full",
						  "lengthChange": false,
						  "searching": false,
						  "ordering": true,
						  "info": true,
						  "autoWidth": false,
						  "pageLength": 7,
						  "retrieve": true,
						  "columnDefs": [{ "width": "60%", "targets": 0 }],
						  "order": [[ 1, "desc" ]]};
						  
if (typeof lrdataTableLang !== 'undefined'){
	dataTableDefaults.language = lrdataTableLang;
}						  

function getIcon (name, icons){
	var sname = name.toLowerCase();
	if ( icons[sname] ){
		return {"hex" : icons[sname]['hex'], "name" : icons[sname]['icon'], "color" : icons[sname]['color'], "type" : icons[sname]['type']};
	}else{
		return {"hex" : icons['default_icon']['hex'], "name" : icons['default_icon']['icon'], "color" : icons['default_icon']['color'], "type" : icons['default_icon']['type']};
	}
}

function prepareTable(tableName, options){
	var settings = $.extend({}, dataTableDefaults, options);
	var table = $(tableName).DataTable(settings);
	return table;
}

function prepareData(data, icons){
	var pieData = [];
	var tableData = [];
	var combined = 0;
	var combinedPercent = 0;
	var lIndex = 0;	
	
	$.each(data, function( i, row ){
		if ((typeof row === 'object') && (row)){
			var tableLabel = row[0];
			var pieLabel   = row[0];
			var rawLabel   = row[0];
			if ($.isArray(row[0])){
				rawLabel   = row[0][0];
				tableLabel = row[0][1] + "<br><a href='//" + row[0][0] + "' target='_blank'>" + row[0][0] + "</a>";
				pieLabel   = row[0][1];
			}
			var icon = getIcon (pieLabel, icons);
			if ((row[2] <= 1) || (i >= 11)){
				combined = combined + parseFloat(row[1]);
				combinedPercent = combinedPercent + parseFloat(row[2]);
			}else{
				pieData[i] = { label: pieLabel,  value: row[1], percent: row[2] ,color: pieColors[i]};
			}
			
			tableData[i] = [rawLabel,"<div style='display:flex;'><div style='padding:2px 10px 0px 0px;'><i class='"+icon.name+" fa-lg fa-fw' style='color:"+icon.color+";'></i></div><div>"+tableLabel+"</div></div>",row[1],row[2]+" %"];
			lIndex = i;
		}
	});
	if ( combined > 0){
		pieData.push({label: "Others",  value: combined,  percent: parseFloat(Math.round(combinedPercent * 100) / 100).toFixed(2), color: pieColors[lIndex]});
	}
	return [tableData, pieData];
}

function drawTablePie(tabName, callName, icons){
	var tableName = "#lrgawidget_"+tabName+"_dataTable";
	var pieData = [];
	var options = {"columnDefs": [{"targets": [ 0 ],"visible": false,"searchable": false},{ "width": "60%", "targets": 1 }],
	               "order": [[ 2, "desc" ]]	};
	   
	var table = prepareTable(tableName, options);
	table.clear();
	
	lrWidgetSettings({action : callName}).done(function (data, textStatus, jqXHR) {
		if (data.status == "done"){
			var processedData = prepareData(data.table_data, icons);
			table.rows.add(processedData[0]);
			table.draw();
			drawDPieChart(tabName, processedData[1],"",icons['default_icon']['icon'], icons['default_icon']['color'], icons['default_icon']['type'], icons['default_icon']['hex']);
		}
	});
	return table;			
}

function versionsPie(tabName, callName, icons, tableObj, cObject){
	var rdata = tableObj.row( cObject ).data();
	$("#lrgawidget_"+tabName+"_dataTable tbody tr").removeClass('selected');
	$(cObject).addClass('selected');			
	lrWidgetSettings({action: callName , versions: rdata[0] }).done(function (data, textStatus, jqXHR) {
		if (data.status == "done"){
			var processedData = prepareData(data.table_data, icons);
			var icon = getIcon (rdata[0], icons);
			drawDPieChart(tabName, processedData[1],rdata[0],icon.name, icon.color, icon.type, icon.hex);
	    }
	});	
}

function drawKeywords(){
	var table = prepareTable("#lrgawidget_keywords_dataTable", {"language": {"emptyTable": lrwidgetenLang.emptygaconsole},
		                                                        "pageLength": 6,"columnDefs": [{"targets": [ 0 ],"visible": false,"searchable": false},{ "width": "60%", "targets": 1 }],"order": [[ 2, "desc" ]]});
	var pieData = [];
	var combined = 0;
	var lIndex = 0;
	table.clear();
	lrWidgetSettings({action : "getKeywords"}).done(function (data, textStatus, jqXHR) {
		if (data.status == "done"){
			$.each(data, function( i, row ){
				if ($.isArray(row.keys)){
					var icon = getIcon (row.keys[0], keywordsIcons);
					table.row.add([row.keys[0],"<i class='"+icon.name+" fa-lg fa-fw' style='color:"+icon.color+";'></i>&nbsp;&nbsp;"+row.keys[0], row.clicks,row.impressions,row.ctr.toFixed(2)+" %", row.position.toFixed(2)]);
					if ((row.clicks <= 1) || (i >= 11)){
						combined = combined + parseFloat(row.clicks);
					}else{
						pieData[i] = { label: row.keys[0],  value: row.clicks , color: pieColors[i]};
					}
					lIndex = i;
				}
			});
			table.draw();
			
			if ( combined > 0){
				pieData.push({label: "Others",  value: combined, color: pieColors[lIndex]});
			}					
			drawDPieChart("keywords", pieData,"",keywordsIcons['default_icon']['icon'], keywordsIcons['default_icon']['color'],keywordsIcons['default_icon']['type'],keywordsIcons['default_icon']['hex']);
		}
	});
	return table;
}


function getObjFromCache(name){
	if (lrsessionStorageReady === true){
		var item = sessionStorage.getItem(name);
		return JSON.parse(item);
	}else{return null;}
}

function saveObjToCache(name, obj){
	if (lrsessionStorageReady === true){
		var strObj = JSON.stringify(obj);
		return sessionStorage.setItem(name, strObj);
	}else{return null;}	
}




function cb(start, end, label) {
	$('#lrgawidget_reportrange').html(moment(start).format('MMMM D, YYYY') + ' - ' + moment(end).format('MMMM D, YYYY'));
	dateRange.start = moment(start).format('YYYY-MM-DD');
	dateRange.end = moment(end).format('YYYY-MM-DD');
	if (debug){console.log(dateRange)};
	if (lrsessionStorageReady === true){
		saveObjToCache('dateRange', dateRange);
	}
	reloadCurrentTab();
}


var mainChart;
var mainChartDefaults = {"grid"   : {axisMargin: 20, hoverable: true, borderColor: "#f3f3f3",	borderWidth: 1,	tickColor: "#f3f3f3", mouseActiveRadius: 350},
						 "series" : {shadowSize: 1},
						 "lines"  : {fill: true, color: ["#3c8dbc", "#f56954"]},
						 "yaxes"  : [{ min: 0 }],
						 "xaxis"  : {mode: "time",timeformat: "%b %d"},
						 "colors" : ["#3c8dbc"],
						 "legend" : {show: true, container:'#lrga-legendholder'}};
						 
var lastFlotIndex = null;
var currentPlotData = {};

function lrTickFormatter (val, axis){
	if(Math.round(val) !== val) { val = val.toFixed(2);}
	return axis.options.lrcustom.before + val +" "+ axis.options.lrcustom.after;
}

function lrLegendFormatter(label, series){
   if (series.lrcustom.total >= 0){
	   return label+"</td><td class='legendEarnings'>"+series.lrcustom.before + series.lrcustom.total+" "+series.lrcustom.after+"</td><td>|</td><td class='legendSales'>"+series.lrcustom.totalorders;
   }
}						 

function drawGraph(data,name){

	if ( ($.plot == null) || ($.plot.version !== "lara-0.8.3")){
		$.plot = laraFlotv083;
		if (debug){console.log("restoring flot 0.8.3")};
	}

	var settings = mainChartDefaults;
	var totalSales = 0;
	var totalEarnings = 0;	
	var gData = [{ data:data["data"], label:data["label"], lines: { show: true },points: { show: true}, lrcustom: {before: data["lrbefore"], after: data["lrafter"], format: data["lrformat"]}}];
	$("#lrgawidget_sessions_chart_tooltip").remove();
	$("#lrgawidget_sessions_chartDiv").removeData("plot").empty();

	if (mainChart){
        mainChart.shutdown();
		mainChart.destroy();
        mainChart = null;
		lastFlotIndex = null;
		currentPlotData = {};
	}

	if (isObject(plotData.sales) && isObject(plotData.earnings)){
		var seData = [];
		var clineWidth = 0.5;
		var cbarWidth = 3600000 * 6;	
		var options = {"yaxes": [{min: 0 },
								 {min: 0, max:plotData["sales"]["config"]["maxv"],  show: true, position: "right", color: "#7EAAC5", tickDecimals: 0, axisLabel: plotData["sales"]["config"]["label"], axisLabelUseCanvas: true, axisLabelFontSizePixels: 12, axisLabelFontFamily: 'Verdana, Arial', axisLabelPadding: 3,lrcustom: {before: plotData["sales"]["config"]["lrbefore"], after: plotData["sales"]["config"]["lrafter"], format: plotData["sales"]["config"]["lrformat"]}},
								 {min: 0, max:plotData["earnings"]["config"]["maxv"], show: true, position: "right", color: "#87C1E3", tickFormatter: lrTickFormatter, tickColor: "#87C1E3", axisLabel: plotData["earnings"]["config"]["label"], axisLabelUseCanvas: true, axisLabelFontSizePixels: 12, axisLabelFontFamily: 'Verdana, Arial', axisLabelPadding: 3, lrcustom: {before: plotData["earnings"]["config"]["lrbefore"], after: plotData["earnings"]["config"]["lrafter"], format: plotData["earnings"]["config"]["lrformat"]}}],
					   "legend": {show: true, container:'#lrga-legendholder', labelFormatter: lrLegendFormatter}};

	    $.each( plotData["earnings"]["series"], function( i, series ) {
			var salesSeries = plotData["sales"]["series"][i];
			if ((graphData.settings.showempty == "off") && (series.total == 0 && salesSeries.total == 0)){ return true;}
			totalSales = totalSales + salesSeries.total;
			totalEarnings = totalEarnings + series.total;
			seData.push({data:salesSeries.data, sid:series.id, color: salesSeries.color, bars: { show: true,  lineWidth: clineWidth, fill: true, barWidth: cbarWidth, order: 2 },  yaxis: 2, stack: 2 });			
			seData.push({data:series.data, sid:series.id, color: series.color, label:series.label, lrcustom: {total: series.total, totalorders: salesSeries.total, before: plotData["earnings"]["config"]["lrbefore"], after: plotData["earnings"]["config"]["lrafter"], format: plotData["earnings"]["config"]["lrformat"]}, bars: { show: true,  lineWidth: clineWidth, fill: true, barWidth: cbarWidth, order: 1 },  yaxis: 3, stack: 1 });			
		});
		
		gData = gData.concat(seData);
		settings = $.extend({}, settings, options);
		$("#lrga-legendholder").css({"right":"105px"});
		$("#lrga-xologoholder").css({"right":"115px"});
	}

	mainChart = $.plot($("#lrgawidget_sessions_chartDiv"), gData, settings);
	currentPlotData = mainChart.getData();

	if( $('#lrga-legendholder').is(':empty')) {$("#lrga-legendholder").hide();}	else{$("#lrga-legendholder").show();}
	
	if (isObject(plotData.sales) && isObject(plotData.earnings)){
		$("#lrga-legendholder table tr:first").before('<tr class="legendTotals"><td class="legendColorBox"></td><td class="legendLabel">'+graphData.settings.graphlabel+'</td><td class="legendEarnings"></td><td></td><td class="legendSales"></td></tr>');	
		if ((totalSales > 0 || totalEarnings > 0) && (graphData.settings.showtotal == "on") ){
			$("#lrga-legendholder table tr:last").after('<tr class="legendTotals"><td class="legendColorBox"></td><td class="legendLabel">'+lrwidgetenLang.total+'</td><td class="legendEarnings">'+plotData["earnings"]["config"]["lrbefore"]+totalEarnings.toFixed(2)+plotData["earnings"]["config"]["lrafter"]+'</td><td>|</td><td class="legendSales">'+totalSales+'</td></tr>');	
		}
	}
	
	$('<div class="tooltip-inner" id="lrgawidget_sessions_chart_tooltip"></div>').css({
		"text-align": "left",
		"position": "absolute",
		"display": "none",
		"opacity": 0.8
	}).appendTo("body");

	$("#lrgawidget_sessions_chartDiv").bind("plothover", function (event, pos, item) {
		if (item) {
			if  ((lastFlotIndex != item.dataIndex)){
				lastFlotIndex = item.dataIndex;
				if (debug){ console.log(item);
							console.log(currentPlotData);
							console.log(lastFlotIndex);
					}
				var x = item.datapoint[0].toFixed(2);
				var y = item.datapoint[1];
				var rightMargin = "auto";
				var leftMargin  = "auto";
				var formattedDateString = moment.utc(item.datapoint[0]).format('ddd, MMMM D, YYYY');
				
				var currToolTipText = formattedDateString + "<br>";
				var totalorders = 0;
				$.each(currentPlotData, function( i, dSeries ){
					if (typeof dSeries.lrcustom !== 'undefined') {
						var cItem = dSeries.data[item.dataIndex][1];
						var tOrders = ((totalorders > 0) ? "| "+totalorders : "");
						if (cItem > 0 || totalorders > 0){
							if (dSeries.lrcustom.format == "seconds"){ cItem = formatSeconds(cItem);}
							currToolTipText += '<div style="display: inline-block;padding:1px;"><div style="width:4px;height:0;border:4px solid '+dSeries.color+';overflow:hidden"></div></div><div style="display: inline-block;padding-left:5px;">'+dSeries.label+' : '+dSeries.lrcustom.before + cItem + " " + dSeries.lrcustom.after +tOrders+"</div><br>";
						}
					}else{
						totalorders = dSeries.data[item.dataIndex][1];
					}
				});
				
				if(item.pageX + 350 > $(document).width()){ 
					rightMargin = ($(document).width() - item.pageX) + 15;
				}else{
					leftMargin  = item.pageX + 15;
				}
				
				$("#lrgawidget_sessions_chart_tooltip").html(currToolTipText)
					.css({top: item.pageY - 25, left: leftMargin, right: rightMargin})
					.show();
			}
		} else {
			lastFlotIndex = null;
			$("#lrgawidget_sessions_chart_tooltip").hide();
			$("#lrgawidget_sessions_chart_tooltip").empty();
		}
	});
}

function formatSeconds(totalSec){
	var hours   = Math.floor(totalSec / 3600);
	var minutes = Math.floor((totalSec - (hours * 3600)) / 60);
	var seconds = totalSec - (hours * 3600) - (minutes * 60);
	var fseconds = seconds.toFixed(0);
	var result = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (fseconds  < 10 ? "0" + fseconds : fseconds);	
	return result;
}

function drawSparkline(id, data, color){
	if (!color){color = '#b1d1e4';}
	$(id).sparkline(data.split(','), {
		type: 'line',
		lineColor: "#3c8dbc",
		fillColor: color,
		spotColor: "#3c8dbc",
		minSpotColor: "#3c8dbc",
		maxSpotColor: "#3c8dbc",
		drawNormalOnTop: false,
		disableTooltips: true,
		disableInteraction: true,
		width:"100px"
		});
}

var plotData = {};
var plotTotalData = {};
var selectedPname = "";
var graphOptions = {};
var graphData = {};

function drawMainGraphWidgets(data, selected){
	$('#lrgawidget_sb-main .row').html("");
	if ($('#lrgawidget_sb-main').is(":visible")){
		$.each(data, function( name, raw ){
			var color    = "";
            var minGraph = '<div class="col-sm-3 col-xs-6 lrgawidget_seven-cols" id="lrgawidget_sb_'+name+'" data-lrgawidget-plot="'+name+'">								<div class="description-block border-right">									<span class="description-text">'+raw['label']+'</span>									<h5 class="description-header">'+raw['total']+'</h5>									<div class="lrgawidget_inlinesparkline" id="lrgawidget_spline_'+name+'"></div>								</div>							</div>';
			$('#lrgawidget_sb-main .row').append(minGraph);
			if (name == selected ){  color = "#77b2d4";}
			drawSparkline("#lrgawidget_spline_"+name, raw['data'], color);
		});
		
		$("[data-lrgawidget-plot]").off('click').on('click', function (e) {
			e.preventDefault();
			selectedPname = $(this).data('lrgawidget-plot');
			$("[data-lrgawidget-plot]").removeClass("selected");
			drawGraph(plotData[selectedPname] , selectedPname);
			$(this).addClass("selected");	
		});		
	}
}

function drawMainGraph(){
	lrWidgetSettings({action : "getMainGraph"}).done(function (data, textStatus, jqXHR) {
		if (data.status == "done" && !setup){
		
			if (isObject(data.graph)){
				graphData = data.graph;
				$("#lrghop_button").show();
			}

			plotData = data.plotdata;
			plotTotalData = data.totalsForAllResults;
			if (!selectedPname){selectedPname = "activeUsers";}
			drawGraph(plotData[selectedPname], selectedPname);
			drawMainGraphWidgets(plotTotalData);
			$("#lrgawidget_sb_"+selectedPname).addClass("selected");
		}
	});	
}

function populateSettings(){
	let settings = graphOptions.settings;
	let settingsOutput = "" ;
	$("#lrghop_settings").html("");

	$.each(settings, function( sId,  sObj){
		settingsOutput += '<div class="row">								<div class="col-sm-4">'+sObj.name+'</div>								<div class="col-sm-8 btn-group btn-toggle" data-toggle="buttons">';
										
		$.each(sObj.options, function( oId,  oName){
			var is_active  = "";
			var is_checked = "";
			if (oId == sObj.value){
				is_active  = 'active';
				is_checked = 'checked="checked"';
			}
			settingsOutput +='<label class="btn btn-xs btn-primary '+is_active+'"><input name="settings['+sObj.id+']" value="'+oId+'" type="radio" '+is_checked+' >'+oName+'</label>';
		});

		settingsOutput += '	  </div>						   </div>';
	});
	
	$("#lrghop_settings").html(settingsOutput);
	
}

var filterPanelsOutput = "";
function populateFilters(){
	let filters = graphOptions.filters;
	let filtersButtons = "";
	let filterPanels   = "";
	$("#lrgfilters_buttons").html("");
	$.each(filters, function( id,  filter){
		filtersButtons += '<button class="btn btn-primary btn-sm btn-block" data-lrghop-button="'+filter.id+'" type="button">'+filter.name+'</button>';
		filterPanels   +='<div data-lrgh-panel="'+filter.id+'">							<div class="lrgo_filterpanel_head">'+filter.name+'<span class="lrgawidget_graph_cached" style="display:none;">['+lrwidgetenLang.cached+']</span></div>							<div class="lrgo_filterpanel_body" id="lrgh_'+filter.id+'">								<ul>';
		
		filterPanelsOutput = '';

		let filterData = filter.data;
		if (typeof filter.datasource !== 'undefined') {
			filterData = graphOptions.filters[filter.datasource].data;
		}
		
		populateFilterPanel(filter.id, filterData);
		filterPanels   += filterPanelsOutput;
		
		filterPanels   +='		</ul>							</div>						  </div>';
	});
	
	filterPanelsOutput = '';
	$("#lrgfilters_buttons").html(filtersButtons);
	$("#lrgfilters_panels").html(filterPanels);
	
	$("[data-lrghop-button]").on('click', function (e) {
		showOptionsGroup($(this).data('lrghop-button'));
	});	
	
}

function populateFilterPanel(filterId, items){

	$.each(items, function( id, item ){
		let checked = "";
		if (filterId == "products" && item.type == "categories"){
			if (typeof item.products !== 'undefined'){
				filterPanelsOutput += '<ul class="lrghop_filter_children" >';
				filterPanelsOutput += '<div class="lrghop_filter_header">'+item.name+'</div>';
			    populateFilterPanel(filterId, item.products);
				filterPanelsOutput += '</ul>';
			}else{
				filterPanelsOutput += '<div class="lrghop_filter_header">'+item.name+'</div>';
			}			
		}else{
			if (item.state == "on"){checked = "checked";}
			filterPanelsOutput += '<li>									<div class="lrghop_colorselector_item_container">										<span style="background-color: '+item.color+';" class="lrghop_colorselector_item">											<input type="hidden" data-lrgo-itemcolor="'+filterId+'_'+item.id+'" name="filters['+filterId+']['+item.id+'][color]" id="hidden-input" value="'+item.color+'">										</span>										<label><input  data-lrgo-itembox="'+filterId+'_'+item.id+'" type="checkbox" name="filters['+filterId+']['+item.id+'][status]" value="on"  '+checked+'>'+item.name+'</label>									</div>								   </li>';
		}

		if (typeof item.children !== 'undefined'){
			filterPanelsOutput += '<ul class="lrghop_filter_children" >';
			populateFilterPanel(filterId, item.children);
			filterPanelsOutput += '</ul>';
		}		
	});	
}

var currenSelectedColorBox = "";
function showGraphOptions(){
	if (typeof graphOptions.status == 'undefined'){
		lrWidgetSettings({action : "getGraphData"}).done(function (data, textStatus, jqXHR) {
			if (data.status == "done" && !setup){
				graphOptions = data;
				populateSettings();
				populateFilters();
				
				if (data.gaoptionscached){ $(".lrgawidget_graph_cached").show();}
				
				$('.lrghop_colorselector' ).off('change');
				
				$('.lrghop_colorselector').each( function() {
					$(this).minicolors({
					  control: 'hue',
					  defaultValue: '',
					  format: 'hex',
					  swatches: data.swatches,
					  inline: true,
					});

				});

				$(document).mouseup(function(e){
					if ($(".lrghop_colorselector_container").is(":visible")){
						if (!$(".lrghop_colorselector_container").is(e.target) && $(".lrghop_colorselector_container").has(e.target).length === 0){
							$(".lrghop_colorselector_container").hide();
						}
					}
				});	
				
				$(".lrgo_filterpanel_body").scroll(function() {
					if ($(".lrghop_colorselector_container").is(":visible")){
							$(".lrghop_colorselector_container").hide();
					}				
				});
				
				$(window).scroll(function() {
					if ($(".lrghop_colorselector_container").is(":visible")){
							$(".lrghop_colorselector_container").hide();
					}				
				});			

				$('.lrghop_colorselector_item' ).on('click', function(e) {
					var sid   = $(this).find(':first-child').data("lrgo-itemcolor");
					var sColor = $(this).find(':first-child').val();

					$("[data-lrgo-itembox='" + sid +"']").prop("checked", true);
					currenSelectedColorBox = sid;
					
					$('.lrghop_colorselector').minicolors('value', {color: sColor});
					$('.lrghop_colorselector_container').css({ left: ($(this).offset().left ) + "px", top: ($(this).offset().top - $(window).scrollTop() + 20) + "px" });
					$('.lrghop_colorselector_container').show();
					
					
				});

				$('.lrghop_colorselector' ).on('change', function(e) {
					var colorChanged = false;
					var graphData = mainChart.getData();
					var sid = currenSelectedColorBox;
					var sColor = $(this).val();
					
					$("[data-lrgo-itemcolor='" + sid +"']").parent(".lrghop_colorselector_item").css({'background-color': sColor});
					$("[data-lrgo-itemcolor='" + sid +"']").attr("value", sColor);
					
					$.each(graphData, function( i,  data){
						if ((typeof data.sid !== 'undefined') && (data.sid == sid)){	
							 graphData[i].color = sColor;
							 colorChanged = true;
						}
					});
					
					if (colorChanged === true){
						mainChart.setData(graphData);
						mainChart.draw();
						if (debug){ console.log("Graph Color Changed"); }
					}
				});
				showOptionsGroup(data.currentfilter);
			}
		});
	}
			
}

function showOptionsGroup(groupID){
	$("[data-lrgh-panel]").hide();
	$("[data-lrgh-panel="+groupID+"]").show();

	if (groupID != "settings"){
	$("[data-lrghop-button]").removeClass("active");
	$("[data-lrghop-button="+groupID+"]").addClass("active");		
		$('[name="currentfilter"]').val(groupID);
	}
}

var rtTimer = null;
var mouseTimer	 = null;
var windowActive = true;
var mouseActive	 = true;

function resetrtTimer(call){
	clearTimeout(rtTimer);
	rtTimer = null;
	if (debug){console.log("RealTime counter reset")};
	if (call === true){
		rtTimer = setTimeout(getRealTime,60000);
		if (debug){console.log("RealTime counter active")};
	}
}

function drawRealTime(rtData){
	var table = prepareTable("#lrgawidget_realtime_dataTable", {"language": {"emptyTable": lrwidgetenLang.noactiveusers},
																"pageLength": 6,"columnDefs": [{"targets": [ 0 ],"visible": false,"searchable": false},{ "width": "60%", "targets": 1 }],"order": [[ 2, "desc" ]]});
	table.clear();
	if (typeof rtData !== 'undefined'){	
		$.each(rtData.data, function( i, row ){
			table.row.add([row[0],row[0], row[1], row[2]+" %"]);

		});
	}
	table.draw();
	return table;
}

var rtProgresColors = ['#058DC7','#50B432','#ED561B','#e25668','#e28956','#e2cf56','#aee256','#68e256','#56e289','#56e2cf','#56aee2','#a6cee3'];
function getRealTime(){
	if ((windowActive === true) && ( mouseActive === true)){
		 lrWidgetSettings({action : "getRealTime"}).done(function (data, textStatus, jqXHR) {
			if (data.status == "done"){
				var currentTableDimension;
				$("#lrgawidget_mode").attr( "class", "label label-info" ).html(lrwidgetenLang.realtime);
				$("#lrgawidget_rttotal").html(data.total);
				$("#lrgawidget_realtime_dimensions").empty();
				
				$.each(data.dimensions, function( d, dimension ){
					var legend = "";
					var progressbar = "";
					var i = 0;
					var cColor = "";
					$.each(dimension.data, function( index, values ){
						if (dimension.label == "table_data"){currentTableDimension = dimension; return true;}
						cColor = rtProgresColors[i];
						legend += '<li><span style="background-color: '+cColor+';"></span>'+values[0]+'</li>';
						progressbar += '<div class="progress-bar lrgawidget_realtime_tooltip" role="progressbar" style="background-color: '+cColor+'; width:'+values[2]+'%"><span class="lrgawidget_realtime_tooltiptext">'+values[0]+' - '+values[2]+'%</span></div>';
						i++;
					});
					
					if (progressbar != ""){
						$("#lrgawidget_realtime_dimensions").append('<div class="lrgawidget_realtime_dimension"><div class="lrgawidget_realtime_dcontainer"><div class="lrgawidget_realtime_dlegend"><ul>'+legend+'</ul></div><div class="lrgawidget_realtime_dpbar"><div class="progress">'+progressbar+'</div></div></div></div>');
					}
				});
				resetrtTimer(true);
				drawRealTime(currentTableDimension);
			}else{ resetrtTimer(false);	}
		});
	}else{
		if (debug){console.log("RealTime is inactive")};
		$("#lrgawidget_mode").attr( "class", "label label-warning").html(lrwidgetenLang.inactive);
		resetrtTimer(true);
	}
}


function setOptionsGrid(){
	$('.lroptions-checkbox-grid[data-lr-roleid="administrator"][data-lr-groupid="permissions"]').find('input:checkbox').prop('checked',true).prop("disabled", true);
	$('.lrgawidget_permissions_switch[data-lr-roleid="administrator"][data-lr-groupid="permissions"]').prop('checked',true).prop("disabled", true);	
	$('.lroptions-checkbox-grid').each( function( index, element ){
		var enabled = $(this).find('input:checked');
		if (enabled.length > 0){
			$('.lrgawidget_permissions_switch[data-lr-roleid="'+$(this).data("lr-roleid")+'"][data-lr-groupid="'+$(this).data("lr-groupid")+'"]').prop("checked",true);
		}else{
			$('.lrgawidget_permissions_switch[data-lr-roleid="'+$(this).data("lr-roleid")+'"][data-lr-groupid="'+$(this).data("lr-groupid")+'"]').prop("checked",false).change();
		}
	});
}

function getPermissions(){
	lrWidgetSettings({action : "getPermissions"}).done(function (data, textStatus, jqXHR) {
		if (data.status == "done" ){
			if (debug){console.log(data)};
			var rolesHTML = "";
			var permissionsHTML = "";
			$.each(data.roles, function( i, role ){
				rolesHTML += '<li><a href="#lrrole_'+role.id+'" data-toggle="pill"><i class="fas fa-user fa-fw"></i> '+role.name+'</a></li>';
				permissionsHTML += '<div class="tab-pane" id="lrrole_'+role.id+'">';
				$.each(data.group_permissions, function( y, group ){
					permissionsHTML += '<div class="box box-primary">										<div class="box-header with-border">											<h3 class="box-title"><i class="'+group.icon+' fa-fw"></i> '+group.name+'</h3>												<input type="hidden" name="lrperms['+role.id+'][]" value="'+group.id+'">												<span class="pull-right">													<label class="switch">														<input type="checkbox" class="lrgawidget_permissions_switch" data-lr-roleid="'+role.id+'" data-lr-groupid="'+group.id+'" data-lr-grouptype="'+group.type+'"  data-lr-groupdefault="'+group.default+'">														<div class="slider "></div>													</label>												</span>										</div>										<div class="box-body lroptions-checkbox-grid" data-lr-roleid="'+role.id+'" data-lr-groupid="'+group.id+'">';
					$.each(group.permissions, function( x, permission ){
						var checked = "";
						if($.inArray(permission.name, data.role_permissions[role.id] ) !== -1){
							checked = "checked";
						}
						permissionsHTML += '												<div>													<label><input '+checked+' name="lrperms['+role.id+'][]" type="'+group.type+'" value="'+permission.name+'"> '+permission.label+'</label>												</div>';		
					});
					
					permissionsHTML += '										</div>									</div>';
				});
													
				permissionsHTML += '</div>';				
			});
			$("#lrgawidget_permissions_roles").html(rolesHTML);
			$("#lrgawidget_permissions_list").html(permissionsHTML);
			$('#lrgawidget_permissions_roles a:first').tab('show');
			$(".lrgawidget_permissions_switch").change(function(){
				var groupType = $(this).data("lr-grouptype");
				if (groupType == "checkbox"){
					$('.lroptions-checkbox-grid[data-lr-roleid="'+$(this).data("lr-roleid")+'"][data-lr-groupid="'+$(this).data("lr-groupid")+'"]').find('input:checkbox').prop('checked',this.checked).prop("disabled", !this.checked);
				}else{
					$('.lroptions-checkbox-grid[data-lr-roleid="'+$(this).data("lr-roleid")+'"][data-lr-groupid="'+$(this).data("lr-groupid")+'"]').find('input:radio').prop('checked',this.checked).prop("disabled", !this.checked);
					if ($(this).is(':checked')){
						$('.lroptions-checkbox-grid[data-lr-roleid="'+$(this).data("lr-roleid")+'"][data-lr-groupid="'+$(this).data("lr-groupid")+'"] input[type=radio][value="'+ $(this).data("lr-groupdefault") +'"]').prop('checked',this.checked);
					}
				}
			});
			setOptionsGrid();
		}
	});	
	
}

function selectDataStream(){
	$('#lrga-wizard').wizard('selectedItem', {step: "lrga-datastream"});
	$("#lrga-wizard .steps li").removeClass("complete");
	$("[data-lrgawidget-reset]").show();	
	
}

$(document).ready(function(){
	
	$("#lrgawidget_permissions_form").submit(function(e) {
		e.preventDefault();
		lrWidgetSettings($("#lrgawidget_permissions_form").serializeArray()).done(function (data, textStatus, jqXHR) {
			if (data.status == "done"){
				location.reload();
			}
		});
	});	
	
	moment.updateLocale('en', {
		months : lrwidgetDateLang.monthNames
	});	
	
	dateRange = {locale: lrwidgetDateLang, start : moment().subtract(29, 'days').format('YYYY-MM-DD'),  end : moment().format('YYYY-MM-DD')};

	try {
		if (typeof (sessionStorage) !== "undefined"){
			lrsessionStorageReady = true;
			if (getObjFromCache('dateRange') !== null){
				dateRange = getObjFromCache('dateRange');
			}else{
				saveObjToCache('dateRange', dateRange);
			}
		}
	} catch(e) {
	  console.log(e);
	}
	
	var dRanges = {};
	dRanges[lrwidgetenLang.lastsevendays] = [moment().subtract(6, 'days'), moment()];
	dRanges[lrwidgetenLang.lastthirtydays] = [moment().subtract(29, 'days'), moment()];
	dRanges[lrwidgetenLang.thismonth] = [moment().startOf('month'), moment().endOf('month')];
	dRanges[lrwidgetenLang.lastmonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];


	$('#lrgawidget_daterange-btn').lrdaterangepicker(
        {
		  locale: lrwidgetDateLang, 			
          ranges: dRanges,
          startDate: moment(dateRange.start),
          endDate: moment(dateRange.end),
		  "opens": "left"
        }, cb);
    $('#lrgawidget_reportrange').html(moment(dateRange.start).format('MMMM D, YYYY') + ' - ' + moment(dateRange.end).format('MMMM D, YYYY'));
	$("[data-lrgawidget-reset]").on('click', function () {
		if (confirm(lrwidgetenLang.resetmsg) == true) {
			lrWidgetSettings({action : "settingsReset"}).done(function (data, textStatus, jqXHR) {
				if (data.status == "done"){
					$('#lrga-wizard').wizard('selectedItem', {step: 1});
					$("[data-lrgawidget-reset]").hide();
				}
			});	
		}
	});
	
	$("#lrgawidget_main a[data-toggle='tab']").on('shown.bs.tab', function (e) {
		
		if (this.hash !== "#lrgawidget_sessions_tab"){
			$("#lrghop_button").hide();
		}
		
		resetrtTimer(false);
		$("#lrgawidget").off('mousemove');
		$(".jvectormap-label").remove();
		$("#lrgawidget_sessions_chart_tooltip").remove();
		
		if (this.hash == "#lrgawidget_settings_tab"){
			if (!setup){
				selectDataStream();
			}
	    }else if (this.hash == "#lrgawidget_permissions_tab"){
			getPermissions();			
	    }else if (this.hash == "#lrgawidget_sessions_tab"){
			drawMainGraph();
		}else if (this.hash == "#lrgawidget_countries_tab"){
			
			var countriesTable = prepareTable("#lrgawidget_countries_dataTable", {});
			
			countriesTable.clear();
			
			var countriesMapData = {};
			
			lrWidgetSettings({action : "getCountries"}).done(function (data, textStatus, jqXHR) {
				if (data.status == "done"){
					$.each(data.table_data, function( index, row ){
						if ((typeof row === 'object') && (row)){
							countriesMapData[row[0]] = row[2];
							countriesTable.row.add(["<img src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' class='flag flag-"+row[0].toLowerCase()+"'>  "+row[1],row[2],row[3]+" %"]);
						}
					});
					countriesTable.draw();
					drawRegionsMap(countriesMapData);
				}
			});

		}else if (this.hash == "#lrgawidget_realtime_tab"){
			windowActive = true;
			mouseActive	 = true;
			
			$("#lrgawidget").on('mousemove', function(e) {
				clearTimeout(mouseTimer);
				mouseTimer = null;
				mouseActive = true;
				if ($("#lrgawidget_mode").hasClass("label-warning")){
					$("#lrgawidget_mode").attr( "class", "label label-info" ).html(lrwidgetenLang.realtime);
					if (debug){console.log("mouse is active")};
				}				
				mouseTimer = setTimeout(function(e) {
					if (debug){console.log("mouse is idle")};
					mouseActive = false;
				}, 300000);
			});			
			
			getRealTime();
		}else if (this.hash == "#lrgawidget_browsers_tab"){

			browsersTable = drawTablePie("browsers", "getBrowsers", browsersIcons);

		}else if (this.hash == "#lrgawidget_languages_tab"){
			
			languagesTable = drawTablePie("languages", "getLanguages", languagesIcons);
		
		}else if (this.hash == "#lrgawidget_os_tab"){
			
			osTable = drawTablePie("os", "getOS", osIcons);
		
		}else if (this.hash == "#lrgawidget_devices_tab"){
			
			devicesTable = drawTablePie("devices", "getDevices", devicesIcons);
			
		}else if (this.hash == "#lrgawidget_screenres_tab"){
			
			screenresTable = drawTablePie("screenres", "getScreenResolution", screenresIcons);
		}else if (this.hash == "#lrgawidget_pages_tab"){
			
			pagesTable = drawTablePie("pages", "getPages", pagesIcons);			
		}else if (this.hash == "#lrgawidget_keywords_tab"){
			
			keuwordsTable = drawKeywords();
			
		}else if (this.hash == "#lrgawidget_sources_tab"){
			
			sourcesTable = drawTablePie("sources", "getSources", sourcesIcons);
		}
		
		
	});
 
    $("#lrgawidget_os_dataTable tbody").on('click', 'tr', function () {
		versionsPie("os", "getOS", osIcons, osTable, this);
    });
	
    $("#lrgawidget_devices_dataTable tbody").on('click', 'tr', function () {
		versionsPie("devices", "getDevices", devicesIcons, devicesTable, this);
    });	


    $('body').on('click', '#lrgawidget_panel_hide', function (e) {
		var wstatevalue = "";
		if ($(this).is(":checked")){
			$("#lrgawidget").show();
			wstatevalue = "show";
		}else{
			$("#lrgawidget").hide();
			wstatevalue = "hide";
		}
		lrWidgetSettings({action : "hideShowWidget", wstate: wstatevalue}).done(function (data, textStatus, jqXHR) {});	
	});

	$(".wrap:eq(1)").children("h1:first").remove();
	$("#adv-settings fieldset").append('<label for="lrgawidget_panel_hide"><input id="lrgawidget_panel_hide" type="checkbox" checked="checked">Lara, Google Analytics Dashboard Widget</label>');
	$("#lrgawidget_remove").on('click', function (e) {
		e.preventDefault(); 
		$("#lrgawidget_panel_hide").click();
	});
	$(".lrdaterangepicker").removeClass("lrdaterangepicker dropdown-menu opensleft").addClass("lrga_bs lrdaterangepicker custom-dropdown-menu opensleft");
	$('[data-toggle="lrgawidget_tooltip"]').tooltip();
	if (typeof actLrgaTabs !== 'undefined'){
		$("#lrgawidget a[data-target='#"+actLrgaTabs+"']").tab('show');
	}

	$("#lrghop_button").on('click', function (e) {
		$("#lrghop_menu").show();
		showGraphOptions();
	});

	$("#lrghop_cancel").on('click', function (e) {
		$("#lrghop_menu").hide();
	});	

    $("#lrghop_form").submit(function(e) {
        e.preventDefault();
		$("#lrghop_menu").hide();
		lrWidgetSettings($("#lrghop_form").serializeArray()).done(function (data, textStatus, jqXHR) {
			reloadCurrentTab();
		});
	});

	
	$(document).mouseup(function(e){
		if ($("#lrghop_menu").is(":visible")){
			var container = $("#lrghop_menu");
			if (!container.is(e.target) && container.has(e.target).length === 0){
				container.hide();
			}
		}
	});	
	
	$(window).bind("focus", function(e) {
		windowActive = true;
		if (debug){console.log("window mode : focus")};
	});
	
	$(window).bind("blur", function(e) {
		windowActive = false;
		if (debug){console.log("window mode : blur")};		
	});	
});


	
})(jQuery);