<?php include('header.php'); ?>

<div class="page-header"><h1>LMMS Community</h1></div>
<div id="toolbar" class="panel panel-default">
	<div class="panel-heading">
		<div class="btn-group" data-toggle="buttons">
			
			<label title="Visit page" class="btn btn-success" target="_blank" data-href="/forum/" onclick="show(this)">
				<span class="fa fa-comments"></span> Forums
			</label>
			<label title="Preview content" class="btn btn-success dropdown-toggle" onclick="show('#forums')"><span class="fa fa-bars"></span>
				<input type="radio" name="options">
			</label>
			
			<label title="Visit page" class="btn btn-primary" onclick="show(this)" target="_blank" data-href="http://facebook.com/makefreemusic">
				<span class="fa fa-facebook"></span> Facebook
			</label>
			<label title="Preview content" class="btn btn-primary dropdown-toggle" onclick="show('#facebook')"><span class="fa fa-bars"></span>
				<input type="radio" name="options">
			</label>
			
			<label title="Visit page" class="btn btn-warning" onclick="show(this)" target="_blank" data-href="http://soundcloud.com/groups/linux-multimedia-studio">
				<span class="fa fa-soundcloud"></span> SoundCloud
			</label>
			<label title="Preview content" class="btn btn-warning dropdown-toggle disabled" onclick="show('#soundcloud')"><span class="fa fa-bars"></span>
				<input type="radio" name="options">
			</label>
			
			<label title="Visit page" class="btn btn-danger" onclick="show(this)" target="_blank" data-href="https://plus.google.com/u/0/113001340835122723950/posts">
				<span class="fa fa-google-plus"></span> Google+
			</label>
			<label title="Preview content" class="btn btn-danger dropdown-toggle disabled" onclick="show('#google+')"><span class="fa fa-bars"></span>
				<input type="radio" name="options">
			</label>
			
			<label title="Visit page" class="btn btn-default disabled" onclick="show(this)" style="color: red;" target="new" data-href="#"><span class="fa fa-youtube"></span>
				<span style="color:black;"> YouTube</span>
			</label>
			<label title="Preview content" class="btn btn-default dropdown-toggle disabled" onclick="show('#youtube')"><span class="fa fa-bars"></span>
				<input type="radio" name="options">
			</label>
			
			<label title="Visit page" class="btn" onclick="show(this)" style="color: #fff; background-color: #000;" target="new" data-href="http://github.com/LMMS/lmms">
				<span class="fa fa-github"></span> GitHub
			</label>
			<label title="Preview content" class="btn dropdown-toggle" onclick="show('#github')" style="color: #fff; background-color: #000;"><span class="fa fa-bars"></span>
				<input type="radio" name="options">
			</label>
		</div>
	</div>
	<div id="alert-div" class="alert alert-warning" role="alert" style="display:none;">
		<a class="close" onclick="$('#alert-div').hide()">×</a>
		<p><img class="visible-lg logo-sm" style="float: left;" src="/img/logo_sm.png"><h4><span id="alert-title">&nbsp;</span></h4><span id="alert-text"></span></p>
	</div>
    <div id="forums-div" class="panel-body" style="display:none;">
<?php include('feed/forums.php'); ?>
	</div>
	<div id="github-div" class="panel-body" style="display:none;">
<!-- Loads download links automatically from dl.php -->
<?php include('feed/issues.php'); ?>
	</div>
	<div id="facebook-div" class="panel-body" style="display:none;">
	<h3>Facebook Feed Placeholder</h3>
	</div>
</div>

<script>
	function show(obj) {
		if ($(obj).attr('data-href')) {
			$(obj).button('toggle');
			return window.open($(obj).attr('data-href'), '_blank');
		}
		$("div[id$='-div']").hide();
		$('#alert-div').show();
		var title = obj.substring(1, obj.length); // remove hash
		
		$('#alert-title').text('LMMS ' + title.toUpperCase() + ' ');
		$('#alert-text').text('Below is a sample of recent activity from our ' + title + ' page.  Please click on an item to be redirected to that page.');

		$(obj + '-div').show();
		// TODO: $(obj).addClass("active") ;
		location.hash = obj;
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
	
	function autoSelect() {
		show('#forums');
	}
});
</script>

<!--
<div class="page-header">
	<h1>Development <a href="https://travis-ci.org/LMMS/lmms"><img src="https://travis-ci.org/LMMS/lmms.svg"></a></h1>
</div>

<h2>Build LMMS</h2>
<p>Building LMMS from source requires Linux or BSD/Unix (including Apple) operating system.  Visit the <a class="btn btn-default btn-xs" href="http://github.com/LMMS/lmms/wiki">LMMS GitHub wiki</a> for compile instructions.</p><br>

<h2>Bug Tracker</h2>
<p>To view open issues (bugs, enhancements) or to create a new issue, please visit the <a class="btn btn-default btn-xs" href="http://github.com/LMMS/lmms/issues">LMMS GitHub issues page</a></p>
<!--<div class="row"> -->
<!--<div style="float: right;" class="col-sm-5"> -->

<!--

		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Recent Issues</h3>
			</div>
		<div class="panel-body">
<!-- Loads download links automatically from dl.php -->
<?php //include('feed/issues.php'); 
?>
<!--		</div>
	</div>
<!--   </div> -->
<!-- </div> -->




<? include('footer.php'); ?>
