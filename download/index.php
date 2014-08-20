<?php include('../header.php'); ?>
<?php include('../feed/releases.php'); ?>

<div class="text-center">
	<h1>Download LMMS</h1>

	<p>Downloading and using LMMS is free! Just choose the operating system you want to run LMMS on:</p>

	<div class="row">
		<div class="btn-group" data-toggle="buttons">
			<label class="btn btn-default" onclick="showOS('#linux')">
				<input type="radio" name="options" id="linux-button"><span class="fa fa-linux fa-5x"></span><br>Linux
			</label>
			<label class="btn btn-default" onclick="showOS('#windows')">
				<input type="radio" name="options" id="windows-button"><span class="fa fa-windows fa-5x"></span><br>Windows
			</label>
			<label class="btn btn-default" onclick="showOS('#mac')">
				<input type="radio" name="options" id="mac-button"><span class="fa fa-apple fa-5x"></span><br>OS X
			</label>
		</div>
	</div>
	<!-- No longer needed because of fixed navbar
	<p class="visible-xs-block">
		<a href="#"><span class="fa fa-music"></span> Download Sample Packs</a> • <a href="/artwork.php"><span class="fa fa-picture-o"></span> Download Artwork</a>
	</p> -->

</div><hr>

<div id="linux-div" class="center hidden">
	<?php include('linux.php'); ?>
</div>
<div id="windows-div" class="center hidden">
	<h2>Install LMMS on Windows</h2>
	<p>Click one of the buttons below (either 32bit or 64bit) to download LMMS for Windows</p>
	<?php get_releases('stable', '.exe'); ?>
	<h3>Beta Versions</h3>
	<?php get_releases('prerelease', '.exe'); ?>
</div>
<div id="mac-div" class="center hidden">
	<h2>Install LMMS on Apple OS X</h2>
	<p>Click one of the buttons below to download LMMS for Apple</p>
	<?php get_releases('stable', '.dmg'); ?>
	<h3>Beta Versions</h3>
	<?php get_releases('prerelease', '.dmg'); ?>
</div>
<div id="prerelease"><small><span class="fa fa-exclamation-circle"></span> Denotes pre-release software, stability may suffer.</small></div>

<script>
function showOS(os) {
	location.hash = os;
	if (os.indexOf("linux") != -1) {
		if (os != "#linux") {
			$(os+"-button").tab("show");
		} else {
			$("#linux-debian-button").tab("show");
		}
		os = "#linux";
		$('#prerelease').hide();
	} else {
		$('#prerelease').show();
	}

	$(os+"-button").tab("show");
	
	hide('#windows-div');
	hide('#linux-div');
	hide('#mac-div');
	show(os+'-div');
	
	$(os+"-button").parent().addClass("active") ;
}

// Add Linux-specific tab functionality
function showLinux() {
	// Make the current hash visible
	if (location.hash != '#linux') {
		$('#linux-tabs a[href="' + location.hash + '"]').tab('show');
	} else {
		$('#linux-tabs a[href="#linux-debian"]').tab('show');
	}
	
	$('#linux-tabs a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	})
}

function hide(obj) {
	$(obj).hide();
	$(obj).removeClass('show');
}

function show(obj) {
	$(obj).show();
	$(obj).removeClass('hidden');
	$(obj).removeClass('hide');
}

function autoSelect() {
	if (navigator.appVersion.indexOf("Mac")!=-1)
		showOS("#mac");
	else if (navigator.appVersion.indexOf("X11")!=-1)
		showOS("#linux");
	else if (navigator.appVersion.indexOf("Linux")!=-1)
		showOS("#linux");
	else showOS("#windows");
}

$(function() {
	if (location.hash) {
		try {
			showOS(location.hash);
		} catch (err) {
			autoSelect();
		}
	} else {
		autoSelect();
	}
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	location.hash = e.target.hash;
	$(e.target).parent().children().removeClass("active");
	e.target.classList.add("active");
})

</script>

<?php include('../footer.php'); ?>
