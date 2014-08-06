<?php include('header.php'); ?>
<?php include('feed/releases.php'); ?>

<div class="page-header"> <h1>Download LMMS</h1> </div>

<p>Downloading and using LMMS is free! Just choose the operating system you want to run LMMS on:</p>

<p>
<div class="btn-group" data-toggle="buttons">
	<label class="btn btn-default" onclick="show('#linux')">
		<input type="radio" name="options" id="linux-button"><span class="fa fa-linux"></span> Linux
	</label>
	<label class="btn btn-default" onclick="show('#windows')">
		<input type="radio" name="options" id="windows-button"><span class="fa fa-windows"></span> Windows
	</label>
	<label class="btn btn-default" onclick="show('#mac')">
		<input type="radio" name="options" id="mac-button"><span class="fa fa-apple"></span> OS X
	</label>
</div>
</p>
<p class="visible-xs-block">
<a href="#"><span class="fa fa-music"></span> Download Sample Packs</a> • <a href="/artwork.php"><span class="fa fa-picture-o"></span> Download Artwork</a>
</p>

<div id="linux-div" style="display:none">
	<h2>Install LMMS on Linux</h2>
	<p>LMMS is included in most major Linux distribution's package repositories. If your distribution is not listed here and you feel the need for it to be included, don't hesitate to contact us about it.</p>
	<h3>Debian, Ubuntu, Linux Mint (deb)</h3>
	<p>For installing LMMS on Debian based distributions such as Debian itself, Ubuntu or Linux Mint, just click the button below.<br> If this doesn't work for you, run <code>sudo apt-get install lmms</code> in a terminal.</p>
	<a class="btn btn-primary" target="new" href="apt://lmms"><span class="fa fa-download"></span> Install LMMS</a>
	<p>If the traditional repositories lag behind on versions, try the <a href="http://kxstudio.sourceforge.net/Repositories#Ubuntu">KXStudio repository</a>.</p>

	<h3>Mandriva, Mageia (rpm)</h3>
	<p>Run <code>urpmi lmms</code> as root.</p>

	<h3>Fedora, CentOS (rpm)</h3>
	<p>Run <code>yum install lmms</code> as root.</p>

	<h3>openSUSE</h3>
	<p>Run <code>zypper install lmms</code> as root.</p>

	<h3>Arch Linux</h3>
	<p>Run <code>sudo pacman -S lmms</code> in a terminal.</p>

	<h2>Build LMMS from source</h2>
	<p>If your Linux distribution does not provide a lmms package (or only an out-dated one), you can still build LMMS from source. Visit the <a href="https://github.com/LMMS/lmms/wiki/Compiling-lmms">LMMS development wiki on GitHub</a> for instructions on how to compile LMMS for Linux.</p>
</div>
<div id="windows-div" style="display:block">
	<h2>Install LMMS on Windows</h3>
	<p>Click one of the buttons below (either 32bit or 64bit) to download LMMS for Windows</p>
	<?php get_releases(1, 'horiz', '.exe'); ?>
	<hr><p>Beta Versions</p>
	<?php get_releases(1, 'horiz', '.exe', 'tresf'); ?>
</div>
<div id="mac-div" style="display:none">
	<h2>Install LMMS on Apple</h3>
	<p>Click one of the buttons below to download LMMS for Apple</p>
	<?php get_releases(1, 'horiz', '.dmg', 'tresf'); ?>
</div>
<hr><small><span class="fa fa-exclamation-circle"></span> Denotes prerelease software, stability may suffer</small>

<script>
function show(os) {
	$("#windows-div").hide();
	$("#linux-div").hide();
	$("#mac-div").hide();
	$(os+"-div").show();
	$(os+"-button").parent().addClass("active") ;
	location.hash = os;
}

function autoSelect() {
	if (navigator.appVersion.indexOf("Mac")!=-1)
		show("#mac");
	else if (navigator.appVersion.indexOf("X11")!=-1)
		show("#linux");
	else if (navigator.appVersion.indexOf("Linux")!=-1)
		show("#linux");
	else show("#windows");
}

$(function() {
	if (location.hash) {
		try { 
			show(location.hash);
		} catch (err) {
			autoSelect();
		}
	} else {
		autoSelect();
	}
});



</script>

<?php include('footer.php'); ?>
