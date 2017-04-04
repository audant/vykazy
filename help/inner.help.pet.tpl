<a name="home"></a>
<div style="position: absolute; top: 5px; right: 10px; font-size: 60%; color: #636363; font-family: serif;">({svn_version} {svn_date})</div>
<h1 style="font-size: 200%; color: #15428B;">{title}</h1>
<h2 style="font-size: 160%; color: #15428B;">{header_title}</h2>
<ul style="margin: 10px 20px;">
	<li style="margin-top: 4px"><a href="#layout">Okno aplikace</a>
	</li>
	<li>Výkazy:</li>
	<ul style="margin: 0 20px;">
		<li><a href="#new_timesheet">Vytvoření nového výkazu</a></li>
		<li style="margin-top: 4px"><a href="#edit_timesheet">Vyplnění výkazu</a></li>
		<li style="margin-top: 4px"><a href="#print_timesheet">Vytisknutí výkazu</a></li>
	</ul>
	<li><a href="#documents">Dokumenty</a></li>
</ul>
<h3 style="font-size: 120%; color: #15428B; padding: 10px 0; border-top: 1px solid #dddddd;">
	<a name="layout"></a>Okno aplikace
</h3>
<div style="margin: 10px 20px;">
	<p style="margin: 10px 20px;">
		<img src="help/img/layout.png">
	</p>
	<p style="margin-top: 4px;">
		<b>Navigace</b> - levá strana plochy aplikace. Je zde hlavní složka uživatele <img src="style/images/default/tree/folder_user.png">, která obsahuje projekty (otevřený - <img src="style/images/default/tree/brick_edit.png">, uzavřený - <img src="style/images/default/tree/brick_close.png">).
	</p>
	<p style="margin-top: 4px;">
		Složka s projektem obsahuje vlastní <i>Výkazy práce</i>:
	</p>
	<ul style="margin: 5px 20px;">
		<li><img src="style/images/default/tree/report_close.png"> <i>Uzamčený výkaz</i> - již nelze editovat, je možné prohlížet a tisknout</li>
		<li><img src="style/images/default/tree/report_edit.png"> <i>Otevřený výkaz</i> - lze editovat</li>
		<li><img src="style/images/default/tree/report_add.png"> <i>Nový výkaz</i> - u otevřených projektů je na konci ikonka pro založení nového výkazu pro tento projekt.</li>
	</ul>
	<p style="margin-top: 4px;">V názvu výkazu je rok/měsíc zkratka pozice (úvazek).</p>
	<p style="margin-top: 4px;">Strom navigace se otevírá / zavírá kliknutím na ikonku trojúhelníčku před symbolem složky / projektu, nebo dvojitým poklikáním přímo na řádku.</p>
	<p style="margin-top: 4px;">
		<b><i>Jednoduchým kliknutím na řádek s výkazem se v pravé části (Pracovní plocha) otevře zvolený pracovní výkaz.</i> </b>
	</p>
	<p style="font-size: 90%; margin-top: 4px;">
		<a href="#home">Zpět nahoru...</a>
	</p>
</div>

<h3 style="font-size: 120%; color: #15428B; padding: 10px 0; border-top: 1px solid #dddddd;">
	<a name="new_timesheet"></a>Vytvoření nového výkazu
</h3>
<div style="margin: 10px 20px;">
	<p style="margin-top: 4px;">
		V <a href="#layout">okně navigace</a> najděte projekt, na kterém chcete vytvořit Pracovní výkaz a klikněte na ikonku <i>Nový&nbsp;výkaz&nbsp;<img src="style/images/default/tree/report_add.png"> </i> (bude na konci seznamu výkazů u zvoleného projektu).
	</p>
	<p style="margin-top: 4px;">Aplikace otevře dialogové okno, ve kterém bude chtít upřesnit rok, měsíc a konkrétní pracovní pozici pro kterou chcete založit výkaz:</p>
	<p style="margin: 10px 20px;">
		<img src="help/img/new_timesheet.png">
	</p>
	<p style="margin-top: 4px;">Vyplňte požadované údaje a klikněte na tlačítko "OK". Okno se zavře a v pravé části (Pracovní plocha) se otevře definovaný výkaz, který můžete začít vyplňovat...</p>
	<p style="font-size: 90%; margin-top: 4px;">
		<a href="#home">Zpět nahoru...</a>
	</p>
</div>

<h3 style="font-size: 120%; color: #15428B; padding: 10px 0; border-top: 1px solid #dddddd;">
	<a name="edit_timesheet"></a>Vyplnění výkazu
