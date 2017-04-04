/**
 * Výkazy práce - projects
 * 
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůch
 */

// Left Content
var projectsList_treeloader = new Ext.tree.TreeLoader({
	dataUrl : 'index.php?json=layout.leftpanel.projects.list',
	preloadChildren : true,
	clearOnLoad : false,
	listeners : {
		beforeload : {
			fn : function() {
				projectsList.body.mask('Nahrávám...');
			}
		},
		load : {
			fn : function(store, records, option) {
				projectsList.body.unmask();
			}
		}
	}
});

var projectsList = new Ext.ux.tree.TreeGrid({
	id : 'left-panel-projekty-list',
	loader : projectsList_treeloader,
	enableSort : true,
	tbar : [ {
		text : 'Nový projekt',
		iconCls : 'drop-add',
		handler : function() {
			// TODO
			alert('Tady se bude zakládat nový projekt...');
		}
	}, {
		text : 'Obnovit',
		iconCls : 'refresh',
		handler : function() {
			projectsList.getRootNode().reload();
		}
	} ],
	columns : [ {
		header : 'Projekt',
		dataIndex : 'project',
		sortable : false,
		width : 320
	}, {
		header : 'ID',
		dataIndex : 'id',
		align : 'right',
		width : 50,
		hidden : true
	}, {
		header : 'Click',
		dataIndex : 'click',
		align : 'right',
		sortable : false,
		width : 50,
		hidden : true
	} ],
	listeners : {
		click : {
			fn : function(node) {
				if (node.attributes.click == 'edit') {
					editProjectShow(node.attributes.id, node.attributes.project);
				}
			}
		}
	}
});

function editProjectShow(id, name) {
	gvar_prj_id = id;
	projectEdit_store.proxy.conn.url = 'index.php?json=layout.rightpanel.projects.project.prj_id:' + gvar_prj_id;
	projectEdit_store.load();
	projectsMonitoringMsg_store.proxy.conn.url = 'index.php?json=layout.rightpanel.projects.monitors.prj_id:' + gvar_prj_id;
	projectsMonitoringMsg_store.load();
	projectsEdit_panel.setTitle(name);
	projectsEdit_panel.show();
	Ext.getCmp('content-panel').layout.setActiveItem('project-layout');
}

var leftPanel_projekty = {
	id : 'left-panel-projekty',
	title : 'Projekty',
	autoScroll : true,
	border : false,
	layout : 'fit',
	iconCls : 'brick',
	items : projectsList
};

/*******************************************************************************
 * Rigt content
 ******************************************************************************/

// Edit project container
var projectEdit_statusbar = new Ext.ux.StatusBar({
	defaultText : '',
	busyText : 'Pracuji...',
	statusAlign : 'left',
	items : [ '->', '&nbsp;' ]
});

var projectEdit_store = new Ext.data.JsonStore({
	autoDestroy : true,
	proxy : new Ext.data.HttpProxy({
		method : 'POST',
		url : 'store.json'
	}),
	root : 'project',
	fields : [ 'prj_id', 'prj_state', 'prj_name', 'prj_regc', 'prj_nazev', 'prj_podpora' ],
	listeners : {
		load : {
			fn : function(store, records, options) {
				if (records.length > 0) {
					projectEdit_form.getForm().setValues({
						prj_id : store.getAt(0).get('prj_id'),
						prj_state : store.getAt(0).get('prj_state'),
						prj_name : store.getAt(0).get('prj_name'),
						prj_regc : store.getAt(0).get('prj_regc'),
						prj_nazev : store.getAt(0).get('prj_nazev'),
						prj_podpora : store.getAt(0).get('prj_podpora')
					});
				}
			}
		},
		clear : {
			fn : function(store) {
				projectEdit_form.getForm().setValues({
					prj_id : '',
					prj_state : '',
					prj_name : '',
					prj_regc : '',
					prj_nazev : '',
					prj_podpora : ''
				});
			}
		}
	}
});

var projectEdit_form = new Ext.form.FormPanel({
	labelWidth : 150,
	url : 'index.php?save=layout.rightpanel.projects.project',
	frame : true,
	height : 200,
	defaultType : 'textfield',
	monitorValid : true,
	items : [ {
		name : 'prj_id',
		hidden : true
	}, new Ext.form.ComboBox({
		fieldLabel : 'Status',
		hiddenName : 'prj_state',
		store : new Ext.data.ArrayStore({
			fields : [ 'prj_state_id', 'prj_state_name' ],
			data : [ [ 'E', 'Aktivní' ], [ 'C', 'Ukončený' ], [ 'D', 'Zrušený' ] ]
		}),
		valueField : 'prj_state_id',
		displayField : 'prj_state_name',
		typeAhead : true,
		mode : 'local',
		triggerAction : 'all',
		selectOnFocus : true,
		width : 100
	}), {
		fieldLabel : 'Projekt',
		allowBlank : false,
		name : 'prj_name',
		width : 220
	}, {
		fieldLabel : 'Registrační číslo',
		allowBlank : false,
		name : 'prj_regc',
		width : 220
	}, new Ext.form.TextArea({
		fieldLabel : 'Název',
		name : 'prj_nazev',
		width : 600,
		height : 80
	}), {
		fieldLabel : 'Příjemce podpory',
		allowBlank : false,
		name : 'prj_podpora',
		width : 600
	} ]
});

