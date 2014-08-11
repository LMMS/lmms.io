<?php include('../header.php'); ?>
<?php include('../feed/releases.php'); ?>

<div class="text-center">
	<h1>Download LMMS</h1>

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

</div><hr>

<div id="linux-div" style="display:none">
	<?php include('linux.php'); ?>
</div>
<div id="windows-div" style="display:block">
	<h2>Install LMMS on Windows</h2>
	<p>Click one of the buttons below (either 32bit or 64bit) to download LMMS for Windows</p>
	<p><?php get_releases(1, 'horiz', '.exe'); ?></p>
	<p>Beta Versions</p>
	<?php get_releases(1, 'horiz', '.exe', 'tresf'); ?>
</div>
<div id="mac-div" style="display:none">
	<h2>Install LMMS on Apple</h2>
	<p>Click one of the buttons below to download LMMS for Apple</p>
	<?php get_releases(1, 'horiz', '.dmg', 'tresf'); ?>
</div>
<hr><small><span class="fa fa-exclamation-circle"></span> Denotes prerelease software, stability may suffer</small>

<script>
function show(os) {
	location.hash = os;
	if (os.indexOf("linux") != -1) {
		if (os != "#linux") {
			$(os+"-button").tab("show");
		}
		os = "#linux";
	}

	$("#windows-div").hide();
	$("#linux-div").hide();
	$("#mac-div").hide();
	$(os+"-div").show();
	$(os+"-button").parent().addClass("active") ;
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

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	location.hash = e.target.hash;
	$(e.target).parent().children().removeClass("active");
	e.target.classList.add("active");
})

</script>

<?php include('../footer.php'); ?>
