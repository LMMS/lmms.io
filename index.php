<?php include('header.php'); ?>
<div class="page-header">
   <h1>Welcome</h1>
</div>

<!-- <div class="row"> -->
   <div style="float:right;" class="col-sm-3">
      <div class="panel panel-default">
         <div class="panel-heading">
            <h3 class="panel-title">Downloads</h3>
         </div>
         <div style="max-height: 140px;" class="panel-body">
<!-- Loads download links automatically from dl.php -->
<?php include('feed/releases.php'); ?>
         </div>
      </div>
   </div>
<!-- </div> -->



<p>This is the home of the LMMS community.  LMMS is a free cross-platform software which allows you to produce music with your computer. This includes creating melodies and beats, synthesizing and mixing sounds and arranging samples. You can have fun with your MIDI keyboard and much more all in a user-friendly and modern interface. Furthermore LMMS comes with many ready-to-use instrument and effect plugins, presets and samples.</p>
<br><br>
<div class="page-header">
   <h1>Features</h1>
</div>
<a target="new" href="http://lmms.sourceforge.net/screenshots/1.0.0/lmms-1.0.0-3.png"><img class="img-thumbnail" alt="426x266" src="http://lmms.sourceforge.net/screenshots/1.0.0/lmms-1.0.0-3.png" style="float:right; width: 426px; height: 266px;"></a>
<ul>
  <li>Song-Editor for composing songs</li>
  <li>A Beat+Bassline-Editor for creating beats and basslines</li>
  <li>An easy-to-use Piano-Roll for editing patterns and melodies</li>
  <li>An FX mixer with 64 FX channels and arbitrary number of effects allow unlimited mixing possibilities</li>
  <li>Many powerful instrument and effect plugins out of the box</li>
  <li>Full user-defined track-based automation and computer-controlled automation sources</li>
  <li>Compatible with many standards such as SoundFont2, VST(i), LADSPA, GUS Patches, and MIDI</li>
  <li>Import of MIDI files, Hydrogen project files and FL Studio ® project files</li>
</ul>
<br>

<?php include('footer.php'); ?>
