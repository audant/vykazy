/**
 * Výkazy práce - workers
 * 
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůch
 */

/* Left Content */
var workersList_treeloader = new Ext.tree.TreeLoader({
	dataUrl : 'index.php?json=layout.leftpanel.workers.list',
	preloadChildren : true,
	clearOnLoad : false,
	listeners : {
		beforeload : {
			fn : function() {
				workersList.body.mask('Nahrávám...');
			}
		},
		load : {
			fn : function(store, records, option) {
				workersList.body.unmask();
			}
		}
	}

});

var workersList = new Ext.ux.tree.TreeGrid({
	id : 'left-panel-pracovnici-list',
	loader : workersList_treeloader,
	enableSort : true,
	tbar : [ {
		text : 'Nový pracovník',
		iconCls : 'drop-add',
		handler : function() {
			workersNewWorker_form.getForm().setValues({
				wrk_name : ''
			});
			workersNewWorker_win.show();
		}
	}, {
		text : 'Obnovit',
		iconCls : 'refresh',
		handler : function() {
			workersList.getRootNode().reload();
		}
	} ],
	columns : [ {
		header : 'Pracovník',
		dataIndex : 'worker',
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
		width : 50,
		hidden : true
	} ],
	listeners : {
		click : {
			fn : function(node) {
				if (node.attributes.click == 'edit') {
					editWorkerShow(node.attributes.id, node.attributes.worker);
				}
			}
		}
	}
});

function editWorkerShow(id, name) {
	gvar_wrk_id = id;
	gvar_wrk_name = name;
	workersEditProperty_store.proxy.conn.url = 'index.php?json=layout.rightpanel.workers.worker.wrk_id:' + gvar_wrk_id;
	workersEditProperty_store.load();

	workersEditGrid_store.proxy.conn.url = 'index.php?json=layout.rightpanel.workers.grid.wrk_id:' + gvar_wrk_id;
	workersEditGrid_store.load();

	workersEditLayout.setTitle(gvar_wrk_name);
	workersEditLayout.show();
	Ext.getCmp('content-panel').layout.setActiveItem('workers-layout');
}

var leftPanel_pracovnici = {
	id : 'left-panel-pracovnici',
	title : 'Pracovníci',
	autoScroll : true,
	border : false,
	layout : 'fit',
	iconCls : 'user',
	items : workersList
};

/* New worker */
var workersNewWorker_form = new Ext.form.FormPanel({
	labelWidth : 120,
	url : 'index.php?save=layout.leftpanel.workers.newWorker',
	defaultType : 'textfield',
	baseCls : 'x-plain',
	monitorValid : true,
	items : [ {
		fieldLabel : 'Příjmení a jméno',
		name : 'wrk_name',
		width : 225
	} ]
});

