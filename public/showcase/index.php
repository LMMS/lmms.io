<?php include('../header.php'); ?>
<div class="jumbotron jumbo">
	<div class="container">
		<h1 class="jumbo">Tracks made with LMMS</h1>
	</div>
</div>
<?php begin_content(); ?>

<div class="text-center">
<div id="wait-div" class="text-center">
	<img src='/img/loading.gif' /><br>
</div>
<iframe class="bandcamp-iframe" src="http://bandcamp.com/EmbeddedPlayer/album=2796846066/size=large/bgcol=ffffff/linkcol=0687f5/artwork=small/transparent=true/" seamless><a href="http://lmmsartists.bandcamp.com/album/the-best-of-lmms-vol-1">The Best of LMMS Vol.1 by LMMS Artists</a></iframe>
</div>

<?php include('../footer.php'); ?>

<script>
$('.bandcamp-iframe').load(function() {
	$('#wait-div').hide();
});
</script>
