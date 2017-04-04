/**
 * Výkazy práce - timesheet
 * 
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůch
 */

/* Form new timesheet */
var form_report_store = new Ext.data.JsonStore({
	autoDestroy : true,
	proxy : new Ext.data.HttpProxy({
		method : 'POST',
		url : 'json.store'
	}),
	fields : [ 'p_pst_id', 'p_pst_name' ],
	listeners : {
		load : {
			fn : function(store, records, options) {
				if (records.length > 0) {
					form_report_add.getForm().findField('report_position').setValue(store.getAt(0).get('p_pst_id'), true);
				}
			}
		}
	}
});

var form_report_add = new Ext.form.FormPanel({
	baseCls : 'x-plain',
	labelWidth : 55,
	defaultType : 'textfield',
	monitorValid : true,
	items : [ new Ext.ux.form.SpinnerField({
		fieldLabel : 'Rok',
		name : 'report_add_year',
		minValue : new Date().getFullYear() - 1,
		maxValue : new Date().getFullYear() + 1,
		anchor : '100%'
	}), new Ext.ux.form.SpinnerField({
		fieldLabel : 'Měsíc',
		name : 'report_add_month',
		minValue : 1,
		maxValue : 12,
		anchor : '100%'
	}), {
		xtype : 'combo',
		mode : 'local',
		anchor : '100%',
		triggerAction : 'all',
		forceSelection : true,
		editable : false,
		allowBlank : false,
		blankText : 'Hledám pozice...',
		fieldLabel : 'Pozice',
		name : 'report_position',
		displayField : 'p_pst_name',
		valueField : 'p_pst_id',
		store : form_report_store
	}, {
		fieldLabel : 'wrk_id',
		name : 'wrk_id',
		hidden : true
	}, {
		fieldLabel : 'prj_id',
		name : 'prj_id',
		hidden : true
	} ]
});

var win_report_add = new Ext.Window({
	title : 'Nový výkaz',
	width : 400,
	height : 170,
	layout : 'fit',
	closable : false,
	modal : true,
	plain : true,
	bodyStyle : 'padding:5px;',
	items : form_report_add,
	buttons : [
			{
				text : 'OK',
				handler : function() {
					if (form_report_add.form.findField('report_position').getValue() != '') {
						gvar_pst_id = form_report_add.form.findField('report_position').getValue();
						gvar_wrk_id = form_report_add.form.findField('wrk_id').getValue();
						gvar_prj_id = form_report_add.form.findField('prj_id').getValue();
						gvar_tms_date = form_report_add.form.findField('report_add_year').getValue() + '/' + form_report_add.form.findField('report_add_month').getValue();
						grid_store.proxy.conn.url = 'index.php?json=layout.rightpanel.timesheets.grid.wrk_id:' + gvar_wrk_id + '.prj_id:' + gvar_prj_id + '.pst_id:' + gvar_pst_id + '.new:timesheet'
								+ '.tms_date:' + gvar_tms_date;
						win_report_add.hide();
						grid_store.load();
						grid.setTitle('Výkaz ' + gvar_tms_date);
						grid.show();
						Ext.getCmp('content-panel').layout.setActiveItem('timesheet-grid');
					} else
						Ext.Msg.alert('Chyba!', 'Vyplňte pozici na projektu?');
				}
			}, {
				text : 'Cancel',
				handler : function() {
					win_report_add.hide();
				}
			} ]
});

/* Left panel */

var treeLoader = new Ext.tree.TreeLoader({
	dataUrl : 'index.php?json=layout.leftpanel.timesheets.list',
	preloadChildren : true,
	clearOnLoad : false,
	listeners : {
		beforeload : {
			fn : function() {
				leftPanel_vykazy_timesheets.body.mask('Nahrávám...');
			}
		},
		load : {
			fn : function(store, records, option) {
				leftPanel_vykazy_timesheets.body.unmask();
			}
		}
	}
});

