<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Music Software">
<meta name="author" content="Tres Finocchiaro">
<link rel="icon" type="image/png" href="/forum/styles/prosilver/imageset/favicon.png" />
<title>LMMS</title></head>
<!-- Bootstrap core CSS -->
<link href="http://getbootstrap.com/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap theme -->
<link href="http://getbootstrap.com/dist/css/bootstrap-theme.min.css" rel="stylesheet">
</head>
<body role="document">
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
   <div class="container">
   <div class="navbar-header">
      <a class="navbar-brand" href="/"><img style="position: relative; top: -2px; margin-right: 5px; width:24; height:24;" src="/forum/styles/prosilver/imageset/favicon.png">LMMS</a>
   </div>
   <div class="navbar-collapse">
      <ul class="nav navbar-nav">
         <?php
            menu_item("Home", "/index.php");
            menu_item("Download");
            menu_item("Screenshots");
            menu_item("Tracks");
            menu_item("Documentation");
            menu_item("Community");
            menu_item("Share", "/lsp/");
         ?>
      </ul>
   </div>
   </div>
</div>
<div class="container theme-showcase" role="main">
<?php 

// Creats a simple tag <li><a href="menu_item.php">Menu Item</a></li>
// Taking into consideration the "active" status/style
function menu_item($text, $url) {
   // Determine the "Active Tab
   if ($text == "Home") {
      switch($_SERVER[REQUEST_URI]) {
         case "/header.php":
         case "/":
         case "/index.php":
         case "/home.php":
            $active = ' class="active"';
            break;
         default:
            $active = '';
      }
   }
   else  {
      if ($_SERVER[REQUEST_URI] == '/' . strtolower($text . '.php') || 
         $_SERVER[REQUEST_URI] == $url) {
         $active = ' class="active"';
      } else {
         $active = '';
      }
   }

   // Build our menu item
   if (is_null($url)) {
      $url = '/' . strtolower($text) . '.php';
   }

   echo '<li' . $active . '><a href="' . $url . '">' . $text . '</a></li>';
}

?>
<!-- FIXME BAD FORMATTING -->
<br><br><br><br>
