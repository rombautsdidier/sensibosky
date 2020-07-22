<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class sensibosky extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    
    public static function cron() {
       sensibosky::getAPIStatus();
    }
    


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */

    public function callSensiboAPI($cmd="",$device_id="") {
      $apikey = trim(config::byKey('sensibo-apikey', 'sensibosky'));
      if ($apikey == '') {
        log::add('sensibosky', 'error', 'Configuration à saisir');
        die;
      }

      if ($cmd=="") {
        $uri = 'https://home.sensibo.com/api/v2/users/me/pods?fields=*&apiKey=' . $apikey;

        log::add('sensibosky', 'debug', 'Appel : ' . $uri);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_string = curl_exec ($ch);
        curl_close ($ch);
        return $json_string;
      } else {
        // 
        // API URL
        $uri = 'https://home.sensibo.com/api/v2/pods/' .$device_id. '/acStates?apiKey=' . $apikey;
        log::add('sensibosky', 'debug', 'uri: '.$uri);
        log::add('sensibosky', 'debug', 'cmd: '.$cmd);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $cmd);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        log::add('sensibosky', 'debug', 'result: '.$result);
        sensibosky::getAPIStatus();
      }
    }

    public function getAPIStatus() {
      $json_string = sensibosky::callSensiboAPI();
      if ($json_string == '') {
        log::add('sensibosky', 'debug', 'Réponse vide');
        return;
      }
      $parsed_json = json_decode($json_string, true);
      $status = $parsed_json['status'];
      log::add('sensibosky', 'debug', 'Retour : ' . $status);

      $items = $parsed_json['result'];
      foreach ($items as $key => $value) {
        log::add('sensibosky', 'debug', '-------------------------------------------------------------------',true);
        log::add('sensibosky', 'debug', 'Information for Pod id : ' . $value['id'],true);
        log::add('sensibosky', 'debug', '-------------------------------------------------------------------',true);
        log::add('sensibosky', 'debug', 'isAlive : ' . $value['connectionStatus']['isAlive'],true);
        log::add('sensibosky', 'debug', 'lastSeen : ' . $value['connectionStatus']['lastSeen']['time'],true);
        log::add('sensibosky', 'debug', 'on : ' . $value['acState']['on'],true);
        log::add('sensibosky', 'debug', 'fanLevel : ' . $value['acState']['fanLevel'],true);
        log::add('sensibosky', 'debug', 'targetTemperature : ' . $value['acState']['targetTemperature'].' '.$value['acState']['temperatureUnit'],true);
        log::add('sensibosky', 'debug', 'mode : ' . $value['acState']['mode'],true);
        log::add('sensibosky', 'debug', 'swing : ' . $value['acState']['swing'],true);
        log::add('sensibosky', 'debug', 'MeasuredTemp : ' . $value['measurements']['temperature'],true);
        log::add('sensibosky', 'debug', 'MeasuredHumid : ' . $value['measurements']['humidity'],true);
        log::add('sensibosky', 'debug', 'MeasuredTime : ' . $value['measurements']['time']['time'],true);
        log::add('sensibosky', 'debug', 'RSSI : ' . $value['measurements']['rssi'],true);
        log::add('sensibosky', 'debug', 'Room : ' . $value['location']['name'],true);

        sensibosky::setPod($key+1,$value['id'],$value['connectionStatus']['isAlive'],$value['measurements']['rssi'],$value['measurements']['temperature'],$value['measurements']['humidity'],$value['location']['name'],$value['acState']['on'],$value['acState']['fanLevel'],$value['acState']['mode'],$value['acState']['swing'],$value['acState']['targetTemperature'],$value['acState']['temperatureUnit'],$value['remoteCapabilities']['modes']);
      }
    }

    public function setPod($id,$podid,$isAlive,$rssi,$temperature,$humidity,$location,$state,$fanLevel,$acMode,$acSwing,$targetTemp,$tempUnit,$capabilities) {
      $sensibosky = self::byLogicalId('pod' . $id, 'sensibosky');
      if (!is_object($sensibosky)) {
        $sensibosky = new sensibosky();
        $sensibosky->setEqType_name('sensibosky');
        $sensibosky->setLogicalId('pod' . $id);
        $sensibosky->setName(__('pod' . $id, __FILE__));
        $sensibosky->setIsEnable(true);
        $sensibosky->setConfiguration('podid',$podid);
        $sensibosky->setConfiguration('location',$location);
        $sensibosky->setConfiguration('tempUnit',$tempUnit);
      }

      $sensibosky->save();

      //Commande refresh
      $cmd = $sensibosky->getCmd(null,'refresh');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('refresh');
        $cmd->setIsVisible(1);
        $cmd->setName(__('Refresh', __FILE__));
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();

      // Création de la commande état de connexion
      $cmd = $sensibosky->getCmd(null,'isAlive');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('isAlive');
        $cmd->setIsVisible(1);
        $cmd->setName(__('IsAlive', __FILE__));
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('info');
      $cmd->setSubType('binary');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('isAlive', $isAlive);
      $cmdId = $cmd->getId();

      // Création de la commande rssi
      $cmd = $sensibosky->getCmd(null,'rssi');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('rssi');
        $cmd->setIsVisible(1);
        $cmd->setName(__('RSSI', __FILE__));
        $cmd->setConfiguration('minValue','-100');
        $cmd->setConfiguration('maxValue','0');
        $cmd->setUnite('dBm');
        $cmd->setTemplate('dashboard','core::tile');
        $cmd->setTemplate('mobile','core::tile');
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('rssi', $rssi);
      $cmdId = $cmd->getId();

      // Création de la commande status
      $cmd = $sensibosky->getCmd(null,'on');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('on');
        $cmd->setIsVisible(1);
        $cmd->setName(__('State', __FILE__));
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }

      if ($state=="") { $state=0; } else { $state=1; }

      $cmd->setType('info');
      $cmd->setSubType('binary');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('on', $state);
      $cmdId = $cmd->getId();

      // Création de la commande température
      $cmd = $sensibosky->getCmd(null,'temperature');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('temperature');
        $cmd->setIsVisible(1);
        $cmd->setName(__('Temperature', __FILE__));

        switch($tempUnit){
          case "C": $minTemp='16';  $maxTemp='30';  break;
          case "F": $minTemp='61';  $maxTemp='86';  break;

        }

        $cmd->setConfiguration('minValue',$minTemp);
        $cmd->setConfiguration('maxValue',$maxTemp);
        $cmd->setUnite('°'.$tempUnit);
        $cmd->setTemplate('dashboard','core::tile');
        $cmd->setTemplate('mobile','core::tile');
        $cmd->setGeneric_type('TEMPERATURE');
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('temperature', $temperature);
      $cmdId = $cmd->getId();

      // Création de la commande humidité
      $cmd = $sensibosky->getCmd(null,'humidity');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('humidity');
        $cmd->setIsVisible(1);
        $cmd->setUnite('%');
        $cmd->setConfiguration('minValue','0');
        $cmd->setConfiguration('maxValue','100');
        $cmd->setTemplate('dashboard','core::tile');
        $cmd->setTemplate('mobile','core::tile');
        $cmd->setGeneric_type('HUMIDITY');
        $cmd->setName(__('Humidity', __FILE__));
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('humidity', $humidity);
      $cmdId = $cmd->getId();

      // Création de la commande fanLevel
      $cmd = $sensibosky->getCmd(null,'fanLevel');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('fanLevel');
        $cmd->setIsVisible(1);
        $cmd->setName(__('Fan level', __FILE__));
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('info');
      $cmd->setSubType('string');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('fanLevel', $fanLevel);
      $cmdId = $cmd->getId();

      // Création de la commande mode etat
      $cmd = $sensibosky->getCmd(null,'mode');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('mode');
        $cmd->setIsVisible(1);
        $cmd->setName(__('Mode', __FILE__));
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('info');
      $cmd->setSubType('string');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('mode', $acMode);
      $cmdId = $cmd->getId();

      // Création de la commande acSwing etat
      $cmd = $sensibosky->getCmd(null,'swing');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('swing');
        $cmd->setIsVisible(1);
        $cmd->setName(__('Swing', __FILE__));
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('info');
      $cmd->setSubType('string');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('swing', $acSwing);
      $cmdId = $cmd->getId();

      // Création de la commande pour donner l'info de la consigne
      $cmd = $sensibosky->getCmd(null,'targetTemperature');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('targetTemperature');
        $cmd->setIsVisible(1);
        $cmd->setName(__('targetTemperature', __FILE__));

        switch($tempUnit){
          case "C": $minTemp='16';  $maxTemp='30';  break;
          case "F": $minTemp='61';  $maxTemp='86';  break;

        }

        $cmd->setConfiguration('minValue',$minTemp);
        $cmd->setConfiguration('maxValue',$maxTemp);
        $cmd->setTemplate('dashboard','core::tile');
        $cmd->setTemplate('mobile','core::tile');
        $cmd->setGeneric_type('TEMPERATURE');
        $cmd->setUnite('°'.$tempUnit);
        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setEqLogic_id($sensibosky->getId());
      $cmd->save();
      $sensibosky->checkAndUpdateCmd('targetTemperature', $targetTemp);

      // Création de la commande pour l'action consigne
      $cmd = $sensibosky->getCmd(null,'setTemperature');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('setTemperature');
        $cmd->setIsVisible(1);
        $cmd->setName(__('setTemperature', __FILE__));

        switch($tempUnit){
          case "C": $minTemp='16';  $maxTemp='30';  break;
          case "F": $minTemp='61';  $maxTemp='86';  break;
        }

        $cmd->setConfiguration('minValue',$minTemp);
        $cmd->setConfiguration('maxValue',$maxTemp);
        $cmd->setUnite('°'.$tempUnit);
        $cmd->setConfiguration('value', "#slider#");
        $cmd->setGeneric_type('THERMOSTAT_SET_SETPOINT');
        $cmd->setTemplate('dashboard','core::button');
        $cmd->setTemplate('mobile','core::button');

        $cmds = $sensibosky->getCmd();
        $order = count($cmds);
        $cmd->setOrder($order);
      }
      $cmd->setType('action');
      $cmd->setSubType('slider');
      $cmd->setEqLogic_id($sensibosky->getId());

      // Liaison de la commande info à la commande action
      $infoCmd = $sensibosky->getCmd(null, 'targetTemperature');
      $cmd->setValue($infoCmd ->getId());      
      $cmd->save();    

     // Création des commandes action pour activer/eteindre le réversible
      $onActions=array('on' => 'True','off' => 'False');
      foreach($onActions as $key => $value) {
        $cmd = $sensibosky->getCmd(null,'setOnTo'.$value);
        if (!is_object($cmd)) {
          $cmd = new sensiboskyCmd();
          $cmd->setLogicalId('setOnTo'.$value);
          $cmd->setIsVisible(1);
          $cmd->setName(__($key, __FILE__));
          switch ($value) {
            case "True":
              $cmd->setConfiguration('param','on=1');
              break;
            case "False":
              $cmd->setConfiguration('param','on=0');
              break;
          }
          $cmds = $sensibosky->getCmd();
          $order = count($cmds);
          $cmd->setOrder($order);
        }
        $cmd->setType('action');
        $cmd->setSubType('other');
        $cmd->setEqLogic_id($sensibosky->getId());
        $cmd->save();               
      }

      // Apprendre les capacités afin d'en déduire les propriétés 
      foreach ($capabilities as $key1 => $value1) {
        // Mode
        $cmd = $sensibosky->getCmd(null,'setModeTo'.$key1);
        if (!is_object($cmd)) {
          $cmd = new sensiboskyCmd();
          $cmd->setLogicalId('setModeTo'.$key1);
          $cmd->setIsVisible(1);
          $cmd->setName(__('Mode '.$key1, __FILE__));

          $cmd->setConfiguration('param','mode='.$key1);
          $cmds = $sensibosky->getCmd();
          $order = count($cmds);
          $cmd->setOrder($order);
        }
        $cmd->setType('action');
        $cmd->setSubType('other');
        $cmd->setEqLogic_id($sensibosky->getId());
        $cmd->save();

        $capabilities_string='';
        // Rotation et puissance du ventilateur
        foreach ($value1 as $key2 => $value2) {
          if (($key2 == 'fanLevels') || ($key2 == 'swing')) {
            $capabilities_string .= $key2.'=';
            $capabilities_string=str_replace('fanLevels', 'fanLevel', $capabilities_string);
            foreach ($value2 as $key3 => $value3) {
              $cmd = $sensibosky->getCmd(null,'set'.$key2.$value3);
              $capabilities_string .= $value3.',';
              if (!is_object($cmd)) {
                $cmd = new sensiboskyCmd();
                $cmd->setLogicalId('set'.$key2.$value3);
                $cmd->setIsVisible(1);
                $cmd->setName(__($key2.' '.$value3, __FILE__));

                $cmd->setConfiguration('param',$key2.'='.$value3);
                $cmds = $sensibosky->getCmd();
                $order = count($cmds);
                $cmd->setOrder($order);
              }
              $cmd->setType('action');
              $cmd->setSubType('other');
              $cmd->setEqLogic_id($sensibosky->getId());
              $cmd->save();   
            }
            // On retire la dernière virgule
            $capabilities_string = substr($capabilities_string, 0, -1).'|';
          }
        } 

        // On sauvegarde les capacités par mode afin de les réutiliser à l'exécution des commandes.
        $sensibosky->setConfiguration('capabilities'.$key1,$capabilities_string);
        $sensibosky->save();
      }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {


    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }



    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class sensiboskyCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
      switch ($this->getLogicalId()) {
          case 'refresh': 
            sensibosky::getAPIStatus();
            break;
          default:
            $acState = array();

            // Récupérer l'action de la commande exécutée 
            $request = $this->getConfiguration('param');
            $eqLogic = $this->getEqLogic();

            // Récupération du device_id
            $podid=$eqLogic->getConfiguration('podid');
            $tempUnit=$eqLogic->getConfiguration('tempUnit');



            // Boucler sur toutes les commandes info afin d'envoyer les mêmes données si pas de changement via une commande action
            $cmds=$eqLogic->getCmd('info');
            
            foreach ($cmds as $the_cmd) {
              if (($the_cmd->getLogicalId()!='temperature') && ($the_cmd->getLogicalId()!='humidity') && ($the_cmd->getLogicalId()!='rssi') && ($the_cmd->getLogicalId()!='isAlive')) {
                $acState[$the_cmd->getLogicalId()]=$the_cmd->execCmd();
              }

              if ($the_cmd->getLogicalId()=='temperature') {
              	$temp=$the_cmd->execCmd();
              }
            }

            // Forcer à false la commande on si la commande off est exécutée, dans les autres cas, il faut toujours envoyer true pour on 
            if($this->getLogicalId()=='setOnToFalse') {
              $acState['on']=(bool) 0;
            } else {
              $acState['on']=(bool) 1;
            }
            
            // Récupérer la valeur du slider de la consigne
            if($this->getLogicalId()=='setTemperature') {
              if ($this->getSubType() == 'slider') {
                $acState['targetTemperature'] = (int) $_options['slider'];  
                }            
            }

            // Puis, l'unité de température
            $acState['temperatureUnit']=$tempUnit;

            // Enfin, écrasement de la valeur de la commande action si différent de on/off
            if (($request!="") && ($this->getLogicalId()!='setOnToFalse') && ($this->getLogicalId()!='setOnToTrue')) { 
              $exec_cmd=explode('=',$request);
              $acState[$exec_cmd[0]]=$exec_cmd[1];
            }

            // Gestion du cool & heat entre consigne et température de la pièce
            if (($acState['mode']!="auto") && ($acState['mode']!="dry") && ($acState['mode']!="fan")) {
            	// Si t° < à la consigne, forcer le mode à heat si pas auto
            	if ($acState['targetTemperature'] < $temp) {
            		log::add('sensibosky', 'debug', 'Attention, passage en mode cool car la consigne '.$acState['targetTemperature'].' est inférieure à '.$temp,true);
            		$acState['mode']='cool';
            	}
            	// Si t° > à la consigne, forcer le mode à cool si pas auto
            	if ($acState['targetTemperature'] > $temp) {
            		log::add('sensibosky', 'debug', 'Attention, passage en mode heat car la consigne '.$acState['targetTemperature'].' est supérieure à '.$temp,true);
            		$acState['mode']='heat';
            	}
            }

            
            log::add('sensibosky', 'debug', '-------------------------------------------------------------------',true);
            log::add('sensibosky', 'debug', 'Array to send for Pod '.$podid.' before capabilities check',true);
            log::add('sensibosky', 'debug', '-------------------------------------------------------------------',true);
            foreach ($acState as $key => $value) {
              log::add('sensibosky', 'debug', $key.' : ' . $value,true);             
            }

            // Ici, vérification des information fanLevel et swing. Si vide, on prend la première valeur de la liste des capacités en fonction du mode
            $capabilities=str_replace('fanLevels', 'fanLevel', $eqLogic->getConfiguration('capabilities' . $acState['mode']));
            log::add('sensibosky', 'debug', ' Capacités du mode '.$acState['mode']. ': '.$capabilities,true);

            $the_capabilities=explode('|', $capabilities);

            foreach ($the_capabilities as $the_properties) {
              $the_property=explode('=', $the_properties);
              $the_property_name=$the_property[0];
              $the_property_values=$the_property[1];
              $the_property_by_default=explode(',', $the_property_values);

              if ($acState[$the_property_name] =='') $acState[$the_property_name]=$the_property_by_default[0];
            }

            log::add('sensibosky', 'debug', '-------------------------------------------------------------------',true);
            log::add('sensibosky', 'debug', 'Array to send for Pod '.$podid.' after capabilities check',true);
            log::add('sensibosky', 'debug', '-------------------------------------------------------------------',true);
            foreach ($acState as $key => $value) {
              log::add('sensibosky', 'debug', $key.' : ' . $value,true);             
            }

            $cmd = json_encode(array("acState" => $acState));
            sensibosky::callSensiboAPI($cmd,$podid);
            break;
      }  
    }
    /*     * **********************Getteur Setteur*************************** */
}