</h3>
<div style="margin: 10px 20px;">
	<p style="margin-top: 4px;">Po odsouhlasení dialogu při vytváření nového výkazu, nebo jednoduchým kliknutím na již existující pracovní výkaz v levém okně Navigace, se na Pracovní ploše otevře vlastní výkaz k editaci / prohlížení / tisku apod.</p>
	<p style="margin: 10px 20px;">
		<img src="help/img/empty_timesheet.png">
	</p>
	<p style="margin-top: 4px;">
		V horní části jsou tlačítka pro <img src="style/images/default/menu/printer.png"> tisk výkazu, <img src="style/images/default/tree/report_close.png"> uzamčení již hotového výkazu (pozor, uzamčený výkaz již nelze editovat, kontaktujte admnistrátora pro případné opravy), nebo zavření aktuálního okna s výkazem <img src="style/images/default/menu/door_in.png"> - pouze se zavře okno
		s výkazem a aktualizují data v navigačním panelu (výkaz můžete i nadále otevřít a pokračovat v editaci).
	</p>
	<p style="margin-top: 4px;">Vlastní okno pak vyplňuje tabulka s výkazem. Pro každý den v měsíci je jeden řádek, vyplňujete pouze ty řádky, ve kterých jste prováděli nějakou činnost kterou je třeba vykázat.</p>
	<p style="margin-top: 4px;">
		Tabulka obsahuje sloupec s datumem (den v měsíci a zkratka dne), vykázané hodiny (časový údaj ve formátu "hodiny:minuty"), popis činnosti a v posledním sloupečku je symbol <img src="style/images/default/grid/bin_closed.png"> koše, kterým se smaže celý řádek.
	</p>
	<p style="margin-top: 4px;">Editaci zvoleného údaje (Hodiny nebo Popis) zahájíte poklikáním na buňku tabulky a napsáním hodnoty, kterou chcete vložit a stiskněte klávesu enter.</p>
	<p style="margin: 10px 20px;">
		<img src="help/img/edit_timesheet.png">
	</p>
	<p style="margin-top: 4px;">Pokud jednoduše kliknete na jakoukoliv buňku tabulky, dojde k jejímu zvýraznění modrou barvou, a můžete se "pohybovat" po tabulce kurzorovými klávesami (šipkamy). K editaci buňky, na které se právě nacházíte, zahájíte stiskem klávesy enter. Po ukončení editace stiskněte enter, tím se uloží data a buňka zůstává nadále aktivní. Nebo stisknutím klávesy "tabelátor" se
		uloží vámi zadaná data a přesunete se k editaci dalšího dostupného pole v tabulce.</p>
	<p style="margin: 10px 20px;">
		<img src="help/img/filled_timesheet.png">
	</p>
	<p style="font-size: 90%; margin-top: 4px;">
		<a href="#home">Zpět nahoru...</a>
	</p>
</div>

<h3 style="font-size: 120%; color: #15428B; padding: 10px 0; border-top: 1px solid #dddddd;">
	<a name="delrow_timesheet"></a>Smazání řádku výkazu
</h3>
<div style="margin: 10px 20px;">
	<p style="margin-top: 4px;">
		V <a href="#layout">okně navigace</a> najděte a otevřete výkaz, který chcete upravovat.
	</p>
	<p style="margin-top: 4px;">
		Na konci každého řádku je symbol koše <img src="style/images/default/grid/bin_closed.png"> kterým smažete obsah celého řádku. Aplikace se pro jistotu ještě zeptá zda řádek skutečně chcete smazat.
	</p>
	<p style="font-size: 90%; margin-top: 4px;">
		<a href="#home">Zpět nahoru...</a>
	</p>
</div>

<h3 style="font-size: 120%; color: #15428B; padding: 10px 0; border-top: 1px solid #dddddd;">
	<a name="print_timesheet"></a>Vytisknutí výkazu
</h3>
<div style="margin: 10px 20px;">
	<p style="margin-top: 4px;">
		V <a href="#layout">okně navigace</a> najděte a otevřete výkaz, který chcete tisknout. V horní části klikněte na tlačířko <img src="style/images/default/menu/printer.png"> Vytisknout. Aplikace vytvoří PDF soubor se sestavou pracovního výkazu. Ten můžete tisknout, uložit kopii, odeslat mailem apod. stejně jako každý jiný PDF dokument.
	</p>
	<p style="margin: 10px 20px;">
		<img src="help/img/pdf_timesheet.png">
	</p>
	<p style="font-size: 90%; margin-top: 4px;">
		<a href="#home">Zpět nahoru...</a>
	</p>
</div>

<h3 style="font-size: 120%; color: #15428B; padding: 10px 0; border-top: 1px solid #dddddd;">
	<a name="documents"></a>Dokumenty
</h3>
<div style="margin: 10px 20px;">
	<p style="margin-top: 4px;">
		V okně navigace klikněte na volbu <img src="style/images/default/menu/page_white_stack.png"> Dokumenty. <br>
		<b>Pro práci s dokumenty doporučuji používat spíše Firefox nebo Operu než Explorer.</b>
	</p>
	<p style="margin: 10px 20px;">
		<img src="help/img/docs_overview.png">
	</p>
	<p style="margin-top: 4px;">
		V okně navigace jsou pak dokumenty roztříděny podle kategorií. Kliknutím na dokument jej otevřete do pracovní plochy. Pokud máte s dokumenty v ploše potíže, můžete jej kliknutím pravého tlačítka myši a vybráním "Otevřít v novém okně" otevřít do samostatného okna prohlížeče.
	</p>
	<p style="font-size: 90%; margin-top: 4px;">
		<a href="#home">Zpět nahoru...</a>
	</p>
</div>