var menu1 = new Ext.menu.Menu({
	items : [ {
		text : 'I like Ext',
		checked : true
	}, '-', {
		text : 'Open With',
		menu : {
			items : [ {
				text : 'Notepad ++'
			}, {
				text : 'GIMP 2.0'
			}, {
				text : 'Firefox'
			} ]
		}
	}, '-', {
		text : 'Cut'
	}, {
		text : 'Copy'
	}, {
		text : 'Delete'
	}, '-', {
		text : 'Rename'
	} ]
});

var leftPanel_vykazy_timesheets = new Ext.ux.tree.TreeGrid({
	id : 'left-panel-vykazy-timesheets',
	columns : [ {
		header : 'Pracovník',
		dataIndex : 'display',
		width : 240
	}, {
		header : 'Hodin',
		width : 80,
		dataIndex : 'duration',
		align : 'right'
	}, {
		header : 'Task',
		width : 50,
		dataIndex : 'task',
		align : 'right',
		hidden : true
	}, {
		header : 'Asg',
		width : 50,
		dataIndex : 'tms_asg',
		align : 'right',
		hidden : true
	}, {
		header : 'Click',
		width : 50,
		dataIndex : 'click',
		align : 'right',
		hidden : true
	} ],
	tbar : [ {
		text : 'Obnovit',
		iconCls : 'refresh',
		handler : function() {
			leftPanel_vykazy_timesheets.getRootNode().reload();
		}
	} ],
	loader : treeLoader,
	loadMask : true,
	defaultSortable : false,
	enableSort : false,
	listeners : {
		click : {
			fn : function(node) {
				gvar_wrk_id = node.attributes.worker;
				gvar_prj_id = node.attributes.project;
				gvar_tms_date = node.attributes.task;
				gvar_tms_asg = node.attributes.tms_asg;
				if (node.attributes.click == 'report_edit') {
					showOpenTimesheet('Výkaz ' + node.attributes.display);
				}
				if (node.attributes.click == 'report_close') {
					showCloseTimesheet('Uzavřený výkaz ' + node.attributes.display);
				}
				if (node.attributes.click == 'report_add') {
					form_report_store.proxy.conn.url = 'index.php?json=layout.rightpanel.timesheets.formReportAddPositions.wrk_id:' + gvar_wrk_id + '.prj_id:' + gvar_prj_id;
					form_report_store.load();
					win_report_add.show();
					form_report_add.getForm().setValues({
						wrk_id : gvar_wrk_id,
						prj_id : gvar_prj_id,
						report_add_year : new Date().getFullYear(),
						report_add_month : new Date().getMonth() + 1,
						report_position : 'Hledám pozice...'
					});
				}
			}
		},
		contextmenu : {
			fn : function(node) {
				if (node.attributes.click == 'report_edit' || node.attributes.click == 'report_close') {
					node.select();
					menu1.show(node.ui.getAnchor());
				}
			}
		}
	}
});

if (gvar_user_role == 'ADMIN') {
	leftPanel_vykazy_timesheets.getTopToolbar().add({
		text : 'Pracovníci',
		iconCls : 'database_lightning',
		menu : [ {
			text : 'Všichni',
			iconCls : 'group',
			handler : function() {
				treeLoader.dataUrl = 'index.php?json=layout.leftpanel.timesheets.list';
				leftPanel_vykazy_timesheets.getRootNode().reload();
			}
		}, {
			text : 'Aktivní',
			iconCls : 'user_edit',
			handler : function() {
				treeLoader.dataUrl = 'index.php?json=layout.leftpanel.timesheets.list.workers:E';
				leftPanel_vykazy_timesheets.getRootNode().reload();
			}
		}, {
			text : 'Neaktivní',
			iconCls : 'user',
			handler : function() {
				treeLoader.dataUrl = 'index.php?json=layout.leftpanel.timesheets.list.workers:D';
				leftPanel_vykazy_timesheets.getRootNode().reload();
			}
		} ]
	});
}

