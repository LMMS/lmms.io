<?php include('header.php'); ?>
<div class="page-header">
	<h3>Welcome to the home of the LMMS community</h3>
</div>

<!-- <div class="row"> -->
	<div style="float:right;" class="col-sm-3">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Download Now</h3>
			</div>
			<div style="max-height: 140px;" class="panel-body">
<!-- Loads download links automatically from dl.php -->
<?php include('feed/releases.php'); ?>
			</div>
		</div>
	</div>
<!-- </div> -->


<h4>LMMS is a free cross-platform software which allows you to produce music with your computer.</h4>
<p>Creating melodies and beats, synthesizing and mixing sounds, arranging samples and much more.<br>
Playback instruments, samples and plugins with a typing or MIDI keyboard in a user-friendly and modern interface.<br>
Bundled with many ready-to-use instrument and effect plugins, presets and samples.</p>
<br><br>
<div class="page-header">
	<h1>Features</h1>
</div>
<div style="float:right;"> 
<a target="new" href="img/ss_proj.png"><img class="img-thumbnail" alt="350x169" src="img/th_ss_proj.png" style="float: top; width: 350px; height: 169px;"></a><br><br>
<a target="new" href="img/ss_song.png"><img class="img-thumbnail" alt="350x169" src="img/th_ss_song.png" style="float: middle; width: 350px; height: 169px;"></a><br><br>
<a target="new" href="img/ss_instr.png"><img class="img-thumbnail" alt="350x169" src="img/th_ss_instr.png" style="float: bottom; width: 350px; height: 169px;"></a>
</div>
<ul>
    <li>Compose music on Windows, Linux and Apple OS&nbsp;X</li>	
    <li>Sequence, compose, mix and automate songs in one simple interface</li>
	<li>Note playback via MIDI or typing keyboard</li>
	<li>Consolidate instrument tracks using Beat+Bassline Editor</li>
	<li>Fine tune patterns, notes, chords and melodies using Piano Roll Editor</li>
	<li>FX mixer with unlimited FX channels:</li>
		<ul>
			<li>Drop-in LADSPA plug-in support</li>
			<li>Drop-in VST ® effect plug-in support on (Linux and Windows)</li>
			<li>Built-in compressor, limiter, delay, reverb, distortion, EQ, bass-enhancer</li>
			<li>Bundled graphic and parametric equalizers</li>
			<li>Built-in visualization/spectrum analyser</li>
		</ul>
	<li>Over 15 built-in instruments including:</li>
		<ul>
		    <li>Built-in 32-bit VST instrument support</li>
			<li>Built-in 64-bit VST instrument support with 32-bit VST bridge (64-bit Windows)</li>
			<li>Roland ® TB-303 style monophonic bass synthesizer</li>
			<li>Embedded ZynAddSubFx:  Polyphonic, mutlitimbral, microtonal, multi-voice additive, subtractive and pad synthesis all in one powerful plugin</li>
			<li>Native Commodore 64 ® SID microchip/instrument emulation</li>
			<li>Native SoundFont ® support (SF2), the industry standard for high quality instrument patches and banks</li>
			<li>Nintendo ®, GameBoy ® and game sound effect emulation</li>
			<li>2 built-in oscillator-based synthesizers</li>
			<li>2 built-in wavetable-based synthesizers</li>
			<li>Gravis UltraSound ® GUS Patch support</li>
		</ul>
	<li>Full user-defined track-based automation and computer-controlled automation sources</li>
	<li>Import of MIDI files, Hydrogen project files and FL Studio ® project files</li>
</ul>
<br>

<?php include('footer.php'); ?>
