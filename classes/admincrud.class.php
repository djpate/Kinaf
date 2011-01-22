<?php
	class AdminCrud{
		
		protected $pdo;
		protected $class;
		protected $instance;
		
		protected $fields = array();
		protected $filtres = array();
		protected $multiples = array();
		protected $contraintes = array();
		protected $prettyFields = array();
		
		/* variable pour la pagination */
		protected $parPage = 25;
		protected $adjacent = 3;
				
		/* variables d'affichage a tweaker */
		protected $titreListing = "Liste";
		protected $titreFiltre = "Filtre de recherche";
		protected $listingVide = "Aucun résultat";
		protected $validSupprimer = "Supprimer avec succès";
		protected $validSave = "Enregistrer avec succès";
		protected $titreNew = "Ajouter";
		protected $titreShowFiltre = "Modifier les filtres";
		protected $titreHideFiltre = "Cacher les filtres";
		
		/* variables des actions possibles */
		protected $canDelete = 1;
		protected $canAdd = 1;
		protected $canUpdate = 1;
		
		public function __construct($c){
			$this->pdo = db::singleton();
			$this->class = $c;
			$this->instance = new $this->class();
		}
		
		public function set($n,$v){
			$this->$n = $v;
		}
		
		public function addField(array $field){
			$this->prettyFields[$field['champ']] = $field['designation'];
			array_push($this->fields,$field);
		}
		
		public function addPrettyField($champ,$designation){
			$this->prettyFields[$champ] = $designation;
		}
		
		public function addFiltre($field){
			array_push($this->filtres,$field);
		}
		
		public function addMultiple($multiple){
			array_push($this->multiples,$multiple);
		}
		
		public function addContrainte($name,$contrainte,$argument="true"){
			if(isset($this->contraintes[$name])){
				array_push($this->contraintes[$name],array($contrainte,$argument));
			} else {
				$this->contraintes[$name] = array();
				array_push($this->contraintes[$name],array($contrainte,$argument));
			}
		}
		
		public function display(){
			if(isset($_REQUEST['listing'])){
				$this->displayListing();
			} else if(isset($_REQUEST['delete'])){
				$this->delete($_REQUEST['delete']);
			} else if(isset($_REQUEST['view'])){
				$this->fiche($_REQUEST['view']);
			} else if(isset($_REQUEST['save'])){
				$this->save($_REQUEST['save']);
			} else {
				$this->displayAjaxLoader();
			}
		}
		
		protected function displayAjaxLoader(){
			?>
				<div id="dialog-confirm-supp" title="Supprimer cet élément ?">
					<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Vous êtes sur le point de supprimer un élément de la base de donnée. Etes-vous sûr de vouloir continuer ?</p>
				</div>

				<form id="listingVal">
					<input type="hidden" name="noWrap" value="1">
					<input type="hidden" name="listing" value="1">
					<input type="hidden" name="pg" id="pg" value="1">
					<input type="hidden" name="order" id="order">
					<input type="hidden" name="order_sens" id="order_sens">
					<?
					foreach($_GET as $id => $val){
						if($id!="page"&&!empty($val)){
							?><input type="hidden" name="<?=$id;?>" value="<?=$val;?>"><?
						}
					}
					?>
				</form>
				<div id="display">
				
				</div>
				<script>
				
					var delid;
				
					function reload(){
						$("#display").load("index.php?page=<?=$_REQUEST['page'];?>",$("#listingVal").serialize()+"&"+$("#formFiltre").serialize());
					}
					
					function goToPage(p){
						$("#pg").val(p);
						reload();
					}
					
					function del(id){
						delid = id;
						$("#dialog-confirm-supp").dialog('open');
					}
					
					function show(id){
						$("#display").load("index.php?page=<?=$_REQUEST['page'];?>",{'view':id,'noWrap':1});
					}
					
					function create(){
						$("#display").load("index.php?page=<?=$_REQUEST['page'];?>",{'view':'new','noWrap':1});
					}
					
					function showFiltre(){
						$("#showFiltreLink").hide();
						$("#hideFiltreLink").show();
						$("#filtreContent").show();
					}
					
					function hideFiltre(){
						$("#showFiltreLink").show();
						$("#hideFiltreLink").hide();
						$("#filtreContent").hide();
					}
					
					$(document).ready(function(){
						reload();
						$("#dialog-confirm-supp").dialog({
							resizable: false,
							autoOpen:false,
							height:200,
							width:400,
							modal: true,
							buttons: {
								'Supprimer': function() {
									$.post("index.php?page=<?=$_REQUEST['page'];?>",{'delete':delid},function(){
										reload();
										msg("green","<?=$this->validSupprimer;?>");
									});
									$(this).dialog('close');
								},
								'Annuler': function() {
									$(this).dialog('close');
								}
							}
						});
					});
				</script>
			<?
		}
		
		protected function displayVal($type,$id,$val,$obj=null){
			if($type=="tinyint"){
				if($val==1){
					return '<img src="css/layout/approved.gif" />';
				} else {
					return '<img src="css/layout/action4.gif" />';
				}
			} elseif($type=="multiple"){
				if(count($val)>0){
					$tmp = "";
					foreach($val as $value){
						$tmp .= $value."<br />";
					}
					return $tmp;
				} else {
					return "N/A";
				}
			} elseif($type=="password"){
				return "*******"; // le mot de passe est crypté du coup ca ne sert a rien de montrer la string crypté
			} elseif($type=="file"){
				$func = "link".$id;
				$root = new parametre(4);
				if($val!=""){
					if(getimagesize($root->get('valeur')."/".$obj->$func())){
						return "<img height=\"100\" src=../".$obj->$func()." />";
					} else {
						return $val;
					}
				}
			} else {
				return $val;
			}
		}
		
		protected function delete($id){
			$o = new $this->class($id);
			$o->delete();
		}
		
		protected function generateListingQuery(){
			$q  = "select id from ".$this->instance->get('table')." where 1";
			foreach($_REQUEST as $id => $val){
				if($val!=""){
					$orm = $this->instance->get('orm');
					if(array_key_exists($id,$orm)){ // si dans request un champ correspond a un des champs dans l'orm
						if($orm[$id]=="object"){
							$q .= " and $id = ".$val;
						} else {
							$q .= " and $id like '%".$val."%'";
						}
					}
				}
			}
			if(!empty($_REQUEST['order'])&&!empty($_REQUEST['order_sens'])){
				$q .= " order by ".$_REQUEST['order']." ".$_REQUEST['order_sens'];
			}
			return $q;
		}
		
		/* function propre a l'edition 
		 * j'ai fais une fonction par type de champ comme ca on pourra facilement en réécrire une en surcharge */
		
		protected function editVarchar($name,$val){
			return "<span class=\"input_wrapper\"><input type=text class=\"text\" name=\"$name\" value=\"$val\" /></span>";
		}
		
		protected function editPassword($name,$val){
			return "<span class=\"input_wrapper\"><input type=password name=\"$name\" value=\"\" /></span>";
		}
		
		protected function editObject($name,$val){
			$obj = new $name();
			$q = $this->pdo->query("select count(id) as cnt from ".$obj->get('table'))->fetch();
			if($q['cnt']>30){ // si plus de 30 resultat dans la drop down on fais un autocomplete car sinon c trop lourd pour la bdd et pour le user
				if($val==0){
					$val = new $name();
				}
				$ret = "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"".$val->get('id')."\" />";
				$ret .= "<input type=\"text\" class=\"autocomplete\" rel=\"".$name."\" value=\"".$val."\" />";
			} else {
				$q = $this->pdo->query("select id from ".$obj->get('table'));
				$ret =  "<select name=\"$name\"><option value=0>---</option>";
				
					foreach($q as $row){
						$o = new $name($row['id']);
						if(($val!=0)&&$val->get('id')==$o->get('id')){
							$selected = "selected";
						} else {
							$selected = "";
						}
						$ret .= "<option value=\"".$o->get('id')."\" $selected>".$o."</option>";
					}
				$ret .= "</select>";
			}
			return $ret;
		}
		
		protected function editDate($name,$val){
			return "<input class=\"date\" type=\"text\" name=\"$name\" value=\"$val\" />";
		}
		
		protected function editText($name,$val){
			return "<textarea name=\"$name\">$val</textarea>";
		}
		
		protected function editWysiwyg($name,$val){
			return "<textarea class=\"wysiwyg\" name=\"$name\">$val</textarea>";
		}
		
		protected function editTinyint($name,$val){
			if($val==1){
				$c = "checked";
			} else {
				$c = "";
			}
			return "<input type=\"hidden\" name=\"$name\" value=\"0\"><input type=\"checkbox\" name=\"$name\" value=\"1\" $c>";
		}
		
		protected function editFile($name,$val){
			return "<input type=\"file\" name=\"$name\">";
		}
		
		protected function editMultiple($name,$val){
			$q = $this->pdo->query("select id from $name");
			$tmp = "<select name=\"multiple_".$name."[]\" multiple=\"multiple\" title=\"Ajouter\">";
			foreach($q as $subr){
				$selected = "";
				$o = new $name($subr['id']);
				foreach($val as $c){
					if($c->get('id') == $o->get('id')){
						$selected = "selected";
						break;
					}
				}
				$tmp .= "<option $selected value=".$o->get('id').">$o</option>";
			}
			$tmp .="</select>";
			return $tmp;
		}
		
		protected function generateEditField($type,$name,$val){
			switch($type){
				case 'varchar':
					return $this->editVarchar($name,$val);
				break;
				case 'int':
					return $this->editVarchar($name,$val);
				break;
				case 'object':
					return $this->editObject($name,$val);
				break;
				case 'date':
					return $this->editDate($name,$val);
				break;
				case 'text':
					return $this->editText($name,$val);
				break;
				case 'wysiwyg':
					return $this->editWysiwyg($name,$val);
				break;
				case 'tinyint':
					return $this->editTinyint($name,$val);
				break;
				case 'file':
					return $this->editFile($name,$val);
				break;
				case 'multiple':
					return $this->editMultiple($name,$val);
				break;
				case 'password':
					return $this->editPassword($name,$val);
				break;
				default:
					return $this->editVarchar($name,$val);
				break;
			}
		}
		
		protected function fiche($id){
			if(is_numeric($id)){
				$o = new $this->class($id);
			} else {
				$o = new $this->class();
			}
			?>
			<!--[if !IE]>start section<![endif]-->
				<div class="section">
					
					<!--[if !IE]>start title wrapper<![endif]-->
					<div class="title_wrapper">
						<span class="title_wrapper_top"></span>
						<div class="title_wrapper_inner">
							<span class="title_wrapper_middle"></span>
							<div class="title_wrapper_content">
								<h2><? if($o->get('id')!=0){ echo "Fiche $o";} else { echo "Ajout"; }?></h2>
								<ul class="section_menu section_nav right">
									<li><a href="javascript:void(0)" onclick="reload()" class="section_back"><span class="l"><span></span></span><span class="m"><em>Retour au listing</em><span></span></span><span class="r"><span></span></span></a></li>
									<? if($o->get('id')!=0&&$this->canDelete){?><li><a href="javascript:void(0)" onclick="del(<?=$id;?>)" class="section_delete"><span class="l"><span></span></span><span class="m"><em>Supprimer</em><span></span></span><span class="r"><span></span></span></a></li><? } ?>
								</ul>
							</div>
						</div>
						<span class="title_wrapper_bottom"></span>
					</div>
					<!--[if !IE]>end title wrapper<![endif]-->
					
					<!--[if !IE]>start section content<![endif]-->
					<div class="section_content">
						<span class="section_content_top"></span>
						
						<div class="section_content_inner">
						
						<!--[if !IE]>start product page<![endif]-->
						<div id="product_page">
							<!--[if !IE]>start product content<![endif]-->
								<div id="product_content" style="width:100%">
									<!--[if !IE]>start modules<![endif]-->
									<div class="modules">
										<div class="module">
											<div class="module_top">
												<h5>Visualisation de la fiche</h5>
												<span class="editModifLink">
													<a href="#" class="edit_module">Modifier</a>
												</span>
												<span class="editSaveLink">
													<a href="#" class="edit_cancel">Annuler</a>&nbsp;&nbsp;
													<a href="#" class="edit_save">Enregistrer</a>
												</span>
											</div>
											<div class="module_bottom">
											<iframe name="hiddenIframe" style="display:none" />
											<form id="saveForm" class="search_form" method="post" enctype="multipart/form-data" target="hiddenIframe">
												<input type="hidden" name="save" value="<?=$o->get('id');?>">
												<input type="hidden" name="noWrap" value="1">
											<?
											$i=1;
											$orm = $o->get('orm');
											foreach($orm['fields'] as $id => $val){
												?>
													<div class="champ">
														<label><?if(isset($this->prettyFields[$id])){echo ucfirst($this->prettyFields[$id]);} else { echo ucfirst($id); }?></label>
														<div class="viewChamp">
															<?=$this->displayVal($val,$id,$o->get($id),$o);?>
														</div>
														<div class="editChamp">
															<?=$this->generateEditField($val,$id,$o->get($id));?>
														</div>
													</div>
												<?
												if($i%2==0){
													?><div style="clear:both"></div><?
												}
												$i++;
											}
											if(count($this->multiples)>0){
												foreach($this->multiples as $m){
													$func = "get".ucfirst($m);
													?>
														<div class="champ">
															<label><?=ucfirst($m);?></label>
															<div class="viewChamp">
																<?=$this->displayVal("multiple",$m,$o->$func());?>
															</div>
															<div class="editChamp">
																<?=$this->generateEditField("multiple",$m,$o->$func());?>
															</div>
														</div>
													<?
												}
											}
											?>
											</form>
											<div style="clear:both"></div>
											</div>
										</div>
									</div>
									<!--[if !IE]>end modules<![endif]-->
									
									
										
									
									
									
								</div>
							<!--[if !IE]>end product content<![endif]-->
						</div>
						<!--[if !IE]>end product page<![endif]-->
						
						
						</div>
						
						<span class="section_content_bottom"></span>
					</div>
					<!--[if !IE]>end section content<![endif]-->
				</div>
				<!--[if !IE]>end section<![endif]-->
				<script>
					$(".edit_module").click(function(){
						$(".viewChamp").hide();
						$(".editChamp").show();
						$(".editSaveLink").show();
						$(".editModifLink").hide();
					});
					
					<?if($o->get('id')==0){?>
						$(".viewChamp").hide();
						$(".editChamp").show();
						$(".editSaveLink").show();
						$(".editModifLink").hide();
					<?}?>
					
					$(".edit_cancel").click(function(){
						<?if($o->get('id')!=0){?>
							$(".editChamp").hide();
							$(".viewChamp").show();
							$(".editModifLink").show();
							$(".editSaveLink").hide();
						<?} else {?>
							reload();
						<?}?>
					});
					
					$(".edit_save").click(function(){
						$("#saveForm").validate({
							<?
							if(count($this->contraintes)>0){
								echo "rules: {\n\t\t\t\t\t\t\t\t";
									$isFirst = true;
									foreach($this->contraintes as $id => $arr){
										if(!$isFirst){
											echo ",";
										} else {
											$isFirst = false;
										}
										echo $id.": {";
											$isF = true;
											foreach($arr as $val){
												if(!$isF){
													echo ",";
												} else {
													$isF = false;
												}
												echo $val[0].":".$val[1];
											}
										echo "}\n";
									}
								echo "},\n";
							}
							?>
							submitHandler: function(form) {
								form.submit();
							}
						});
						$("#saveForm").submit();
					});
					
					
					var objet_autocomplete = "";
					$(".autocomplete").autocomplete({
						search: function(){
							objet_autocomplete = $(this).attr('rel');
						},
						source: function(request,response){
							$.getJSON("index.php?page=ajax", {
								term: request.term,
								action: 'autocomplete',
								objet: objet_autocomplete
							}, response);
						},
						select: function(event,ui){
							$("#"+objet_autocomplete).val(ui.item.id);
						},
						minLength: 2
					});
					
					$(".date").datepicker();
					$("select[multiple]").asmSelect({removeLabel:'Retirer',highlightAddedLabel:'Ajout de ',highlightRemovedLabel:'Suppression de ',highlight:true});
				</script>
			<?
		}
		
		protected function generateFilterField($val){
			$orm = $this->instance->get('orm');
			$type = $orm[$val['champ']];
			switch($type){
				case 'object':
					return $this->filterObject($val);
				break;
				case 'tinyint':
					return $this->filterTinyint($val);
				break;
				default:
					return $this->filterVarchar($val);
				break;
			}
		}
		
		protected function filterVarchar($val){
			return "<input type='text' name='".$val['champ']."' value='".$_REQUEST[$val['champ']]."' class='search_text filtre_field' /></span>";
		}
		
		protected function filterTinyint($val){
			$ret = "<select name='".$val['champ']."'>";
			$ret .= "<option value=''>---</option>";
			if($_REQUEST[$val['champ']]=="0"){
				$ret .= "<option value='0' selected>Non</option>";
			} else {
				$ret .= "<option value='0'>Non</option>";
			}
			if($_REQUEST[$val['champ']]==1){
				$ret .= "<option value='1' selected>Oui</option>";
			} else {
				$ret .= "<option value='1'>Oui</option>";
			}
			$ret .= "</select>";
			return $ret;
		}
		
		protected function filterObject($val){
			$typeObj = $val['champ'];
			$obj = new $typeObj();
			$ret = "salut";
			$q = $this->pdo->query("select count(id) as cnt from ".$obj->get('table'))->fetch();
			if($q['cnt']>30){ // si plus de 30 resultat dans la drop down on fais un autocomplete car sinon c trop lourd pour la bdd et pour le user
				if(!empty($_REQUEST[$typeObj])){
					$tmpO = new $typeObj($_REQUEST[$typeObj]);
					$ret = "<input type=\"hidden\" name=\"$typeObj\" id=\"$typeObj\" value='".$tmpO->get('id')."' />";
					$ret .= "<input type=\"text\" class=\"autocomplete search_text\" rel=\"".$typeObj."\" value=\"".$tmpO."\" />";
				} else {
					$ret = "<input type=\"hidden\" name=\"$typeObj\" id=\"$typeObj\" />";
					$ret .= "<input type=\"text\" class=\"autocomplete search_text\" rel=\"".$typeObj."\" />";
				}
			} else {
				$q = $this->pdo->query("select id from ".$obj->get('table'));
				$ret =  "<select name=\"$typeObj\"><option value=''>---</option>";
				
					foreach($q as $row){
						$o = new $typeObj($row['id']);
						if($_REQUEST[$typeObj]==$o->get('id')){
							$selected = "selected";
						} else {
							$selected = "";
						}
						$ret .= "<option value=\"".$o->get('id')."\" $selected>".$o."</option>";
					}
				$ret .= "</select>";
			}
			return $ret;
		}
		
		protected function displayFiltre(){
			?>
			<tr>
				<?
				foreach($this->fields as $id => $val){
					?>
						<td>
							<?
							echo $this->generateFilterField($val);
							?>
						</td>
					<?
				}
				?>
				<td style="width: 96px;"><a href="javascript:void(0)" class="filtre_launch">Filtrer</a></td>
			</tr>
			<?
		}
		
		protected function displayListing(){
			$q = $this->pdo->query($this->generateListingQuery());
			?>
		
			<!--[if !IE]>start section<![endif]-->
				<div class="section">
					
					<!--[if !IE]>start title wrapper<![endif]-->
					<div class="title_wrapper">
						<span class="title_wrapper_top"></span>
						<div class="title_wrapper_inner">
							<span class="title_wrapper_middle"></span>
							<div class="title_wrapper_content">
								<h2><?=$this->titreListing;?></h2>
								<?if($this->canAdd){?>
								<ul class="section_menu section_nav right">
									<li><a href="javascript:void(0)" onclick="create()" class="section_add"><span class="l"><span></span></span><span class="m"><em><?=$this->titreNew;?></em><span></span></span><span class="r"><span></span></span></a></li>
								</ul>
								<?}?>
							</div>
						</div>
						<span class="title_wrapper_bottom"></span>
					</div>
					<!--[if !IE]>end title wrapper<![endif]-->
					
					<!--[if !IE]>start section content<![endif]-->
					<div class="section_content">
						<span class="section_content_top"></span>
						
						<div class="section_content_inner">
							<!--[if !IE]>start table_wrapper<![endif]-->
							<div class="table_wrapper">
								<div class="table_wrapper_inner">
								<form id="formFiltre">
									<table cellpadding="0" cellspacing="0" width="100%">
										<tbody>
										<tr>
											<?
											foreach($this->fields as $id => $val){
												?>
													<th class="sortable <?if($_REQUEST['order']==$val['champ']){echo "current "; if($_REQUEST['order_sens']=="asc"){echo "sortDown";} else {echo "sortUp";}}?>" rel="<?=$val['champ'];?>">
														<?=$val['designation'];?>
													</th>
												<?
											}
											?>
											<th style="width: 96px;">Actions</th>
										</tr>
										<?=$this->displayFiltre();?>
										<?
										if($q->rowCount()>0){
											
											$totalPage = ceil($q->rowCount()/$this->parPage);
											$count = $q->rowCount();
											
											if($totalPage>1){
												// si ya pas plus de 1 page on fait rien
												if(isset($_REQUEST['pg'])&&is_numeric($_REQUEST['pg'])){
													$currentPage = $_REQUEST['pg'];
												} else {
													$currentPage = 1;
												}
												
												$limit = " limit ".($currentPage-1)*$this->parPage.",".$this->parPage;
												
												$q = $this->pdo->query($this->generateListingQuery().$limit);
												
											}
											
											foreach($q as $row){
												echo "<tr class='first'>";
												$o = new $this->class($row['id']);
												$orm = $this->instance->get('orm');
												foreach($this->fields as $id => $val){
													?>
														<td>
															<?
															if($orm[$val['champ']]=="tinyint"){
																if($o->get($val['champ'])==1){
																	echo '<img src="css/layout/approved.gif" />';
																} else {
																	echo '<img src="css/layout/action4.gif" />';
																}
															} else {
																echo $o->get($val['champ']);
															}
															?>
														</td>
													<?
												}
												?>
												<td>
												<div class="actions">
													<ul>
														<?if($this->canUpdate){?><li><a class="action1" href="javascript:void(0)" onclick="show(<?=$o->get('id');?>)" title="Modifier">1</a></li><?}?>
														<?if($this->canDelete){?><li><a class="action4" href="javascript:void(0)" onclick="del(<?=$o->get('id');?>)" title="Supprimer">4</a></li><?}?>
													</ul>
												</div>
												</td>
												</tr>
												<?
											}
										} else {
											?>
												<tr class="first">
													<td align=center style="text-align:center" colspan="<?=count($this->fields)+1;?>"><?=$this->listingVide;?></td>
												</tr>
											<?
										}
										?>
										</tbody>
									</table>
								</form>
								</div>
							</div>
							<!--[if !IE]>end table_wrapper<![endif]-->
						</div>
						
						<?
						if($totalPage>1){
						?>
						<!--[if !IE]>start pagination<![endif]-->
							<div class="pagination_wrapper">
							<span class="pagination_top"></span>
							<div class="pagination_middle">
							<div class="pagination">
								<span class="page_no">Page <?=$currentPage;?> sur <?=$totalPage;?> - <?=$count;?> résultats</span>
								
								<ul class="pag_list">
									<?if($currentPage>1){?>
									<li><a href="javascript:void(0)" onclick="goToPage(<?=$currentPage-1;?>)" class="pag_nav"><span><span>Précédent</span></span></a> </li>
									<?}?>
									
									<?
									if(($currentPage-$this->adjacent)<=2){
										for($i=1;$i<$currentPage;$i++){
											?>
											<li><a href="javascript:void(0)" onclick="goToPage(<?=$i;?>)"><?=$i;?></a></li>
											<?
										}
									} else {
										?>
										<li><a href="javascript:void(0)" onclick="goToPage(1)">1</a></li>
										<li>[...]</li>
										<?
										for($i=($currentPage-$this->adjacent);$i<$currentPage;$i++){
											?>
											<li><a href="javascript:void(0)" onclick="goToPage(<?=$i;?>)"><?=$i;?></a></li>
											<?
										}
									}
									?>
										<li><a href="javascript:void(0)" class="current_page"><span><span><?=$currentPage;?></span></span></a></li>
									<?
									if($currentPage+$this->adjacent>=$totalPage){
										for($i=$currentPage+1;$i<=$totalPage;$i++){
											?>
											<li><a href="javascript:void(0)" onclick="goToPage(<?=$i;?>)"><?=$i;?></a></li>
											<?
										}
									} else {
										for($i=$currentPage+1;$i<=$currentPage+$this->adjacent;$i++){
											?>
											<li><a href="javascript:void(0)" onclick="goToPage(<?=$i;?>)"><?=$i;?></a></li>
											<?
										}
										?>
											<li>[...]</li>
											<li><a href="javascript:void(0)" onclick="goToPage(<?=$totalPage;?>)"><?=$totalPage;?></a></li>
										<?
									}
									?>
									
									<?if($currentPage<$totalPage){?>
									<li><a href="javascript:void(0)" onclick="goToPage(<?=$currentPage+1;?>)" class="pag_nav"><span><span>Suivant</span></span></a></li>
									<? } ?>
								</ul>
							</div>
							</div>
							<span class="pagination_bottom"></span>
							</div>

						<!--[if !IE]>end pagination<![endif]-->
						<? } ?>

						
						<span class="section_content_bottom"></span>
					</div>
					<!--[if !IE]>end section content<![endif]-->
				</div>
				<script>
					$(document).ready(function(){
						$(".filtre_launch").click(function(){
							reload();
						});
						$(".sortable").click(function(){
							if($(this).hasClass("current")){
								if($(this).hasClass("sortUp")){
									$("#order_sens").val("asc");
									$(this).addClass("sortDown");
									$(this).removeClass("sortUp");
								} else {
									$("#order_sens").val("desc");
									$(this).removeClass("sortDown");
									$(this).addClass("sortUp");
								}
							} else {
								$("#order").val($(this).attr('rel'));
								$("#order_sens").val("desc");
								/* on commence en desc */
								$(".current").removeClass("sortUp");
								$(".current").removeClass("sortDown");
								$(".current").removeClass("current");
								$(this).addClass("sortUp");
								$(this).addClass("current");
							}
							reload();
						});
						var objet_autocomplete = "";
						$(".autocomplete").autocomplete({
							search: function(){
								objet_autocomplete = $(this).attr('rel');
							},
							source: function(request,response){
								$.getJSON("index.php?page=ajax", {
									term: request.term,
									action: 'autocomplete',
									objet: objet_autocomplete
								}, response);
							},
							select: function(event,ui){
								$("#"+objet_autocomplete).val(ui.item.id);
							},
							minLength: 2
						});
					});
				</script>
		<?
		}
		
		protected function save($id){
			$o = new $this->class($id);
			if($o->get('id')==0){
				$o->save();
			}
			foreach($_POST as $id => $val){
				$orm = $o->get('orm');
				$orm = $orm['fields'];
				if($orm[$id]=="object"){ // si c'est un objet fo set un objet et pas juste un id
					$o->set($id,new $id($val));
				} elseif($orm[$id]=="password"){ // si c'est un mdp on le save apres l'avoir converti en sha512
					if($val!=""){
						$o->set($id,hash("sha512",$val));
					}
				} elseif(preg_match("@multiple_@",$id)){
					$func = "save".ucfirst(strtolower(preg_replace("@multiple_@","",$id)));
					$o->$func($val);
				} else {
					$o->set($id,$val);
				}
			}
			$o->save();
			if(count($_FILES)>0){
				foreach($_FILES as $id => $val){
					$func = "upload".$id;
					if(!$o->$func()){
						error_log("could not upload $id");
					}
				}
			}
			?>
				<script>
					window.parent.show(<?=$o->get('id');?>);
					window.parent.msg("green","<?=$this->validSave;?>");
				</script>
			<?
		}
	
	}
?>
