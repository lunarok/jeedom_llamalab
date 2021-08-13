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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

http_response_code(200);
header("Content-Type: application/json");

if (!jeedom::apiAccess(init('apikey'), 'llamalab')) {
 echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (llamalab)', __FILE__);
 die();
}

$eqLogic = eqLogic::byId(init('id'));
if (!is_object($eqLogic)) {
  return true;
}

$data = json_decode(file_get_contents('php://input'), true);
$eqLogic->updateInfos($data);

$cmd = cmd::byEqLogicIdAndLogicalId(init('id'),'todo');
$value = $cmd->execCmd();
if ($value != '') {
  $eqLogic->checkAndUpdateCmd('todo', '');
}

if (class_exists('geotrav')) {
 $geolocCmd = geotravCmd::byEqLogicIdAndLogicalId($eqLogic->getConfiguration('geoloc', ''),'location:updateCoo');
 $option = array('message' => $data["latitude"] . ',' . $data["longitude"]);
 if (is_object($geolocCmd)) {
  $geolocCmd->execute($option);
 }
}
//echo json_encode($value);
echo $value;

die();
?>