// Monitoring reports
var projectsMonitoringMsg_store = new Ext.data.JsonStore({
	autoDestroy : true,
	proxy : new Ext.data.HttpProxy({
		method : 'POST',
		url : 'store.json'
	}),
	root : 'monitors',
	fields : [ 'mnt_id', 'mnt_name', 'mnt_esty', 'mnt_estm', 'mnt_lety', 'mnt_letm' ]
});

var projectsMonitoringMsg_row = Ext.data.Record.create([ 'mnt_id', 'mnt_name', 'mnt_esty', 'mnt_estm', 'mnt_lety', 'mnt_letm' ]);

var projectMonitorsGridText_editor = new fm.TextField({
	allowBlank : false
});

var projectMonitorsGridYear_editor = new Ext.ux.form.SpinnerField({
	allowBlank : false,
	minValue : new Date().getFullYear() - 1,
	maxValue : new Date().getFullYear() + 1
});

var projectMonitorsGridMonth_editor = new Ext.ux.form.SpinnerField({
	allowBlank : false,
	minValue : 1,
	maxValue : 12
});

var projectsMonitoringMsg_grid = new Ext.grid.EditorGridPanel({
	title : 'Monitorovací zprávy',
	store : projectsMonitoringMsg_store,
	autoHeight : true,
	height : 'auto',
	stripeRows : true,
	autoExpandColumn : 'mnt_name',
	tbar : [ {
		text : 'Nová zpráva',
		iconCls : 'drop-add',
		handler : function() {
			projectEdit_statusbar.showBusy();
			Ext.Ajax.request({
				url : 'index.php?save=layout.rightpanel.projects.newMonitor',
				success : function(result) {
					var answer = Ext.util.JSON.decode(result.responseText);
					if (answer.success) {
						projectsMonitoringMsg_store.add(new projectsMonitoringMsg_row({
							mnt_id : answer.monitor.mnt_id,
							mnt_name : answer.monitor.mnt_name,
							mnt_esty : answer.monitor.mnt_esty,
							mnt_estm : answer.monitor.mnt_estm,
							mnt_lety : answer.monitor.mnt_lety,
							mnt_letm : answer.monitor.mnt_letm
						}));
						projectEdit_statusbar.setStatus({
							text : 'Uloženo',
							iconCls : 'x-status-valid',
							clear : true
						});
					} else {
						projectEdit_statusbar.setStatus({
							text : answer.error,
							iconCls : 'x-status-error',
							clear : true
						});
					}
				},
				failure : function(result, request) {
					projectEdit_statusbar.setStatus({
						text : result.responseText,
						iconCls : 'x-status-error',
						clear : true
					});
				},
				params : {
					'prj_id' : gvar_prj_id
				}
			});
		}
	} ],
	columns : [ {
		header : 'mnt_id',
		hidden : true,
		dataIndex : 'mnt_id'
	}, {
		header : 'Číslo zprávy',
		id : 'mnt_name',
		width : 140,
		sortable : true,
		dataIndex : 'mnt_name',
		editor : projectMonitorsGridText_editor
	}, {
		header : 'Zahrnout výkazy od (měsíc)',
		sortable : true,
		width : 180,
		align : 'right',
		dataIndex : 'mnt_estm',
		editor : projectMonitorsGridMonth_editor
	}, {
		header : '(rok)',
		width : 120,
		align : 'right',
		sortable : true,
		dataIndex : 'mnt_esty',
		editor : projectMonitorsGridYear_editor
	}, {
		header : 'Zahrnout výkazy do (měsíc)',
		width : 180,
		align : 'right',
		sortable : true,
		dataIndex : 'mnt_letm',
		editor : projectMonitorsGridMonth_editor
	}, {
		header : '(rok)',
		width : 120,
		align : 'right',
		sortable : true,
		dataIndex : 'mnt_lety',
		editor : projectMonitorsGridYear_editor
	}, {
		xtype : 'actioncolumn',
		width : 30,
		items : [ {
			icon : 'style/images/default/grid/bin_closed.png',
			tooltip : 'Smazat řádek',
			handler : function(grid, rowIndex, colIndex) {
				var rec = projectsMonitoringMsg_store.getAt(rowIndex);
				Ext.Msg.confirm('Haló!', 'Opravdu chcete smazat tento řádek?', function(btn, text) {
					if (btn == 'yes') {
						projectEdit_statusbar.showBusy();
						Ext.Ajax.request({
							url : 'index.php?delete=layout.rightpanel.projects.monitor',
							success : function(result) {
								var answer = Ext.util.JSON.decode(result.responseText);
								if (answer.success) {
									projectsMonitoringMsg_store.removeAt(rowIndex);
									projectEdit_statusbar.setStatus({
										text : 'Odstraněno',
										iconCls : 'x-status-valid',
										clear : true
									});
								} else {
									projectEdit_statusbar.setStatus({
										text : answer.error,
										iconCls : 'x-status-error',
										clear : true
									});
								}
							},
							failure : function(result, request) {
								projectEdit_statusbar.setStatus({
									text : result.responseText,
									iconCls : 'x-status-error',
									clear : true
								});
							},
							params : {
								'mnt_id' : rec.get('mnt_id')
							}
						});
					}
				});
			}
		} ]
	} ],
	listeners : {
		afteredit : {
			fn : function(data) {
				var rec = data.record;
				if (data.value == '') {
					rec.reject();
				} else {
					projectEdit_statusbar.showBusy();
					Ext.Ajax.request({
						url : 'index.php?save=layout.rightpanel.projects.monitor',
						success : function(result, request) {
							var answer = Ext.util.JSON.decode(result.responseText);
							if (answer.success) {
								rec.commit();
								projectEdit_statusbar.setStatus({
									text : 'Uloženo',
									iconCls : 'x-status-valid',
									clear : true
								});
							} else {
								projectEdit_statusbar.setStatus({
									text : answer.error,
									iconCls : 'x-status-error',
									clear : true
								});
							}
						},
						failure : function(result, request) {
							projectEdit_statusbar.setStatus({
								text : result.responseText,
								iconCls : 'x-status-error',
								clear : true
							});
						},
						params : {
							'field' : data.field,
							'value' : data.value,
							'mnt_id' : rec.get('mnt_id')
						}
					});
				}
			}
		}
	}
});

