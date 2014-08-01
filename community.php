<?php include('header.php'); ?>

<div class="page-header"><h1>LMMS Community</h1></div>
<a class="btn btn-success" target="_blank" href="/forum/"><span class="fa fa-comments"></span> Forums</a>
<a class="btn btn-primary" target="_blank" href="http://facebook.com/makefreemusic"><span class="fa fa-facebook"></span> Facebook</a>
<a class="btn btn-warning" target="_blank" href="http://soundcloud.com/groups/linux-multimedia-studio"><span class="fa fa-soundcloud"></span> SoundCloud</a>
<a class="btn btn-danger" target="_blank" href="https://plus.google.com/u/0/113001340835122723950/posts"><span class="fa fa-google-plus"></span> Google+</a><br><br>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Recent Forum Discussions</h3>
	</div>
   <div class="panel-body">
<?php include('feed/forums.php'); ?>
	</div>
</div>

<div class="page-header">
	<h1>Development <a href="https://travis-ci.org/LMMS/lmms"><img src="https://travis-ci.org/LMMS/lmms.svg"></a></h1>
</div>

<h2>Build LMMS</h2>
<p>Building LMMS from source requires Linux or BSD/Unix (including Apple) operating system.  Visit the <a class="btn btn-default btn-xs" href="http://github.com/LMMS/lmms/wiki">LMMS GitHub wiki</a> for compile instructions.</p><br>

<h2>Bug Tracker</h2>
<p>To view open issues (bugs, enhancements) or to create a new issue, please visit the <a class="btn btn-default btn-xs" href="http://github.com/LMMS/lmms/issues">LMMS GitHub issues page</a></p>
<!--<div class="row"> -->
<!--<div style="float: right;" class="col-sm-5"> -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Recent Issues</h3>
			</div>
		<div class="panel-body">
<!-- Loads download links automatically from dl.php -->
<?php include('feed/issues.php'); ?>
		</div>
	</div>
<!--   </div> -->
<!-- </div> -->




<? include('footer.php'); ?>