function showOpenTimesheet(display) {
	grid_store.proxy.conn.url = 'index.php?json=layout.rightpanel.timesheets.grid.wrk_id:' + gvar_wrk_id + '.prj_id:' + gvar_prj_id + '.tms_asg:' + gvar_tms_asg + '.tms_date:' + gvar_tms_date;
	grid_store.load();
	grid.setTitle(display);
	Ext.getCmp('content-panel').layout.setActiveItem('timesheet-grid');
}

function showCloseTimesheet(display) {
	grid_store.proxy.conn.url = 'index.php?json=layout.rightpanel.timesheets.grid.wrk_id:' + gvar_wrk_id + '.prj_id:' + gvar_prj_id + '.tms_asg:' + gvar_tms_asg + '.tms_date:' + gvar_tms_date;
	grid_store.load();
	grid_noedit.setTitle(display);
	grid_noedit.show();
	if (gvar_user_role == 'ADMIN')
		grid_noedit.getTopToolbar().add({
			text : 'Odemknout',
			iconCls : 'report_edit',
			handler : function() {
				Ext.Ajax.request({
					url : 'index.php?unlock=layout.rightpanel.timesheets.grid',
					params : {
						'asg_id' : gvar_tms_asg,
						'tms_date' : gvar_tms_date,
						'wrk_id' : gvar_wrk_id,
						'prj_id' : gvar_prj_id,
						'pst_id' : gvar_pst_id
					},
					success : function(result, request) {
						var jsonData = Ext.util.JSON.decode(result.responseText);
						if (jsonData.result == 'UNLOCKED') {
							leftPanel_vykazy_timesheets.getRootNode().reload();
							showOpenTimesheet(grid_noedit.title.replace('Uzavřený výkaz', 'Výkaz'));
						}
						if (jsonData.result == 'KO') {
							Ext.MessageBox.alert('Failed', jsonData.row);
							rec.reject();
						}
					}
				});
			}

		});

	Ext.getCmp('content-panel').layout.setActiveItem('timesheet-noedit-grid');
}

var leftPanel_vykazy = {
	id : 'left-panel-vykazy',
	title : 'Výkazy',
	autoScroll : true,
	border : false,
	layout : 'fit',
	iconCls : 'report',
	items : leftPanel_vykazy_timesheets
};

/* Content panel */

var fm = Ext.form;

var grid_store = new Ext.data.JsonStore({
	autoDestroy : true,
	proxy : new Ext.data.HttpProxy({
		method : 'POST',
		url : 'store.json'
	}),
	root : 'timesheets',
	fields : [ 'tms_desc', 'tms_time', 'tms_date', 'tms_state', 'tms_idat', 'tms_id', 'wrk_id', 'prj_id' ],
	listeners : {
		beforeload : {
			fn : function() {
				var recTime = sumRecordedTime();
				Ext.fly(countReportedTime.getEl()).update('Vykázáno: ' + recTime);
				grid.body.mask('Nahrávám...');

				Ext.fly(countReportedTime_noedit.getEl()).update('Vykázáno: ' + recTime);
				grid_noedit.body.mask('Nahrávám...');
			}
		},
		load : {
			fn : function(store, records) {
				var recTime = sumRecordedTime();
				Ext.fly(countReportedTime.getEl()).update('Vykázáno: ' + recTime);
				grid.body.unmask();

				Ext.fly(countReportedTime_noedit.getEl()).update('Vykázáno: ' + recTime);
				grid_noedit.body.unmask();
			}
		}
	}
});

var countReportedTime = new Ext.Toolbar.TextItem('Vykázáno:');

var grid_statusbar = new Ext.ux.StatusBar({
	defaultText : '',
	busyText : 'Pracuji...',
	statusAlign : 'left',
	items : [ '->', '-', countReportedTime ]
});

