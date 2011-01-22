<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="imagetoolbar" content="no" />
<title>Accès administrateur</title>
<link media="screen" rel="stylesheet" type="text/css" href="css/login.css"  />
<!--[if lte IE 6]><link media="screen" rel="stylesheet" type="text/css" href="css/login-ie.css" /><![endif]-->
<!-- aurora-theme is default -->

<link media="screen" rel="stylesheet" type="text/css" href="css/login-blue.css"  />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
</head>

<body>
	<!--[if !IE]>start wrapper<![endif]-->
	<div id="wrapper">
	<div id="wrapper2">
	<div id="wrapper3">
	<div id="wrapper4">
	<span id="login_wrapper_bg"></span>
	
	<div id="stripes">
		
		<!--[if !IE]>start login wrapper<![endif]-->
		<div id="login_wrapper">
			<?if(isset($errLogin)){?>
			<div class="error">
				<div class="error_inner">
					<strong>Accès refusé</strong> | <span>login / mot de passe erroné</span>
				</div>
			</div>
			<?}?>
			
			<!--[if !IE]>start login<![endif]-->
			<form id="loginForm" action="index.php" method="post">
				<fieldset>
					
					<h1>Accès Administrateur</h1>
					<div class="formular">
						<span class="formular_top"></span>
						
						<div class="formular_inner">
						
						<label>
							<strong>Login :</strong>

							<span class="input_wrapper">
								<input name="login" type="text" />
							</span>
						</label>
						<label>
							<strong>Mot de passe :</strong>
							<span class="input_wrapper">
								<input name="password" type="password" />

							</span>
						</label>
						
						
						<ul class="form_menu">
							<li><span class="button"><span><span><a href="javascript:void(0)" class="go">Connexion</a></span></span></span></li>
						</ul>
						
						</div>
						
						<span class="formular_bottom"></span>
						
					</div>
				</fieldset>
			</form>
			<!--[if !IE]>end login<![endif]-->
			
			<!--[if !IE]>start reflect<![endif]-->
			<span class="reflect"></span>
			<span class="lock"></span>
			<!--[if !IE]>end reflect<![endif]-->
			
			
		</div>

		<!--[if !IE]>end login wrapper<![endif]-->
	    </div>
		</div>
     	</div>
		</div>	
	</div>
	<!--[if !IE]>end wrapper<![endif]-->
	<script>
		$(document).ready(function(){
			$(".go").click(function(){
				$("#loginForm").submit();
			});
		});
	</script>
</body>
</html>
