<?php
  header("Expires: 0"); 
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
  header("cache-control: no-store, no-cache, must-revalidate"); 
  header("Pragma: no-cache");

?>
<!DOCTYPE html>
<html>
<head>
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="cache-Control" CONTENT="no-cache">
	<META name="ROBOTS" content="NOINDEX, NOFOLLOW, NOARCHIVE">
    <link rel="stylesheet" type="text/css" href="w2ui/w2ui-1.2.min.css" />
    <link rel="stylesheet" type="text/css" href="css/dashboard.css" />	
    <script src="js/jquery.min.js"></script>
    <script src="js/ajax.js"></script>
    <script src="js/clock.js"></script>	
    <script type="text/javascript" src="w2ui/w2ui-1.2.min.js"></script>
</head>
<body onload="startTime('timeinfo')">
	<div id="qm_HEADER">
		<div class="qm_BANNER">
			<img alt="header image" src="img/header.png"/>
		</div>
		<div id="timeinfo" class="timeinfo"></div>
		<div class="qm_INFO">
			<div id="serverinfo"></div>
		</div>	
	</div>	
	<div style="position: relative; height: 300px;">
		<div id="grd_schedules" style="position: absolute; left: 2px; width: 60%; top: 100px; height: 700px;"></div>		
		<div id="tabs" style="position: absolute; right: 2px; width: 39.5%; top: 100px; height: 28px; "></div>	
		<div id="grd_details" style="position: absolute; right: 2px; width: 39.5%; top: 128px; height: 672px; display: block;"></div>
		<div id="grd_progress" style="position: absolute; right: 2px; width: 39.5%; top: 128px; height: 672px; display: none;"></div>
	</div>	
</body>
<script>
var LastAssessment='0';

ShowInformation('serverinfo','_qmpinfo.php');

$('#tabs').w2tabs({
	name: 'tabs',
	active: 'tab_details',
	tabs: [
		{ id: 'tab_details', caption: 'Details' },
		{ id: 'tab_progress', caption: 'Progress'},
	], 	
	onClick: function (target, data) {
		if(target=='tab_details') {
			document.getElementById('grd_details').style.display='block';
			document.getElementById('grd_progress').style.display='none';
			w2ui['grd_details'].refresh();
		};
		if(target=='tab_progress') {
			document.getElementById('grd_details').style.display='none';
			document.getElementById('grd_progress').style.display='block';
			w2ui['grd_progress'].load('_qmpprogress001.php'+'?assessmentid='+Window.LastAssessment);			
		};		
	}

});