var grid = new Ext.grid.EditorGridPanel({
	id : 'timesheet-grid',
	title : 'Výkaz',
	store : grid_store,
	tools : [ {
		type : 'close',
		tooltip : 'Zavřít',
		handler : function(event, toolEl, owner, tool) {
			owner.hide();
			grid_store.removeAll();
			leftPanel_vykazy_timesheets.getRootNode().reload();
			Ext.getCmp('content-panel').layout.setActiveItem('start-panel');
		}
	} ],
	tbar : [ {
		text : 'Vytisknout',
		iconCls : 'printer',
		handler : function() {
			var pdf_url = 'index.php?pdf=timesheet.wrk_id:' + gvar_wrk_id + '.prj_id:' + gvar_prj_id + '.tms_asg:' + gvar_tms_asg + '.pst_id:' + gvar_pst_id + '.tms_date:' + gvar_tms_date;
			window.open(pdf_url);
		}
	}, {
		text : 'Uzamknout',
		iconCls : 'report_close',
		handler : function() {
			Ext.Ajax.request({
				url : 'index.php?lock=layout.rightpanel.timesheets.grid',
				params : {
					'asg_id' : gvar_tms_asg,
					'tms_date' : gvar_tms_date,
					'wrk_id' : gvar_wrk_id,
					'prj_id' : gvar_prj_id,
					'pst_id' : gvar_pst_id
				},
				success : function(result, request) {
					var jsonData = Ext.util.JSON.decode(result.responseText);
					if (jsonData.result == 'LOCKED') {
						leftPanel_vykazy_timesheets.getRootNode().reload();
						showCloseTimesheet(grid.title.replace('Výkaz', 'Uzavřený výkaz'));
					}
					if (jsonData.result == 'KO') {
						Ext.MessageBox.alert('Failed', jsonData.row);
						rec.reject();
					}
				}
			});
		}
	} ],
	bbar : grid_statusbar,
	columns : [ {
		header : 'Den',
		width : 80,
		sortable : true,
		dataIndex : 'tms_date'
	}, {
		header : 'Hodiny',
		width : 80,
		sortable : true,
		dataIndex : 'tms_time',
		editor : new fm.TimeField({
			format : 'H:i',
			minValue : '00:00'
		})
	}, {
		header : 'Popis',
		id : 'tms_desc',
		width : 160,
		sortable : true,
		dataIndex : 'tms_desc',
		editor : new fm.TextField({
			allowBlank : false
		})
	}, {
		header : 'Stav',
		width : 50,
		sortable : true,
		hidden : true,
		dataIndex : 'tms_state'
	}, {
		xtype : 'actioncolumn',
		width : 30,
		items : [ {
			icon : 'style/images/default/grid/bin_closed.png',
			tooltip : 'Smazat řádek',
			handler : function(grid, rowIndex, colIndex) {
				var rec = grid_store.getAt(rowIndex);
				if (rec.get('tms_id') != null) {
					Ext.Msg.confirm('Haló!', 'Opravdu chcete smazat tento řádek?', function(btn, text) {
						if (btn == 'yes') {
							deleteRow(rec);
						}
					});
				}
			}
		} ]
	} ],
	stripeRows : true,
	autoExpandColumn : 'tms_desc',
	stateful : true,
	stateId : 'grid',
	listeners : {
		afteredit : {
			fn : function(data) {
				var rec = data.record;
				if (data.value == '') {
					rec.reject();
				} else {
					grid_statusbar.showBusy();
					Ext.Ajax.request({
						url : 'index.php?save=layout.rightpanel.timesheets.grid',
						success : function(result, request) {
							var jsonData = Ext.util.JSON.decode(result.responseText);
							if (jsonData.result == 'NEW') {
								rec.set('tms_id', jsonData.row.tms_id);
								rec.set('tms_state', 'W');
							}
							if (jsonData.result == 'KO') {
								Ext.MessageBox.alert('Failed', jsonData.row);
								rec.reject();
							} else {
								rec.commit();
								Ext.fly(countReportedTime.getEl()).update('Vykázáno: ' + sumRecordedTime());
								grid_statusbar.setStatus({
									text : 'Uloženo',
									iconCls : 'x-status-valid',
									clear : true
								});
							}
						},
						failure : function(result, request) {
							grid_statusbar.setStatus({
								text : 'Chyba!',
								iconCls : 'x-status-error',
								clear : true
							});
							Ext.MessageBox.alert('Failed', result.responseText);
						},
						params : {
							'field' : data.field,
							'row' : data.row,
							'value' : data.value,
							'tms_id' : rec.get('tms_id'),
							'tms_idat' : rec.get('tms_idat'),
							'wrk_id' : rec.get('wrk_id'),
							'prj_id' : rec.get('prj_id'),
							'pst_id' : gvar_pst_id,
							'asg_id' : gvar_tms_asg
						}
					});
				}
			}
		}
	}
});

