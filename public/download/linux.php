<div class="text-center">
	<h2>Install LMMS on Linux</h2>

	<p>LMMS is included in most major Linux distribution's package repositories.<br>
	Please <a href="/community/">contact us</a> if your distribution is not listed here.</p>

	<div class="nav-center-container">
		<ul id="linux-tabs" class="nav nav-tabs nav-center" role="tablist">
			<li><a href="#linux-debian" role="tab" data-toggle="tab" id="linux-debian-button" class="active">
				<span class="visible-lg-inline"><span class="fl fl-16 fl-debian fl-tab"></span>&nbsp;Debian&nbsp;/</span>
				<span class="fl fl-16 fl-ubuntu fl-tab"></span>&nbsp;Ubuntu
				<span class="visible-md-inline visible-lg-inline">&nbsp;/<span class="fl fl-16 fl-linuxmint-inverse fl-tab"></span>&nbsp;Mint</span>
			</a></li>

			<li><a href="#linux-suse" role="tab" data-toggle="tab" id="linux-suse-button">
				<span class="fl fl-16 fl-opensuse fl-tab"></span>&nbsp;OpenSUSE
			</a></li>

			<li><a href="#linux-mageia" role="tab" data-toggle="tab" id="linux-mageia-button">
				<span class="fl fl-16 fl-mageia fl-tab"></span>&nbsp;Mageia
				<span class="visible-lg-inline">&nbsp;/<span class="fl fl-16 fl-mandriva fl-tab"></span>&nbsp;Mandriva</span>
			</a></li>

			<li><a href="#linux-fedora" role="tab" data-toggle="tab" id="linux-fedora-button">
				<span class="fl fl-16 fl-fedora fl-tab"></span>&nbsp;Fedora
				<span class="visible-md-inline visible-lg-inline">&nbsp;/<span class="fl fl-16 fl-centos fl-tab"></span>&nbsp;CentOS</span>
				<span class="visible-lg-inline">&nbsp;/<span class="fl fl-16 fl-redhat fl-tab"></span>&nbsp;Red Hat</span>
			</a></li>

			<li><a href="#linux-arch" role="tab" data-toggle="tab" id="linux-arch-button">
				<span class="fl fl-16 fl-archlinux fl-tab"></span>&nbsp;Arch Linux
			</a></li>

			<li><a href="#linux-slackware" role="tab" data-toggle="tab" id="linux-slackware-button">
				<span class="fl fl-16 fl-slackware fl-tab"></span>&nbsp;Slackware
			</a></li>
		</ul>
	</div>
</div>

<div class="tab-content">
	<div id="linux-debian" class="tab-pane active">
		<h3>Ubuntu, Linux Mint, Debian (deb)</h3>
		<p>For installing LMMS on Debian based distributions such as Debian itself, Ubuntu or Linux Mint, just click the button below.<br></p>
		<!-- <p><a class="btn btn-primary" target="new" href="apt://lmms"><span class="fa fa-download"></span> Install LMMS</a></p> -->
		<p>
		<a class="btn btn-md btn-dl btn-dl-md btn-primary" href="apt://lmms" title="apt://lmms"><span id="button-title">LMMS</span><br><span class="fa fa-download download-icon"></span><big>Install Now</big><br><small>(From Package Manager)</small></a>
		</p>
		<p>If this doesn't work for you, run this command in a terminal.</p>
		<div class="code-block"><pre>$ sudo apt-get install lmms</pre></div>
		<p>If the traditional repositories lag behind on versions, try the <a href="http://kxstudio.sourceforge.net/Repositories#Ubuntu">KXStudio repository</a>.</p>
	</div>

	<div id="linux-mageia" class="tab-pane">
		<h3>Mandriva, Mageia (rpm)</h3>
		<p>Run the following command as root in a terminal:</p>
		<div class="code-block"><pre>$ urpmi lmms</pre></div>
	</div>

	<div id="linux-fedora" class="tab-pane">
		<h3>Fedora, RedHat, CentOS (rpm)</h3>
		<p>Run the following command as root in a terminal:</p>
		<div class="code-block"><pre>$ yum install lmms</pre></div>
	</div>

	<div id="linux-arch" class="tab-pane">
		<h3>Arch Linux</h3>
		<p>Run the following command in a terminal:</p>
		<div class="code-block"><pre>$ sudo pacman -S lmms</pre></div>
	</div>

	<div id="linux-slackware" class="tab-pane">
		<h3>Slackware Linux</h3>
		<p></p>
		<div class="code-block"><pre># sbopkg -i fltk -i lmms</pre></div>
	</div>

	<div id="linux-suse" class="tab-pane">
		<h3>openSUSE</h3>
		<p>Run the following command in a terminal:</p>
		<div class="code-block"><pre>$ sudo zypper install lmms</pre></div>
	</div>
</div>
<hr>
<div id="linux-source">
	<h3>Build LMMS from source</h3>
	<p>If your Linux distribution does not provide a lmms package (or only an out-dated one), you can still build LMMS from source. Visit the <a href="https://github.com/LMMS/lmms/wiki/Compiling-lmms">LMMS development wiki on GitHub</a> for instructions on how to compile LMMS for Linux.</p>
</div>