$('#grd_schedules').w2grid({
    name: 'grd_schedules',
    header: 'Schedules',
	recordsPerPage: 25,
	show: {
		header         : true,  // indicates if header is visible
		toolbar        : true,  // indicates if toolbar is visible
		footer         : true,  // indicates if footer is visible
		columnHeaders  : true,   // indicates if columns is visible
		lineNumbers    : false,  // indicates if line numbers column is visible
		expandColumn   : false,  // indicates if expand column is visible
		selectColumn   : false,  // indicates if select column is visible
		emptyRecords   : false,   // indicates if empty records are visible
		toolbarReload  : true,   // indicates if toolbar reload button is visible
		toolbarColumns : true,   // indicates if toolbar columns button is visible
		toolbarSearch  : true,   // indicates if toolbar search controls are visible
		toolbarAdd     : false,   // indicates if toolbar add new button is visible
		toolbarDelete  : false,   // indicates if toolbar delete button is visible
		toolbarSave    : false,   // indicates if toolbar save button is visible
	},
	multiSearch: true,
	multiSort: true,
	searches: [				
		{ field: 'schedule_name', caption: 'Schedule', type: 'text' },
		{ field: 'group_name', caption: 'Group', type: 'text' },
		{ field: 'schedule_starts', caption: 'Schedule start', type: 'text' },		
	],
	sortData: [ { field: 'schedule_starts', direction: 'asc' } ],
	toolbar: {
		items: [
			{ type: 'spacer', id: 'space01' },
			{ type: 'button', id: 'btnToday', caption: 'Today', disabled: false, hint: 'Load active schedules for today'},
			{ type: 'break', id: 'break01' },
			{ type: 'button', id: 'btnNext7', caption: 'Next 7', disabled: false, hint: 'Load active schedules for the next 7 days'},
			{ type: 'break', id: 'break02' },
			{ type: 'button', id: 'btnNext14', caption: 'Next 14', disabled: false, hint: 'Load active schedules for the next 14 days'},
			{ type: 'break', id: 'break03' },
			{ type: 'button', id: 'btnNoDate', caption: 'No date', disabled: true, hint: 'Load schedules which are always available'},
			{ type: 'break', id: 'break04' },
			{ type: 'button', id: 'btnAll', caption: 'All', disabled: true, hint: 'Load all schedules in the system' }
		],
		onClick: function (target, data) {
			if(target=='btnToday') { w2ui['grd_schedules'].load('_qmpschedule007.php?filter=today'); }
			if(target=='btnNext7') { w2ui['grd_schedules'].load('_qmpschedule007.php?filter=next7'); }
			if(target=='btnNext14') { w2ui['grd_schedules'].load('_qmpschedule007.php?filter=next14'); }
			if(target=='btnNoDate') { w2ui['grd_schedules'].load('_qmpschedule007.php?filter=nodate'); }
			if(target=='btnAll') { w2ui['grd_schedules'].load('_qmpschedule007.php?filter=all'); }
			if(target=='refresh') { w2ui['grd_schedules'].reload(); }			
		}
	},
	columns: [				
		{ field: 'assessment_id', caption: 'Assessment_ID',  hidden: true, resizable: true, sortable: true, size: '1%'},		
		{ field: 'schedule_name', caption: 'Schedule',  resizable: true, sortable: true, size: '15%'},
		{ field: 'group_name', caption: 'Group',  resizable: true, sortable: true, size: '10%'},
		{ field: 'participant_name', caption: 'Participant',  resizable: true, sortable: true, size: '10%'},
		{ field: 'schedule_count', caption: 'Count',  resizable: true, sortable: true, size: '5%', attr: 'align=middle'},
		{ field: 'schedule_start_date', caption: 'Start date',  resizable: true, sortable: true, size: '75px'},
		{ field: 'schedule_start_time', caption: 'Start time',  resizable: true, sortable: true, size: '65px'},
		{ field: 'schedule_stop_date', caption: 'Stop date',  resizable: true, sortable: true, size: '75px'},
		{ field: 'schedule_stop_time', caption: 'Stop time',  resizable: true, sortable: true, size: '65px'},
		{ field: 'max_attempts', caption: 'Attempts', hidden: true, size: '1%'},
		{ field: 'monitored', caption: 'Monitored', hidden: true, sortable: true, size: '1%'},
		{ field: 'time_limit', caption: 'Time limit', hidden: true, size: '1%'},
		{ field: 'delivery', caption: 'Delivery', hidden: true, size: '1%'},					
		{ field: 'restrict_times', caption: 'Restrict times', hidden: true, size: '1%'},
		{ field: 'restrict_attempts', caption: 'Restrict attempts', hidden: true, size: '1%'},		
		{ field: 'session_name', caption: 'Session', hidden: true, size: '1%'},
		{ field: 'save_answers', caption: 'Save Answers', hidden: true, size: '1%'},
		{ field: 'save_answer_data', caption: 'Save Answer Data', hidden: true, size: '1%'},
		{ field: 'open_session', caption: 'Open', hidden: true, size: '1%'},
		{ field: 'session_timed', caption: 'Session_timed', hidden: true, size: '1%'},
		{ field: 'time_limit', caption: 'time_limit', hidden: true, size: '1%'},
		{ field: 'template_name', caption: 'Template', hidden: true, size: '1%'},
		{ field: 'permit_external_call', caption: 'permit_external_call', hidden: true, size: '1%'},
		{ field: 'modified_date', caption: 'Assessment modified', hidden: true, size: '1%'},
		{ field: 'block_count', caption: 'Number of blocks', hidden: true, size: '1%'},
		{ field: 'monitor_required', caption: 'Monitoring required', hidden: true, size: '1%'}		
	],
	onClick: function (target, eventData) {
		var record = this.get(eventData.recid);
		Window.LastAssessment=record.assessment_id;
		if(document.getElementById('grd_details').style.display=='block') {
			w2ui['grd_details'].clear();
			w2ui['grd_details'].add([
				{ recid: 1, setting: 'Assessment name', value: record.session_name },
				{ recid: 2, setting: 'Assessment modified', value: record.modified_date },
				{ recid: 3, setting: 'Assessment nr of blocks', value: record.block_count },
				{ recid: 4, setting: 'Assessment template', value: record.template_name },
				{ recid: 5, setting: 'Assessment monitoring required', value: record.monitoring_required},			
				{ recid: 6, setting: 'Schedule delivery', value: record.delivery },
				{ recid: 7, setting: 'Schedule monitored', value: record.monitored },
				{ recid: 8, setting: 'Schedule time limit', value: record.time_limit },
				{ recid: 9, setting: 'Schedule restrict times', value: record.restrict_times },
				{ recid: 10, setting: 'Schedule nr of attempts', value: record.restrict_attempts },
				{ recid: 11, setting: 'Schedule nr of max attempts', value: record.max_attempts },
			]);
		}
		if(document.getElementById('grd_progress').style.display=='block') {
			w2ui['grd_progress'].clear();		
			console.log('getting progress records'+Window.LastAssessment);
			w2ui['grd_progress'].load('_qmpprogress001.php'+'?assessmentid='+Window.LastAssessment);
		};
		return eventData;		
	},
});
$('#grd_details').w2grid({
	name:	'grd_details',
	header: 'Details',
	multiSearch: false,
	multiSort: false,
	show: {
		header         : false,  // indicates if header is visible
		toolbar        : false,  // indicates if toolbar is visible
		footer         : false,  // indicates if footer is visible
		columnHeaders  : true,   // indicates if columns is visible
		lineNumbers    : false,  // indicates if line numbers column is visible
		expandColumn   : false,  // indicates if expand column is visible
		selectColumn   : false,  // indicates if select column is visible
		emptyRecords   : true,   // indicates if empty records are visible
		toolbarReload  : false,   // indicates if toolbar reload button is visible
		toolbarColumns : false,   // indicates if toolbar columns button is visible
		toolbarSearch  : false,   // indicates if toolbar search controls are visible
		toolbarAdd     : false,   // indicates if toolbar add new button is visible
		toolbarDelete  : false,   // indicates if toolbar delete button is visible
		toolbarSave    : false,   // indicates if toolbar save button is visible
	},
	columns: [
		{ field: 'recid', caption: '#', size: '5%', resizable: true, sortable: true, attr: 'align=right'},	
		{ field: 'setting', caption: 'Setting', size: '50%', resizable: true, sortable: true},
		{ field: 'value', caption: 'Value', size: '45%' }
	]
});