var countReportedTime_noedit = new Ext.Toolbar.TextItem('Vykázáno:');

var grid_noedit_statusbar = new Ext.ux.StatusBar({
	defaultText : '',
	busyText : 'Pracuji...',
	statusAlign : 'left',
	items : [ '->', '-', countReportedTime_noedit ]
});

var grid_noedit = new Ext.grid.EditorGridPanel({
	id : 'timesheet-noedit-grid',
	title : 'Výkaz',
	store : grid_store,
	tools : [ {
		type : 'close',
		tooltip : 'Zavřít',
		handler : function(event, toolEl, owner, tool) {
			owner.hide();
			grid_store.removeAll();
			Ext.getCmp('content-panel').layout.setActiveItem('start-panel');
		}
	} ],
	tbar : [ {
		text : 'Vytisknout',
		iconCls : 'printer',
		handler : function() {
			var pdf_url = 'index.php?pdf=timesheet.wrk_id:' + gvar_wrk_id + '.prj_id:' + gvar_prj_id + '.tms_asg:' + gvar_tms_asg + '.pst_id:' + gvar_pst_id + '.tms_date:' + gvar_tms_date;
			window.open(pdf_url);
		}
	} ],
	bbar : grid_noedit_statusbar,
	columns : [ {
		header : 'Den',
		width : 80,
		sortable : true,
		dataIndex : 'tms_date'
	}, {
		header : 'Hodiny',
		width : 80,
		sortable : true,
		dataIndex : 'tms_time'
	}, {
		header : 'Popis',
		id : 'tms_desc',
		width : 160,
		sortable : true,
		dataIndex : 'tms_desc'
	}, {
		header : 'Stav',
		width : 50,
		sortable : true,
		hidden : true,
		dataIndex : 'tms_state'
	} ],
	stripeRows : true,
	autoExpandColumn : 'tms_desc',
	stateful : true,
	stateId : 'grid'
});

function sumRecordedTime() {
	var sum = 0;
	for (i = 0; i < grid_store.getCount(); i++) {
		var min = grid_store.getAt(i).get('tms_time');
		if (min == null || min == '')
			min = '00:00';
		sum += parseInt(min.substr(0, min.indexOf(':')), 10) * 60;
		sum += parseInt(min.substr(min.indexOf(':') + 1), 10);
	}
	var hours = Math.floor(sum / 60).toString();
	var minutes = '0' + (sum % 60).toString();
	return hours + ':' + minutes.substring(minutes.length - 2, minutes.length);
}

function deleteRow(rec) {
	grid_statusbar.showBusy();
	Ext.Ajax.request({
		url : 'index.php?delete=layout.rightpanel.timesheets.grid',
		success : function(result, request) {
			rec.set('tms_id', '');
			rec.set('tms_time', '');
			rec.set('tms_desc', '');
			rec.commit();
			grid_statusbar.setStatus({
				text : 'Uloženo',
				iconCls : 'x-status-valid',
				clear : true
			});
			Ext.fly(countReportedTime.getEl()).update('Vykázáno: ' + sumRecordedTime());
		},
		failure : function(result, request) {
			grid_statusbar.setStatus({
				text : 'Chyba!',
				iconCls : 'x-status-error',
				clear : true
			});
			Ext.MessageBox.alert('Failed', result.responseText);
		},
		params : {
			'tms_id' : rec.get('tms_id')
		}
	});
}
