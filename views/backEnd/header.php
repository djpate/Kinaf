<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="imagetoolbar" content="no" />
<title><?=$this->pageTitle;?></title>
<link media="screen" rel="stylesheet" type="text/css" href="css/admin.css"  />
<link media="screen" rel="stylesheet" type="text/css" href="css/jquery.asmselect.css"  />
<!--[if lte IE 6]>
<link media="screen" rel="stylesheet" type="text/css" href="css/admin-ie.css" />
<![endif]-->
<link media="screen" rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/cupertino/jquery-ui.css" />
<link media="screen" rel="stylesheet" type="text/css" href="css/admin-blue.css"  />
<!--[if IE 7]>
<link media="screen" rel="stylesheet" type="text/css" href="css/admin-ie7.css" />
<![endif]-->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.validate.js"></script>
<script type="text/javascript" src="js/jquery.asmselect.js"></script>
<script type="text/javascript" src="js/behaviour.js"></script>
<script type="text/javascript" src="js/jquery.datepick-fr.js"></script>
</head>

<body>

	<!--[if !IE]>start wrapper<![endif]-->
	<div id="wrapper">
		
		<!--[if !IE]>start header main menu<![endif]-->
		
		<div id="header_main_menu">
		
		<span id="header_main_menu_bg"></span>
		<!--[if !IE]>start header<![endif]-->
		<div id="header">
			<div class="inner">
				<h1 id="logo"><a href="#">websitename <span>Administration Panel</span></a></h1>
				
				<!--[if !IE]>start user details<![endif]-->
				<div id="user_details">
					<ul id="user_details_menu">
						<li class="welcome">Bienvenue <strong><?=$this->admin->get('prenom');?></strong></li>
						
						<li>
							<ul id="user_access">
								<li class="last"><a href="index.php?doLogout=1">Se deconnecter</a></li>
							</ul>
						</li>
						
						
					</ul>
					
					<div id="server_details">
						<dl>
							<dt>Date :</dt>
							<dd><?=date("H:i:s");?> | <?=date("m/d/Y");?></dd>
						</dl>
					</div>
					
				</div>
				
				<!--[if !IE]>end user details<![endif]-->
			</div>
		</div>
		<!--[if !IE]>end header<![endif]-->
		
		<!--[if !IE]>start main menu<![endif]-->
		<div id="main_menu">
			<div class="inner">
			<ul>
				<?
				foreach($this->mainMenu as $id => $val){
					?>
					<li>
						<a <?if($val['rewriteUrl']==$this->getParentUrl()){echo 'class="selected_lk"';}?> href="index.php?page=<?=$val['rewriteUrl'];?>"><span class="l"><span></span></span><span class="m"><em><?=$val['pageTitle'];?></em><span></span></span><span class="r"><span></span></span></a>
						<?if($val['rewriteUrl']==$this->getParentUrl()){
							if(count($this->subMenu)>0){
								echo "<ul>";
									foreach($this->subMenu as $id => $val){
										?>
										<li><a <?if($val['rewriteUrl']==$this->rewriteUrl){echo 'class="selected_lk"';};?>href="index.php?page=<?=$val['rewriteUrl'];?>"><span class="l"><span></span></span><span class="m"><em><?=$val['pageTitle'];?></em><span></span></span><span class="r"><span></span></span></a></li>
										<?
									}
								echo "</ul>";
							}
						}?>
					</li>
					<?
				}
				?>
			</ul>
			</div>
			<span class="sub_bg"></span>
		</div>
		<!--[if !IE]>end main menu<![endif]-->
		
		</div>
		
		<!--[if !IE]>end header main menu<![endif]-->
		
		
		
		
		<!--[if !IE]>start content<![endif]-->
		<div id="content">
			<div class="inner">
				<!--[if !IE]>start section<![endif]-->
				<div class="section" style="padding:0">
					<ul class="system_messages">
						<li class="white"><span class="ico"></span><strong class="system_title"></strong><a href="#" class="close">FERMER</a></li>
						<li class="red"><span class="ico"></span><strong class="system_title"></strong> <a href="#" class="close">FERMER</a></li>
						<li class="blue"><span class="ico"></span><strong class="system_title"></strong> <a href="#" class="close">FERMER</a></li>
						<li class="green"><span class="ico"></span><strong class="system_title"></strong> <a href="#" class="close">FERMER</a></li>
						<li class="yellow"><span class="ico"></span><strong class="system_title"></strong> <a href="#" class="close">FERMER</a></li>
					</ul>
					
				</div>
				<!--[if !IE]>end section<![endif]-->
