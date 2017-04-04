<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>{title}</title> [insert_css]
<link rel="stylesheet" type="text/css" href="{css_file}" />
[/insert_css]
<script type="text/javascript">
[insert_gvar]var {name} = '{value}';
[/insert_gvar]
</script>
[insert_js]
<script type="text/javascript" src="{js_file}"></script>
[/insert_js] {timesheet_js}
</head>
<body>
	<div id="header">
		<h1>{header_title}</h1>
		<div class="user_info">
			<a href="index.php?auth=logout"></a>
			<p>
				{worker_name}<br /> <span>({worker_role})</span>
			</p>
		</div>
	</div>
</body>
</html>
