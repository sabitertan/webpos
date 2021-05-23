<?php
class ModelExtensionModuleWebposBuilder extends Model {
	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "webposbank` (
				`bank_id` INT(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(64) NOT NULL,
				`image` varchar(64) NOT NULL,
				`method` varchar(64) NOT NULL,
				`model` varchar(64) NOT NULL,
				`short` varchar(64) NOT NULL,
				`status` tinyint(1) NOT NULL,
				PRIMARY KEY (`bank_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;"
		);
	}

	public function update() {
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "webposbank`;");
	}
	public function addbank($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "webposbank SET name = '" . $this->db->escape($data['name']) . "',image = '" . $this->db->escape($data['image']) . "', method = '" . $this->db->escape($data['method']) . "', model = '" . $this->db->escape($data['model']) . "', short = '" . $this->db->escape($data['short']) . "', status = '" . (int)$data['status'] . "'");

		$bank_id = $this->db->getLastId();

	}

	public function editbank($bank_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "webposbank SET name = '" . $this->db->escape($data['name']) . "',image = '" . $this->db->escape($data['image']) . "', method = '" . $this->db->escape($data['method']) . "', model = '" . $this->db->escape($data['model']) . "', short = '" . $this->db->escape($data['short']) . "', status = '" . (int)$data['status'] . "' WHERE bank_id = '" . (int)$bank_id . "'");

	}

	public function deletebank($bank_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "webposbank WHERE bank_id = '" . (int)$bank_id . "'");
	}

	public function getbank($bank_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "webposbank WHERE bank_id = '" . (int)$bank_id . "'");

		return $query->row;
	}

	public function checkShortName($short) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "webposbank WHERE short = '" . $this->db->escape($short) . "'");
		$bank=array();
		$bank = $query->row;
		if (!isset($this->request->request['bank_id'])) {
		return $bank;
		} else {
		if ($bank['bank_id']==$this->request->request['bank_id']) {
		return null;
		} else {
		return $bank;
		}
		}
	}

	public function getbanks($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "webposbank";

		$sort_data = array(
			'name',
			'bank_id',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data) && $data['sort']!='status') {
			$sql .= " ORDER BY " . $data['sort'];
		} else if(isset($data['sort']) && ($data['sort']=='status')) {
			$sql .= " WHERE status=1 ORDER BY name";
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}


	public function getTotalbanks() {

      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "webposbank;");

		return $query->row['total'];
	}

}