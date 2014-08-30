<?php global $LSP_URL; ?>
<div class="lsp-sidebar pull-left">
	<div class="panel panel-primary">
	<div class="panel-heading">
	
	<form action="<?php echo $LSP_URL; ?>" method="post">
		<?php
			echo '<input type="hidden" name="category" value="'.@$_GET["category"].'" />'."\n";
			echo '<input type="hidden" name="subcategory" value="'.@$_GET["subcategory"].'" />'."\n";
		?>
		<div class="form-inline">
		<input type="text" id="search" name="search" class="lsp-search form-control textin" maxlength="64" placeholder="Search Content"/>
		<button type="submit" id="ok" name="ok" class="lsp-search btn btn-default textin"><span class="fa fa-search"></span></button>
		</div>
	</form>
	</div>
	<?php get_categories(); ?>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading"><h3 class="panel-title">My account</h3></div>
		<div id="accountmenu" class="panel-body overflow-hidden">
			<?php
			if( @$_GET["action"] == 'logout' )
			{
				unset ($_SESSION["remote_user"]);
				session_destroy();
				$_GET["action"] = $_GET["oldaction"];
				if( $_GET["action"] != "browse" &&
					 $_GET["action"] != "show" &&
						$_GET["file"] != "" )
				{
					$_GET["action"] = "show";
				}
			}

			if(!isset($_SESSION["remote_user"]) && @$_GET["action"] == 'login' && $_POST["ok"] == "Login")
			{
				if (password_match ($_POST["password"],$_POST["login"]))
				{
					$_SESSION["remote_user"] = $_POST["login"];
					$_GET["action"] = $_POST["oldaction"];
					$_GET["category"] = $_POST["category"];
					$_GET["subcategory"] = $_POST["subcategory"];
				}
				else /*if ($_POST["ok"] == 'Login')*/
				{
					echo '<span style="font-weight:bold; color:#f00;">Authentication failure.</span><br />';
				}
			}
			if( !isset( $_SESSION["remote_user"] ) )
			{
				echo '<form action="'.$_SERVER['PHP_SELF'].'?action=login" method="post" role="form">';
				echo '<div class="form-group">';
				echo '<label for="login">Username</label>';
				echo '<input type="text" id="login" name="login" class="form-control textin" maxlength="10" placeholder="username" />';
				echo '</div>';
				echo '<div class="form-group">';
				echo '<label for="password">Password</label>';
				echo '<input type="password" id="password" name="password" class="form-control textin" maxlength="15" placeholder="password"/>';
				echo '</div>';
				echo '<button type="submit" name="ok" class="btn btn-default textin" />Login</button>';
				echo '</form>';

				echo '<input type="hidden" name="file" value="'.@$_GET["file"].'" />'."\n";
				echo '<input type="hidden" name="category" value="'.@$_GET["category"].'" />'."\n";
				echo '<input type="hidden" name="subcategory" value="'.@$_GET["subcategory"].'" />'."\n";
				echo '<input type="hidden" name="oldaction" value="'.@$_GET["action"].'" />'."\n";
				echo '</p></form><br />';
				echo '<a href="?action=register"><span class="fa  fa-chevron-circle-right"></span>&nbsp;Not registered yet?</a>';
			}

			if( isset( $_SESSION["remote_user"] ) )
			{
				echo 'Hello '.$_SESSION["remote_user"].'!<br />';
				echo '<div><ul>';
				echo '<li><a href="?content=add">Add file</a> '."\n";
				echo '<li><a href="?action=browse&user='.$_SESSION["remote_user"].'">My content</a> '."\n";
				echo '<li><a href="?account=settings">Account settings</a> '."\n";
				echo '<li><a href="?action=logout&oldaction='.$_GET["action"].'&file='.$_GET["file"].'&f='.$_GET["category"].'&subcategory='.$_GET["subcategory"].'">Logout</a> '."\n";
				echo '</ul></div>';
			}
			?>
		</div>
	</div>
</div>