// Main project layout
var projectsEdit_panel = new Ext.Panel({
	id : 'project-layout',
	title : 'project.panel',
	region : 'center',
	margins : '35 5 5 0',
	layout : 'vbox',
	layoutConfig : {
		align : 'stretch',
		pack : 'start'
	},
	items : [ projectEdit_form, projectsMonitoringMsg_grid ],
	bbar : projectEdit_statusbar,
	tools : [ {
		type : 'close',
		tooltip : 'Zavřít',
		handler : function(event, toolEl, owner, tool) {
			owner.hide();
			projectEdit_store.removeAll();
			projectsMonitoringMsg_store.removeAll();
			Ext.getCmp('content-panel').layout.setActiveItem('start-panel');
		}
	} ],
	tbar : [ {
		text : 'Uložit',
		iconCls : 'disk',
		handler : function() {
			if (projectEdit_form.getForm().isValid()) {
				projectEdit_statusbar.showBusy();
				projectEdit_form.getForm().submit({
					success : function(form, answer) {
						projectsList.getRootNode().reload();
						leftPanel_vykazy_timesheets.getRootNode().reload();
						projectEdit_statusbar.setStatus({
							text : 'Uloženo',
							iconCls : 'x-status-valid',
							clear : true
						});
					},
					failure : function(form, answer) {
						projectEdit_statusbar.setStatus({
							text : answer.result.error,
							iconCls : 'x-status-error',
							clear : true
						});
					}
				});
			} else
				Ext.Msg.alert('Chyba', 'Opravte prosím chyby formuláře');
		}
	}, {
		text : 'Smazat',
		iconCls : 'bin_closed',
		handler : function() {
			projectEdit_statusbar.showBusy();
			Ext.Ajax.request({
				url : 'index.php?delete=layout.rightpanel.workers.worker',
				success : function(result) {
					var answer = Ext.util.JSON.decode(result.responseText);
					if (answer.success) {
						workersList.getRootNode().reload();
						leftPanel_vykazy_timesheets.getRootNode().reload();
						projectEdit_statusbar.setStatus({
							text : 'Odstraněno',
							iconCls : 'x-status-valid',
							clear : true
						});
						Ext.getCmp('content-panel').layout.setActiveItem('start-panel');
					} else {
						projectEdit_statusbar.setStatus({
							text : answer.error,
							iconCls : 'x-status-error',
							clear : true
						});
					}
				},
				failure : function(result, request) {
					projectEdit_statusbar.setStatus({
						text : result.responseText,
						iconCls : 'x-status-error',
						clear : true
					});
				},
				params : {
					'wrk_id' : gvar_wrk_id
				}
			});
		}
	} ]
});
