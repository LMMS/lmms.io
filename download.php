<?php include('header.php'); ?>
<?php include('feed/releases.php'); ?>
<div class="page-header">
	<h1><?php echo get_page_name(); ?> Page Placeholder</h1>
	<p>Please edit <code><?php echo strtolower(get_page_name()) . '.php'; ?></code></p>
	<h3>Horizontal Downloads Test</h3>
	<?php @get_releases(1, "horiz"); ?><br><br>
	<h3>Vertical Downloads Test</h3>
	<?php @get_releases(1, "vert"); ?><br><br>
	<h3>All Downloads Test</h3>
	<?php @get_releases(999, "horiz"); ?><br><br>
</div>


<?php ?>
<?php include('footer.php'); ?>
