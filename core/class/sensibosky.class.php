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

    public function callSensiboAPI() {
      $apikey = trim(config::byKey('sensibo-apikey', 'sensibosky'));
      if ($apikey == '') {
        log::add('sensibosky', 'error', 'Configuration à saisir');
        die;
      }
      $uri = 'https://home.sensibo.com/api/v2/users/me/pods?fields=*&apiKey=' . $apikey;

      log::add('sensibosky', 'debug', 'Appel : ' . $uri);
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$uri);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $json_string = curl_exec ($ch);
      curl_close ($ch);
      return $json_string;
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
        log::add('sensibosky', 'debug', 'status : ' . $value['acState']['on'],true);
        log::add('sensibosky', 'debug', 'fanLevel : ' . $value['acState']['fanLevel'],true);
        log::add('sensibosky', 'debug', 'targetTemp : ' . $value['acState']['targetTemperature'].' '.$value['acState']['temperatureUnit'],true);
        log::add('sensibosky', 'debug', 'mode : ' . $value['acState']['mode'],true);
        log::add('sensibosky', 'debug', 'swing : ' . $value['acState']['swing'],true);
        log::add('sensibosky', 'debug', 'MeasuredTemp : ' . $value['measurements']['temperature'],true);
        log::add('sensibosky', 'debug', 'MeasuredHumid : ' . $value['measurements']['humidity'],true);
        log::add('sensibosky', 'debug', 'MeasuredTime : ' . $value['measurements']['time']['time'],true);
        log::add('sensibosky', 'debug', 'RSSI : ' . $value['measurements']['rssi'],true);
        log::add('sensibosky', 'debug', 'Room : ' . $value['location']['name'],true);

        sensibosky::setPod($key+1,$value['id'],$value['connectionStatus']['isAlive'],$value['measurements']['rssi'],$value['measurements']['temperature'],$value['measurements']['humidity'],$value['location']['name']);
      }
    }

    public function setPod($id,$podid,$isAlive,$rssi,$temperature,$humidity,$location) {
      $sensibosky = self::byLogicalId('pod' . $id, 'sensibosky');
      if (!is_object($sensibosky)) {
        $sensibosky = new sensibosky();
        $sensibosky->setEqType_name('sensibosky');
        $sensibosky->setLogicalId('pod' . $id);
        $sensibosky->setName(__('pod' . $id, __FILE__));
        $sensibosky->setIsEnable(true);
      }
      $sensibosky->setConfiguration('podid',$podid);
      $sensibosky->setConfiguration('location',$location);
      $sensibosky->save();

      // Création de la commande état de connexion
      $cmd = sensiboskyCmd::byEqLogicIdAndLogicalId($sensibosky->getId(),'isAlive');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('isAlive');
        $cmd->setIsVisible(1);
        $cmd->setName(__('Connecté', __FILE__));
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
      $cmd = sensiboskyCmd::byEqLogicIdAndLogicalId($sensibosky->getId(),'rssi');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('rssi');
        $cmd->setIsVisible(1);
        $cmd->setName(__('rssi', __FILE__));
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

      // Création de la commande température
      $cmd = sensiboskyCmd::byEqLogicIdAndLogicalId($sensibosky->getId(),'temperature');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('temperature');
        $cmd->setIsVisible(1);
        $cmd->setName(__('Température', __FILE__));
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
      $cmd = sensiboskyCmd::byEqLogicIdAndLogicalId($sensibosky->getId(),'humidity');
      if (!is_object($cmd)) {
        $cmd = new sensiboskyCmd();
        $cmd->setLogicalId('humidity');
        $cmd->setIsVisible(1);
        $cmd->setName(__('Humidité', __FILE__));
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
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

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
        
    }

    /*     * **********************Getteur Setteur*************************** */
}


