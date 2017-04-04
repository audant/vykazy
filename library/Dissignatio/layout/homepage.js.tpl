<script type="text/javascript">

	var start = new Ext.Panel({
		id : 'start-panel',
		layout : 'fit',
		bodyStyle : 'padding: 20px;',
		autoScroll : true,
		autoLoad : {
			url : 'index.php?help=inner'
		}
	});

	var contentPanel = new Ext.Panel({
		id : 'content-panel',
		region : 'center',
		margins : '35 5 5 0',
		layout : 'card',
		activeItem : 'start-panel',
		items : [ {contentPanelModules} ]
	});

	var leftPanel = new Ext.Panel({
		id : 'left-panel',
		region : 'west',
		title : 'Navigace',
		split : true,
		width : 340,
		collapsible : true,
		margins : '35 0 5 5',
		cmargins : '35 5 5 5',
		layout : 'accordion',
		layoutConfig : {
			animate : true
		},
		items : [ {leftPanelModules} ]
	});

	Ext.onReady(function() {
		Ext.form.Field.prototype.msgTarget = 'side';
		Ext.Ajax.timeout = 90000;
		Ext.QuickTips.init();
		var mainViewport = new Ext.Viewport( {
			layout : 'border',
			items : [ leftPanel, contentPanel ],
			renderTo : Ext.getBody()
		});
	});

</script>