var workersNewWorker_win = new Ext.Window({
	title : 'Nový pracovník',
	width : 400,
	height : 110,
	layout : 'fit',
	closable : false,
	modal : true,
	plain : true,
	bodyStyle : 'padding:5px;',
	items : workersNewWorker_form,
	buttons : [ {
		text : 'OK',
		handler : function() {
			if (workersNewWorker_form.getForm().isValid()) {
				workersEditGrid_statusbar.showBusy();
				workersNewWorker_form.getForm().submit({
					success : function(form, answer) {
						workersList.getRootNode().reload();
						leftPanel_vykazy_timesheets.getRootNode().reload();
						workersNewWorker_win.hide();
						editWorkerShow(answer.result.worker.wrk_id, answer.result.worker.wrk_name);
						Ext.MessageBox.show({
							title : 'Pozor',
							msg : 'Pro nového uživatele "' + answer.result.worker.wrk_name + '"<br>bylo automaticky vytvořeno heslo: ' + answer.result.worker.password,
							icon : Ext.MessageBox.WARNING
						});
						workersEditGrid_statusbar.setStatus({
							text : 'Uloženo',
							iconCls : 'x-status-valid',
							clear : true
						});
					},
					failure : function(form, answer) {
						workersNewWorker_win.hide();
						Ext.MessageBox.show({
							title : 'Chyba',
							msg : answer.result.error,
							icon : Ext.MessageBox.ERROR
						});
						workersEditGrid_statusbar.setStatus({
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
		text : 'Cancel',
		handler : function() {
			workersNewWorker_win.hide();
		}
	} ],
	listeners : {
		show : {
			fn : function(component) {
				workersNewWorker_form.getForm().findField('wrk_name').focus(true, 100);
			}
		}
	}
});

/* Right Content - Property */
var workersEditProperty_store = new Ext.data.JsonStore({
	autoDestroy : true,
	proxy : new Ext.data.HttpProxy({
		method : 'POST',
		url : 'store.json'
	}),
	root : 'worker',
	fields : [ 'wrk_id', 'wrk_state', 'wrk_name', 'wrk_nick', 'wrk_pass', 'wrk_role' ],
	listeners : {
		load : {
			fn : function(store, records, options) {
				if (records.length > 0) {
					workersEditForm.getForm().setValues({
						wrk_id : store.getAt(0).get('wrk_id'),
						wrk_state : store.getAt(0).get('wrk_state'),
						wrk_role : store.getAt(0).get('wrk_role'),
						wrk_name : store.getAt(0).get('wrk_name'),
						wrk_nick : store.getAt(0).get('wrk_nick'),
						wrk_pass : ''
					});
				}
			}
		},
		clear : {
			fn : function(store) {
				workersEditForm.getForm().setValues({
					wrk_id : '',
					wrk_state : '',
					wrk_role : '',
					wrk_name : '',
					wrk_nick : '',
					wrk_pass : ''
				});
			}
		}
	}
});

var workersEditForm = new Ext.form.FormPanel({
	labelWidth : 150,
	url : 'index.php?save=layout.rightpanel.workers.worker',
	frame : true,
	height : 145,
	defaults : {
		width : 220
	},
	defaultType : 'textfield',
	monitorValid : true,
	items : [ {
		name : 'wrk_id',
		hidden : true
	}, new Ext.form.ComboBox({
		fieldLabel : 'Status',
		hiddenName : 'wrk_state',
		store : new Ext.data.ArrayStore({
			fields : [ 'wrk_state_id', 'wrk_state_name' ],
			data : [ [ 'E', 'Aktivní' ], [ 'D', 'Neaktivní' ] ]
		}),
		valueField : 'wrk_state_id',
		displayField : 'wrk_state_name',
		typeAhead : true,
		mode : 'local',
		triggerAction : 'all',
		selectOnFocus : true,
		width : 100
	}), new Ext.form.ComboBox({
		fieldLabel : 'Oprávnění',
		hiddenName : 'wrk_role',
		store : new Ext.data.ArrayStore({
			fields : [ 'wrk_role_id', 'wrk_role_name' ],
			data : [ [ 'USER', 'Uživatel' ], [ 'ADMIN', 'Administrátor' ] ]
		}),
		valueField : 'wrk_role_id',
		displayField : 'wrk_role_name',
		typeAhead : true,
		mode : 'local',
		triggerAction : 'all',
		selectOnFocus : true,
		width : 100
	}), {
		fieldLabel : 'Příjmení a jméno',
		allowBlank : false,
		name : 'wrk_name'
	}, {
		fieldLabel : 'Přihlašovací jméno',
		allowBlank : false,
		name : 'wrk_nick',
		minLength : 4
	}, {
		fieldLabel : 'Heslo',
		name : 'wrk_pass',
		inputType : 'password',
		minLength : 4
	} ]
});

/* Right Content - new assignement */
var workersNewAsgPrj_store = new Ext.data.JsonStore({
	autoDestroy : true,
	proxy : new Ext.data.HttpProxy({
		method : 'POST',
		url : 'index.php?json=layout.rightpanel.workers.workersNewAsgPrj'
	}),
	fields : [ 'r_prj_id', 'r_prj_name' ],
	listeners : {
		load : {
			fn : function(store, records, options) {
				if (records.length > 0) {
					store.sort('r_prj_name', 'ASC');
					workersNewAsg_form.getForm().findField('r_prj_id').setValue(store.getAt(0).get('r_prj_id'), true);
				}
			}
		}
	}
});

var workersNewAsgPst_store = new Ext.data.JsonStore({
	autoDestroy : true,
	proxy : new Ext.data.HttpProxy({
		method : 'POST',
		url : 'index.php?json=layout.rightpanel.workers.workersNewAsgPst'
	}),
	fields : [ 'p_pst_id', 'p_pst_code', 'p_pst_name' ],
	listeners : {
		load : {
			fn : function(store, records, options) {
				if (records.length > 0) {
					store.sort('p_pst_name', 'ASC');
					workersNewAsg_form.getForm().findField('p_pst_id').setValue(store.getAt(0).get('p_pst_id'), true);
				}
			}
		}
	}
});

var workersNewAsg_form = new Ext.form.FormPanel({
	labelWidth : 60,
	url : 'index.php?save=layout.rightpanel.workers.newAssignment',
	defaultType : 'textfield',
	baseCls : 'x-plain',
	monitorValid : true,
	items : [ {
		name : 'wrk_id',
		hidden : true
	}, new Ext.form.ComboBox({
		fieldLabel : 'Projekt',
		hiddenName : 'r_prj_id',
		store : workersNewAsgPrj_store,
		valueField : 'r_prj_id',
		displayField : 'r_prj_name',
		typeAhead : true,
		mode : 'local',
		triggerAction : 'all',
		selectOnFocus : false,
		anchor : '100%'
	}), new Ext.form.ComboBox({
		fieldLabel : 'Pozice',
		hiddenName : 'p_pst_id',
		store : workersNewAsgPst_store,
		valueField : 'p_pst_id',
		displayField : 'p_pst_name',
		typeAhead : true,
		mode : 'local',
		triggerAction : 'all',
		selectOnFocus : true,
		anchor : '100%'
	}) ]
});

var workersNewAsg_win = new Ext.Window({
	title : 'Přiřadit na projekt',
	width : 400,
	height : 135,
	layout : 'fit',
	closable : false,
	modal : true,
	plain : true,
	bodyStyle : 'padding:5px;',
	items : workersNewAsg_form,
	buttons : [ {
		text : 'OK',
		handler : function() {
			if (workersNewAsg_form.getForm().isValid()) {
				workersEditGrid_statusbar.showBusy();
				workersNewAsg_form.getForm().submit({
					success : function(form, answer) {
						var workersNewAsgPrj_id = workersNewAsgPrj_store.find('r_prj_id', answer.result.asignment.asg_prj);
						var workersNewAsgPst_id = workersNewAsgPst_store.find('p_pst_id', answer.result.asignment.asg_pozice);
						workersNewAsg_win.hide();
						workersEditGrid_store.add(new workersEditGrid_row({
							a_asg_id : answer.result.asignment.asg_id,
							a_asg_prj : answer.result.asignment.asg_prj,
							a_asg_pozice : answer.result.asignment.asg_pozice,
							a_asg_pracpom : answer.result.asignment.asg_pracpom,
							a_asg_uvazek : answer.result.asignment.asg_uvazek,
							a_asg_dalsiuvaz : answer.result.asignment.asg_dalsiuvaz,
							a_asg_dalsicin : answer.result.asignment.asg_dalsicin,
							r_prj_name : workersNewAsgPrj_store.getAt(workersNewAsgPrj_id).get('r_prj_name'),
							p_pst_code : workersNewAsgPst_store.getAt(workersNewAsgPst_id).get('p_pst_code'),
							p_pst_name : workersNewAsgPst_store.getAt(workersNewAsgPst_id).get('p_pst_name')
						}));
						leftPanel_vykazy_timesheets.getRootNode().reload();
						workersEditGrid_statusbar.setStatus({
							text : 'Uloženo',
							iconCls : 'x-status-valid',
							clear : true
						});
					},
					failure : function(form, answer) {
						workersNewAsg_win.hide();
						workersEditGrid_statusbar.setStatus({
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
		text : 'Cancel',
		handler : function() {
			workersNewAsg_win.hide();
		}
	} ],
	listeners : {
		show : {
			fn : function(component) {
				workersNewAsg_form.getForm().findField('r_prj_id').focus(true, 100);
			}
		}
	}
});

/* Right Content - Grid */
var workersEditGrid_store = new Ext.data.JsonStore({
	autoDestroy : true,
	proxy : new Ext.data.HttpProxy({
		method : 'POST',
		url : 'store.json'
	}),
	root : 'assignment',
	fields : [ {
		name : 'a_asg_id'
	}, {
		name : 'a_asg_prj'
	}, {
		name : 'a_asg_pozice'
	}, {
		name : 'a_asg_pracpom'
	}, {
		name : 'a_asg_uvazek'
	}, {
		name : 'a_asg_dalsiuvaz'
	}, {
		name : 'a_asg_dalsicin'
	}, {
		name : 'r_prj_name'
	}, {
		name : 'p_pst_code'
	}, {
		name : 'p_pst_name'
	}, {
		name : 'a_asg_state',
		type : 'boolean'
	} ],
	listeners : {
		load : {
			fn : function(store, records, options) {
				store.sort('r_prj_name', 'ASC');
			}
		}
	}
});

var workersEditGrid_row = Ext.data.Record.create([ {
	name : 'a_asg_id'
}, {
	name : 'a_asg_prj'
}, {
	name : 'a_asg_pozice'
}, {
	name : 'a_asg_pracpom'
}, {
	name : 'a_asg_uvazek'
}, {
	name : 'a_asg_dalsiuvaz'
}, {
	name : 'a_asg_dalsicin'
}, {
	name : 'r_prj_name'
}, {
	name : 'p_pst_code'
}, {
	name : 'p_pst_name'
}, {
	name : 'a_asg_state'
} ]);

var workersEditGridText_editor = new fm.TextField({
	allowBlank : false
});

var workersEditGrid = new Ext.grid.EditorGridPanel({
	title : 'Pozice na projektech',
	store : workersEditGrid_store,
	autoHeight : true,
	height : 'auto',
	frame : true,
	stripeRows : true,
	autoExpandColumn : 'p_pst_name',
	tbar : [ {
		text : 'Nové přiřazení',
		iconCls : 'drop-add',
		handler : function() {
			workersNewAsgPrj_store.load();
			workersNewAsgPst_store.load();
			workersNewAsg_win.show();
			workersNewAsg_form.getForm().setValues({
				wrk_id : gvar_wrk_id,
				r_prj_id : 'Hledám projekty...',
				p_pst_id : 'Hledám pozice...'
			});
		}
	} ],
	columns : [ {
		header : 'asg_id',
		hidden : true,
		dataIndex : 'a_asg_id'
	}, {
		header : 'asg_prj',
		hidden : true,
		dataIndex : 'a_asg_prj'
	}, {
		header : 'asg_pozice',
		hidden : true,
		dataIndex : 'a_asg_pozice'
	}, {
		header : 'Projekt',
		width : 140,
		sortable : true,
		dataIndex : 'r_prj_name'
	}, {
		header : 'Kód pozice',
		width : 100,
		sortable : true,
		hidden : true,
		dataIndex : 'p_pst_code'
	}, {
		header : 'Pozice',
		id : 'p_pst_name',
		width : 200,
		sortable : true,
		dataIndex : 'p_pst_name'
	}, {
		header : 'Pracovní poměr',
		width : 120,
		sortable : true,
		dataIndex : 'a_asg_pracpom',
		editor : workersEditGridText_editor
	}, {
		header : 'Měsíční úvazek',
		width : 110,
		sortable : true,
		dataIndex : 'a_asg_uvazek',
		editor : workersEditGridText_editor
	}, {
		header : 'Další úvazek',
		width : 100,
		sortable : true,
		dataIndex : 'a_asg_dalsiuvaz',
		editor : workersEditGridText_editor
	}, {
		header : 'Další činnosti',
		width : 100,
		sortable : true,
		dataIndex : 'a_asg_dalsicin',
		editor : workersEditGridText_editor
	}, {
		xtype : 'checkcolumn',
		header : 'Aktivní',
		dataIndex : 'a_asg_state',
		width : 50,
		listeners : {
			click : {
				fn : function(cm, rec) {
					workersEditGrid_statusbar.showBusy();
					Ext.Ajax.request({
						url : 'index.php?save=layout.rightpanel.workers.assignment',
						success : function(result, request) {
							var answer = Ext.util.JSON.decode(result.responseText);
							if (answer.success) {
								rec.commit();
								workersEditGrid_statusbar.setStatus({
									text : 'Uloženo',
									iconCls : 'x-status-valid',
									clear : true
								});
							} else {
								workersEditGrid_statusbar.setStatus({
									text : answer.error,
									iconCls : 'x-status-error',
									clear : true
								});
							}
						},
						failure : function(result, request) {
							workersEditGrid_statusbar.setStatus({
								text : result.responseText,
								iconCls : 'x-status-error',
								clear : true
							});
						},
						params : {
							'field' : cm.dataIndex.replace('a_asg_', 'asg_'),
							'value' : rec.get('a_asg_state'),
							'asg_id' : rec.get('a_asg_id')
						}
					});
				}
			}
		}
	}, {
		xtype : 'actioncolumn',
		width : 30,
		items : [ {
			icon : 'style/images/default/grid/bin_closed.png',
			tooltip : 'Smazat řádek',
			handler : function(grid, rowIndex, colIndex) {
				var rec = workersEditGrid_store.getAt(rowIndex);
				Ext.Msg.confirm('Haló!', 'Opravdu chcete smazat tento řádek?', function(btn, text) {
					if (btn == 'yes') {
						workersEditGrid_statusbar.showBusy();
						Ext.Ajax.request({
							url : 'index.php?delete=layout.rightpanel.workers.assignment',
							success : function(result) {
								var answer = Ext.util.JSON.decode(result.responseText);
								if (answer.success) {
									workersEditGrid_store.removeAt(rowIndex);
									workersEditGrid_statusbar.setStatus({
										text : 'Odstraněno',
										iconCls : 'x-status-valid',
										clear : true
									});
								} else {
									workersEditGrid_statusbar.setStatus({
										text : answer.error,
										iconCls : 'x-status-error',
										clear : true
									});
								}
							},
							failure : function(result, request) {
								workersEditGrid_statusbar.setStatus({
									text : result.responseText,
									iconCls : 'x-status-error',
									clear : true
								});
							},
							params : {
								'asg_id' : rec.get('a_asg_id')
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
					workersEditGrid_statusbar.showBusy();
					Ext.Ajax.request({
						url : 'index.php?save=layout.rightpanel.workers.assignment',
						success : function(result, request) {
							var answer = Ext.util.JSON.decode(result.responseText);
							if (answer.success) {
								rec.commit();
								workersEditGrid_statusbar.setStatus({
									text : 'Uloženo',
									iconCls : 'x-status-valid',
									clear : true
								});
							} else {
								workersEditGrid_statusbar.setStatus({
									text : answer.error,
									iconCls : 'x-status-error',
									clear : true
								});
							}
						},
						failure : function(result, request) {
							workersEditGrid_statusbar.setStatus({
								text : result.responseText,
								iconCls : 'x-status-error',
								clear : true
							});
						},
						params : {
							'field' : data.field.replace('a_asg_', 'asg_'),
							'value' : data.value.replace(',', '.'),
							'asg_id' : rec.get('a_asg_id')
						}
					});
				}
			}
		}
	}
});

/* Main worker container */
var workersEditGrid_statusbar = new Ext.ux.StatusBar({
	defaultText : '',
	busyText : 'Pracuji...',
	statusAlign : 'left',
	items : [ '->', '&nbsp;' ]
});

var workersEditLayout = new Ext.Panel({
	id : 'workers-layout',
	title : 'worker.panel',
	region : 'center',
	margins : '35 5 5 0',
	layout : 'vbox',
	layoutConfig : {
		align : 'stretch',
		pack : 'start'
	},
	items : [ workersEditForm, workersEditGrid ],
	bbar : workersEditGrid_statusbar,
	tools : [ {
		type : 'close',
		tooltip : 'Zavřít',
		handler : function(event, toolEl, owner, tool) {
			owner.hide();
			workersEditProperty_store.removeAll();
			workersEditGrid_store.removeAll();
			Ext.getCmp('content-panel').layout.setActiveItem('start-panel');
		}
	} ],
	tbar : [ {
		text : 'Uložit',
		iconCls : 'disk',
		handler : function() {
			if (workersEditForm.getForm().isValid()) {
				workersEditGrid_statusbar.showBusy();
				workersEditForm.getForm().submit({
					success : function(form, answer) {
						workersList.getRootNode().reload();
						leftPanel_vykazy_timesheets.getRootNode().reload();
						workersEditGrid_statusbar.setStatus({
							text : 'Uloženo',
							iconCls : 'x-status-valid',
							clear : true
						});
					},
					failure : function(form, answer) {
						workersEditGrid_statusbar.setStatus({
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
			workersEditGrid_statusbar.showBusy();
			Ext.Ajax.request({
				url : 'index.php?delete=layout.rightpanel.workers.worker',
				success : function(result) {
					var answer = Ext.util.JSON.decode(result.responseText);
					if (answer.success) {
						workersList.getRootNode().reload();
						leftPanel_vykazy_timesheets.getRootNode().reload();
						workersEditGrid_statusbar.setStatus({
							text : 'Odstraněno',
							iconCls : 'x-status-valid',
							clear : true
						});
						Ext.getCmp('content-panel').layout.setActiveItem('start-panel');
					} else {
						workersEditGrid_statusbar.setStatus({
							text : answer.error,
							iconCls : 'x-status-error',
							clear : true
						});
					}
				},
				failure : function(result, request) {
					workersEditGrid_statusbar.setStatus({
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
	} ],
	listeners : {
		activate : {
			fn : function(panel) {
				workersEditForm.getForm().findField('wrk_state').focus(true, 100);
			}
		}
	}
});