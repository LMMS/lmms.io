<?php include('header.php'); ?>

<h1 class="text-center">LMMS Community</h1>
<hr id="hr" class="show hidden-sm hidden-xs">
<div id="alert" class="alert show text-center alert-warning hidden-sm hidden-xs" role="alert">
	<a class="close" onclick="hideAlert()">×</a>
	<a class="close pull-left" onclick="hideAlert()">×</a>
	<p>Click a button to be redirected to that page.  Clicking on the down arrow &nbsp;<strong><span class="fa fa-arrow-down"></span></strong>&nbsp; below each button will preview its content.</p>
	</ul>
</div>

<div class="row overflow-auto">
<table id="community-table" class="table">
	<tr>
		<td><label id="forums-button" class="btn btn-default" target="_self" data-href="/forum/" onclick="show(this)">
			<span class="fa-5x fa fa-comments"></span> <span class="visible-lg-inline"><br>Forums</span>
		</label></td>
		
		<!-- 
		<td><label id="facebook-button" title="Visit page" class="btn btn-default" onclick="show(this)" target="_blank" data-href="http://facebook.com/makefreemusic">
			<span class="fa-5x fa fa-facebook"></span> <span class="visible-lg-inline"><br>Facebook</span>
		</label></td>
		-->

		<td><label id="soundcloud-button" title="Visit page" class="btn btn-default" onclick="show(this)" target="_blank" data-href="https://soundcloud.com/lmmsio">
			<span class="fa-5x fa fa-soundcloud"></span> <span class="visible-lg-inline"><br>SoundCloud</span>
		</label></td>

		<td><label id="google+-button" title="Visit page" class="btn btn-default" onclick="show(this)" target="_blank" data-href="https://plus.google.com/u/0/113001340835122723950/posts">
			<span class="fa-5x fa fa-google-plus"></span> <span class="visible-lg-inline"><br>Google+</span>
		</label></td>

		<td><label id="youtube-button" title="Visit page" class="btn btn-default" onclick="show(this)" target="_blank" data-href="http://youtube.com/lmmsofficial"><span class="fa-5x fa fa-youtube"></span>
			<span class="visible-lg-inline"><br>YouTube</span>
		</label></td>

		<td><label id="github-button" title="Visit page" class="btn btn-default" onclick="show(this)" target="new" data-href="http://github.com/LMMS/lmms">
			<span class="fa-5x fa fa-github"></span> <span class="visible-lg-inline"><br>GitHub</span>
		</label></td>
	</tr>
	<tr>
		<td><label data-name="Forums" id="forums-toggle" title="Preview content" class="btn btn-default dropdown-toggle" onclick="show('#forums')"><span class="fa fa-arrow-down"></span></label></td>

		<!--
		<td><label data-name="Facebook" id="facebook-toggle" title="Preview content" class="btn btn-default dropdown-toggle" onclick="show('#facebook')"><span class="fa fa-arrow-down"></span></label></td>
		-->
		
		<td><label data-name="Soundcloud" id="soundcloud-toggle" title="Preview content" class="btn btn-default dropdown-toggle" onclick="show('#soundcloud')"><span class="fa fa-arrow-down"></span></label></td>

		<td><label data-name="Google+" id="google+-toggle" title="Preview content" class="btn btn-default dropdown-toggle" onclick="show('#google+')"><span class="fa fa-arrow-down"></span></label></td>

		<td><label data-name="YouTube" id="youtube-toggle" title="Preview content" class="btn btn-default dropdown-toggle" onclick="show('#youtube')"><span class="fa fa-arrow-down"></span></label></td>

		<td><label data-name="GitHub" id="github-toggle" title="Preview content" class="btn btn-default dropdown-toggle" onclick="show('#github')"><span class="fa fa-arrow-down"></span></label></td>
	</tr>
</table>
</div>

	<div id="wait-div" class="panel-body text-center show">
		<img src='/img/loading.gif' /><br>
	</div>

	<div id="forums-div" class="panel-body hidden">
		<?php include('feed/forums.php'); ?>
	</div>

	<div id="facebook-div" class="panel-body hidden">
        <h3 class="text-center">Sorry, this feed is temporarily disabled.</h3>
        <?php // include('feed/facebook.php'); ?>
        </div>


	<div id="soundcloud-div" class="panel-body hidden">
		<?php include('feed/soundcloud.php'); ?>
	</div>

	<div id="google+-div" class="panel-body hidden">
		<?php include('feed/google+.php'); ?>
	</div>

	<div id="github-div" class="panel-body hidden">
		<?php include('feed/issues.php'); ?>
	</div>

	<div id="youtube-div" class="panel-body hidden">
		<?php include('feed/youtube.php'); ?>
	</div>