$('#grd_progress').w2grid({
	name:	'grd_progress',
	header: 'Progress',
	autoLoad: false,	
	multiSearch: true,
	multiSort: true,
	searches: [				
		{ field: 'participant', caption: 'Participant', type: 'text' },
		{ field: 'member_group', caption: 'Group', type: 'text' },
		{ field: 'ip_address', caption: 'PC', type: 'text' },		
	],	
	show: {
		header         : false,  // indicates if header is visible
		toolbar        : true,  // indicates if toolbar is visible
		footer         : true,  // indicates if footer is visible
		columnHeaders  : true,   // indicates if columns is visible
		lineNumbers    : false,  // indicates if line numbers column is visible
		expandColumn   : false,  // indicates if expand column is visible
		selectColumn   : false,  // indicates if select column is visible
		emptyRecords   : true,   // indicates if empty records are visible
		toolbarReload  : true,   // indicates if toolbar reload button is visible
		toolbarColumns : true,   // indicates if toolbar columns button is visible
		toolbarSearch  : true,   // indicates if toolbar search controls are visible
		toolbarAdd     : false,   // indicates if toolbar add new button is visible
		toolbarDelete  : false,   // indicates if toolbar delete button is visible
		toolbarSave    : false,   // indicates if toolbar save button is visible
	},
	toolbar: {
		onClick: function (target, data) {
			if(target=='refresh') { w2ui['grd_progress'].reload(); }			
		}
	},
	columns: [
		{ field: 'participant', caption: 'Participant', size: '10%', resizable: true, sortable: true},	
		{ field: 'member_group', caption: 'Group', size: '10%', resizable: true, sortable: true},
		{ field: 'ip_address', caption: 'PC', size: '10%', resizable: true, sortable: true},
		{ field: 'still_going', caption: 'Active', size: '5%', resizable: true, sortable: true},
		{ field: 'when_started_date', caption: 'Start date', size: '75px', resizable: true, sortable: true},
		{ field: 'when_started_time', caption: 'Start time', size: '65px', resizable: true, sortable: true},
		{ field: 'last_modified_date', caption: 'Modified date', size: '65px', resizable: true, sortable: true, hidden: true},
		{ field: 'last_modified_time', caption: 'Modified', size: '65px', resizable: true, sortable: true},
		{ field: 'when_finished_date', caption: 'Finished date', size: '65px', resizable: true, sortable: true, hidden: true},
		{ field: 'when_finished_time', caption: 'Finished', size: '65px', resizable: true, sortable: true}
	]
});

w2ui['grd_schedules'].load('_qmpschedule007.php');
</script>
</html>