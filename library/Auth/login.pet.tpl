<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css"
	href="library/Auth/login.style.css" />
</head>
<body>
	<div id="obalovy">
		<div id="prostredni">
			<div id="vnitrni">
				<form method="POST" action="index.php" name="login_user">
					<div id="firma">{title}</div>
					<div id="majitel">{header_title}</div>
					<div id="tablogin">
						<h1>Přihlašte se prosím</h1>
						<p class="line">
							<label class="label">Jméno</label> <input name="username"
								id="nick" type="text" size="20" class="input">
						</p>
						<p class="line">
							<label class="label">Heslo</label> <input name="password"
								type="password" size="20" class="input">
						</p>
						<p class="sendline">
							<span>{logerror}</span> <input type="hidden" name="auth"
								value="login">
							<button type="submit">
								<img src="style/images/key_go.png">&nbsp;Přihlásit
							</button>
						</p>
					</div>
				</form>
				<script type="text/javascript">document.getElementById("nick").focus();</script>
			</div>
		</div>
	</div>
</body>
</html>
