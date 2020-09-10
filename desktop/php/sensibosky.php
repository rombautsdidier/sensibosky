<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('sensibosky');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
   <div class="col-xs-12 eqLogicThumbnailDisplay">
  <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
  <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
      <i class="fas fa-wrench"></i>
    <br>
    <span>{{Configuration}}</span>
  </div>
  </div>
  <legend><i class="fas fa-table"></i> {{Mes Sensibo Sky}}</legend>
	   <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
<div class="eqLogicThumbnailContainer">
    <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
	echo '<br>';
	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
    <form class="form-horizontal">
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
                <div class="col-sm-3">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement template}}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                <div class="col-sm-3">
                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                        <option value="">{{Aucun}}</option>
                        <?php
foreach (jeeObject::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
                   </select>
               </div>
           </div>
	   <div class="form-group">
                <label class="col-sm-3 control-label">{{Catégorie}}</label>
                <div class="col-sm-9">
                 <?php
                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                    }
                  ?>
               </div>
           </div>
	<div class="form-group">
		<label class="col-sm-3 control-label"></label>
		<div class="col-sm-9">
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
		</div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{{Forçage du mode}}</label>
        <div class="col-sm-3">
            <select class="form-control eqLogicAttr configuration" data-l1key="configuration" data-l2key="forceMode">
                <option value="none">{{Aucun}}</option>
                <option value="1">{{1 degré}}</option>
                <option value="2">{{2 degrés}}</option>
                <option value="3">{{3 degrés}}</option>
            </select>
        </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{ID du pod}}</label>
      <div class="col-sm-9">
        <span class="eqLogicAttr" data-l1key="configuration" data-l2key="podid"/></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Localisation}}</label>
      <div class="col-sm-9">
        <span class="eqLogicAttr" data-l1key="configuration" data-l2key="location"/></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Unité de températures}}</label>
      <div class="col-sm-9">
        <span class="eqLogicAttr" data-l1key="configuration" data-l2key="tempUnit"/></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Capacités Cool}}</label>
      <div class="col-sm-9">
        <span class="eqLogicAttr" data-l1key="configuration" data-l2key="capabilitiescool"/></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Capacités Dry}}</label>
      <div class="col-sm-9">
        <span class="eqLogicAttr" data-l1key="configuration" data-l2key="capabilitiesdry"/></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Capacités Fan}}</label>
      <div class="col-sm-9">
        <span class="eqLogicAttr" data-l1key="configuration" data-l2key="capabilitiesfan"/></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Capacités Heat}}</label>
      <div class="col-sm-9">
        <span class="eqLogicAttr" data-l1key="configuration" data-l2key="capabilitiesheat"/></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Capacités Auto}}</label>
      <div class="col-sm-9">
        <span class="eqLogicAttr" data-l1key="configuration" data-l2key="capabilitiesauto"/></span>
      </div>
    </div>
</fieldset>
</form>
</div>
      <div role="tabpanel" class="tab-pane" id="commandtab">
<br/><br/>
<table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width: 50px;"> ID</th>
              <th style="width: 230px;">{{Nom}}</th>
              <th style="width: 110px;">{{Sous-Type}}</th>
              <th>{{Valeur}}</th>
              <th>{{Paramètres}}</th>
              <th style="width: 300px;">{{Options}}</th>
              <th style="width: 150px;"></th>
            </tr>
          </thead>
          <tbody>
            
          </tbody>
        </table>
</div>
</div>

</div>
</div>

<?php include_file('desktop', 'sensibosky', 'js', 'sensibosky');?>
<?php include_file('core', 'plugin.template', 'js');?>