</div>

<script>
	function show(obj) {
		if ($(obj).attr('data-href')) {
			$(obj).button('toggle');
			return window.open($(obj).attr('data-href'), $(obj).attr('target'));
		}
		$("div[id$='-div']").hide();
		$("div[id$='-div']").removeClass('show');
		$("label[id$='-toggle']").removeClass('active');

		createHoverEffect('#forums', 'btn-success');
		createHoverEffect('#facebook', 'btn-primary');
		createHoverEffect('#soundcloud', 'btn-warning');
		createHoverEffect('#google+', 'btn-danger');
		createHoverEffect('#youtube', 'btn-danger');
		createHoverEffect('#github', 'btn-dark');

		if (obj.indexOf('#') != 0) {
			obj = '#' + obj;
		}

		// jQuery doesn't like plus signs
		var btn = $(obj.replace(/\+/g, "\\+") + '-button');
		var div = $(obj.replace(/\+/g, "\\+") + '-div')
		var tog = $(obj.replace(/\+/g, "\\+") + '-toggle');


		switch (obj) {
			case '#forums':
				reverseHoverEffect(obj, "btn-success");
				break;
			case '#facebook':
				reverseHoverEffect(obj, "btn-primary");
				break;
			case '#soundcloud':
				reverseHoverEffect(obj, "btn-warning");
				break;
			case '#youtube':
			case '#google+':
				reverseHoverEffect(obj, "btn-danger");
				break;
			case '#github':
				reverseHoverEffect(obj, "btn-dark");
				break;
		}

		/*$(obj.replace(/\+/g, "\\+") + '-button').unbind('mouseenter mouseleave');*/

		var title = obj.substring(1, obj.length); // remove hash

		//$('#alert-title').text('LMMS ' + title.toUpperCase() + ' ');
		//$('#alert-text').text('Below is a sample of recent activity from our ' + title + ' page.  Please click on an item to be redirected to that page.');

		// Play nicely with boostrap
		div.removeClass('hidden');
		div.show();

		tog.addClass("active");
		location.hash = obj;
	}


	function autoSelect() {
		show('#forums');
	}

	/*
	 * Adds hover-style tooltips to the buttons
	 */
	function addTooltips() {
		$("label[id$='-button']").each(function() {
			var name = $(this).text().trim().toLowerCase();
			$(this).attr('data-toggle', 'tooltip')
				.attr('data-placement', 'top')
				.attr('title', 'Visit ' + name + ' page');
			$(this).tooltip();
				});

		$("label[id$='-toggle']").each(function() {
			var name = $(this).data('name').toLowerCase();
			$(this).attr('data-toggle', 'tooltip')
				.attr('data-placement', 'bottom')
				.attr('title', 'Preview ' + name + ' feed');
			$(this).tooltip();
				});
	}

	/*
	 * Illuminates button to its respective color on hover (Facebook = blue, etc)
	 */
	function createHoverEffect(id, className) {
		$(id.replace(/\+/g, "\\+") + '-button').removeClass(className);
		$(id.replace(/\+/g, "\\+") + '-toggle').removeClass(className);
		$(id.replace(/\+/g, "\\+") + '-button').hover(
		// Enter
		function() {
			$(this).addClass(className);
		},
		// Leave
		function() {
			$(this).removeClass(className);
		});

		$(id.replace(/\+/g, "\\+") + '-toggle').hover(
		// Enter
		function() {
			$(this).addClass(className);
		},
		// Leave
		function() {
			$(this).removeClass(className);
		});
	}

	/*
	 * Inverse-illuminates a button (takes color away on hover) to accommodate active buttons
	 */
	function reverseHoverEffect(id, className) {
		$(id.replace(/\+/g, "\\+") + '-button').addClass(className);
		$(id.replace(/\+/g, "\\+") + '-toggle').addClass(className);

		$(id.replace(/\+/g, "\\+") + '-button').hover(
		// Enter
		function() {
			$(this).removeClass(className);
		},
		// Leave
		function() {
			$(this).addClass(className);
		});

		$(id.replace(/\+/g, "\\+") + '-toggle').hover(
		// Enter
		function() {
			$(this).removeClass(className);
		},
		// Leave
		function() {
			$(this).addClass(className);
		});
	}

	/*
	 * Makes the navbar behave properly when already loaded (a hashtag work-around)
	 * by replacing the menu href with javascript events since hash tags are normally
	 * page anchors and don't refresh the page content.
	 */
	function menuFix() {
		$('li a').each(function (i, a) {
			if (a.href.indexOf('/community/') != -1 && a.href.indexOf('#') != -1
				&& a.innerText.trim() != "Community" && a.innerText.trim() != "Forums") {
				a.href = "javascript:show('" + a.innerText.trim().toLowerCase() + "')";
			}
		});
	}

	function hideAlert() {
		$('#alert').hide();
		$('#hr').hide();
		$('#alert').removeClass('show');
		$('#hr').removeClass('show');
	}

	// Adds a YouTube iframe to the DOM
	function embedVideo(parent, id, width, height) {
		// Don't add the iframe twice
		if ($(parent).has('iframe').length) {
			return;
		}

		// Default to 320x180 if not specified
		switch (arguments.length) {
			case 4: break;
			default:
				width = 320; height = 180;
		}

		// A hack to pause all playing embeds by reloading
		// the the "src" attribute with autoplay param removed
		$('tr').find('iframe').each(function(){
			var tmp = $(this).attr('src');
			$(this).attr('src', '');
			$(this).attr('src', tmp.replace(/(\?autoplay=1)|(\&amp;auto_play=true)/g, ''));
		});

		var iframe = $('<iframe></iframe>');
		iframe.addClass('youtube-thumb');
		iframe.addClass('img-thumbnail');
		iframe.attr('id', 'iframe_' + id);
		iframe.attr('frameborder', 0);
		iframe.attr('allowfullscreen', true);
		$(parent).find('h4').each(function(){
			// Add the YoutTube iframe
			$(this).append(iframe);
			// Hide the thumbnail
			$(this).find('img').each(function(){
				iframe.width($(this).width());
				iframe.height($(this).height());
				$(this).hide();
			});
			// Hide the play button
			$(this).find('div').each(function(){
				$(this).hide();
			});

			// Add the YoutTube iframe
			$(this).append(iframe);
			iframe.animate({'width':width, 'height':height});
			iframe.attr('src', '//www.youtube.com/embed/' + id + '?autoplay=1');
		});
	}

	// Adds a Soundcloud iframe to the DOM
	function embedSound(parent, id, width, height) {
		// Don't add the iframe twice
		if ($(parent).has('iframe').length) {
			return;
		}

		// Default to 320x120 if not specified
		switch (arguments.length) {
			case 4: break;
			default:
				width = 320; height = 160;
		}

		// A hack to pause all playing embeds by reloading
		// the the "src" attribute with autoplay param removed
		$('tr').find('iframe').each(function(){
			var tmp = $(this).attr('src');
			$(this).attr('src', '');
			$(this).attr('src', tmp.replace(/(\?autoplay=1)|(\&amp;auto_play=true)/g, ''));

		});

		var iframe = $('<iframe></iframe>');
		iframe.addClass('soundcloud-thumb');
		iframe.attr('id', 'iframe_' + id);
		iframe.attr('frameborder', 'no');
		$(parent).find('h4').each(function(){
			// Add the YoutTube iframe
			$(this).append(iframe);
			// Hide the thumbnail
			$(this).find('img').each(function(){
				iframe.width($(this).width());
				iframe.height($(this).height());
				$(this).hide();
			});
			// Hide the play button
			$(this).find('div').each(function(){
				$(this).hide();
			});

			// Add the Soundcloud iframe
			$(this).append(iframe);
			iframe.animate({'width':width, 'height':height});
			iframe.attr('src', '//w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/' + id + '&amp;color=ff5500&amp;auto_play=true&amp;show_reposts=true');
		});
	}


	$(function() {
		createHoverEffect('#forums', 'btn-success');
		createHoverEffect('#facebook', 'btn-primary');
		createHoverEffect('#soundcloud', 'btn-warning');
		createHoverEffect('#google+', 'btn-danger');
		createHoverEffect('#youtube', 'btn-danger');
		createHoverEffect('#github', 'btn-dark');
		if (location.hash) {
			try {
				show(location.hash);
			} catch (err) {
				autoSelect();
			}
		} else {
			autoSelect();
		}
		menuFix();
		addTooltips();
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




<?php include('footer.php'); ?>
