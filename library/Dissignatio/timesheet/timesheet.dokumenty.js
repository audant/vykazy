/**
 * Výkazy práce - documents
 * 
 * @author Michal Basl (audant@bobb.cz)
 * @copyright 2011 Základní škola a Odborná škola Zbůch
 */

/*
 * Left Content
 */
var docsList_treeloader = new Ext.tree.TreeLoader({
	dataUrl : 'index.php?json=layout.leftpanel.documents.list',
	preloadChildren : true,
	clearOnLoad : false,
	listeners : {
		beforeload : {
			fn : function() {
				docsList.body.mask('Nahrávám...');
			}
		},
		load : {
			fn : function(store, records, option) {
				docsList.body.unmask();
			}
		}
	}

});

var docsListContextMenu = new Ext.menu.Menu({
	items : [ {
		text : 'Otevřít v novém okně',
		iconCls : 'page_white_edit',
		handler : function(item, eventObject) {
			window.open(Ext.util.base64.decode(gvar_dcs_link));
		}
	} ]
});

var docsList = new Ext.ux.tree.TreeGrid({
	id : 'left-panel-docs-list',
	loader : docsList_treeloader,
	enableSort : true,
	tbar : [ {
		text : 'Obnovit',
		iconCls : 'refresh',
		handler : function() {
			docsList.getRootNode().reload();
		}
	} ],
	columns : [ {
		header : 'Dokument',
		dataIndex : 'docs',
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
	}, {
		header : 'Kind',
		dataIndex : 'kind',
		align : 'right',
		width : 50,
		hidden : true
	} ],
	listeners : {
		click : {
			fn : function(node) {
				if (node.attributes.click == 'edit' || node.attributes.click == 'view') {
					documentShow(node.attributes.id, node.attributes.docs, node.attributes.link, node.attributes.kind);
				}
			}
		},
		contextmenu : {
			fn : function(node) {
				if (node.attributes.click == 'edit') {
					node.select();
					gvar_dcs_link = node.attributes.link;
					docsListContextMenu.show(node.ui.getAnchor());
				}
			}
		}
	}

});

function documentShow(id, name, link, kind) {
	var tab = new Ext.Panel({
		id : 'doc-' + id,
		title : name,
		closable : true,
		items : [ new Ext.Panel({
			id : 'right-panel-docs-tab-' + id,
			layout : 'fit',
			html : null
		}) ]
	});
	Ext.Ajax.request({
		url : 'index.php?document=layout.rightpanel.documents.docs.dcs_id:' + id + '.dcs_link:' + link + '.dcs_kind:' + kind,
		success : function(r) {
			var docs = Ext.decode(r.responseText).htmlContent;
			tab.body.update(docs);
			tab.doLayout();
		}
	});
	documentEditTabs.add(tab).show();
	Ext.getCmp('content-panel').layout.setActiveItem('right-panel-docs');
}

var leftPanel_dokumenty = {
	id : 'left-panel-docs',
	title : 'Dokumenty',
	autoScroll : true,
	border : false,
	layout : 'fit',
	iconCls : 'page_white_stack',
	items : docsList
};

/*
 * Right Content
 */
var documentEditTabs = new Ext.TabPanel({
	id : 'right-panel-tabs',
	title : 'Dokumenty',
	header : true,
	activeTab : 0,
	border : false,
	enableTabScroll : true
});

var documentEditLayout = new Ext.Panel({
	id : 'right-panel-docs',
	title : 'Dokumenty',
	layout : 'fit',
	items : [ documentEditTabs ],
	tools : [ {
		type : 'close',
		tooltip : 'Zavřít',
		handler : function(event, toolEl, owner, tool) {
			owner.hide();
			documentEditTabs.removeAll(true);
			Ext.getCmp('content-panel').layout.setActiveItem('start-panel');
		}
	} ]
});

function afterIframeLoad(id) {
	document.getElementById('documents-loader-' + id).style.display = 'none';
}
