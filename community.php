<?php include('header.php'); ?>

<div class="page-header"><h1>Social Media</h1></div>
<a class="btn btn-lg btn-success" target="new" href="/forum/">Forums</a>
<a class="btn btn-lg btn-primary" target="new" href="http://facebook.com/makefreemusic">Facebook</a>
<a class="btn btn-lg btn-warning" target="new" href="http://soundcloud.com/groups/linux-multimedia-studio">SoundCloud</a>
<a class="btn btn-lg btn-danger" target="new" href="https://plus.google.com/u/0/113001340835122723950/posts">Google+</a><br><br>
<div class="page-header">
   <h1>Development <a class="img-thumbnail" href="https://travis-ci.org/LMMS/lmms"><img src="https://travis-ci.org/LMMS/lmms.svg"></a></h1></div>
<h2>Build LMMS</h2>
<p>Building LMMS from source requires Linux or BSD/Unix (including Apple) operating system.  Visit the <a class="btn btn-xs btn-success" href="http://github.com/LMMS/lmms/wiki">LMMS GitHub wiki</a> for compile instructions.</p><br>
<h2>Bug Tracker</h2>
<p>To view open issues (bugs, enhancements) or to create a new issue, please visit the <a class="btn btn-xs btn-success" href="http://github.com/LMMS/lmms/issues">LMMS GitHub issues page</a></p>
<!-- <div class="row"> -->
<!--   <div style="float: right;" class="col-sm-5"> -->
      <div class="panel panel-default">
         <div class="panel-heading">
            <h3 class="panel-title">Recent Issues</h3>
         </div>
         <div class="panel-body">
<!-- Loads download links automatically from dl.php -->
<?php include('github/issues.php'); ?>
         </div>
      </div>
<!--   </div> -->
<!-- </div> -->




<? include('footer.php'); ?>
