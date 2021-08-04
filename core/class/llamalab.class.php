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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class llamalab extends eqLogic {
	public function loadCmdFromConf($type) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
			return;
		}
		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $liste_cmd) {
				if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
				|| (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
					$cmd = $liste_cmd;
					break;
				}
			}
			if ($cmd == null || !is_object($cmd)) {
				$cmd = new llamalabCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
			}
		}
	}

	public function postSave() {
		$this->loadCmdFromConf('llamalab');
	}
}

class llamalabCmd extends cmd {
	public function execute($_options = null) {
			if ($this->getType() == 'action') {
				$eqLogic = $this->getEqLogic();
				if ($this->getLogicalId() == 'refresh') {
					$eqLogic->refresh();
					return;
				}
				$put = array();
				if ($this->getSubType() == 'slider') {
					$put[$this->getConfiguration('argument')] = $_options['slider'];
				} else if ($this->getSubType() == 'select') {
					$put[$this->getConfiguration('argument')] = $_options['select'];
				} else if ($this->getSubType() == 'message') {
					$put[$this->getConfiguration('argument')] = $_options['title'];
					if ($this->getConfiguration('argument') == 'command') {
						$put['arguments'][] = $_options['message'];
					} else {
						if (strpos('icone=',$_options['message']) === false) {
							$put['message'][] = $_options['message'];
						} else {
							$parts = explode(';', str_replace('icone=','',$_options['message']));
							$put['message'][] = $parts[1];
							$put['icon'][] = $parts[0];
						}
					}
				} else {
					$put[$this->getConfiguration('argument')] = $this->getConfiguration('value');
				}
				if (strpos('audio',$this->getConfiguration('request')) === false) {
					$method = 'post';
				} else {
					$method = 'put';
				}
				$eqLogic->callOpenData($this->getConfiguration('request'),$put,$method);
			}
		}
}
?>
