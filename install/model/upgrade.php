<?php
class ModelUpgrade extends Model {
	public function mysql($data, $sqlfile) {
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
		
		$connection = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);

		mysql_select_db(DB_DATABASE, $connection);

		mysql_query("SET NAMES 'utf8'", $connection);
		mysql_query("SET CHARACTER SET utf8", $connection);

		$file = DIR_APPLICATION . $sqlfile;

		if (!file_exists($file)) { die('Could not load sql file: ' . $file); }

		if ($sql = file($file)) {
			$query = '';

			foreach($sql as $line) {
				$tsl = trim($line);

				if (($sql != '') && $tsl && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != '#')) {

					// Improved compatibility...
					$line = str_replace("oc_", DB_PREFIX, $line);
					$line = str_replace(" order ", " `order` ", $line);
					$line = str_replace(" ssl ", " `ssl` ", $line);
					$line = str_replace("NOT NULL DEFAULT ''", "NOT NULL", $line);
					$line = str_replace("NOT NULL DEFAULT NULL", "NOT NULL", $line);
					$line = str_replace("NOT NULL DEFAULT 0 COMMENT '' auto_increment", "NOT NULL COMMENT '' auto_increment", $line);


					// Check existing conditions for specific commands
					// For example, ALTER TABLE will error if the table has since been removed,
					// So validate the table exists first, etc.
					if (preg_match('/^ALTER TABLE (.+?) ADD PRIMARY KEY/', $line, $matches)) {
						$res = mysql_query(sprintf("SHOW KEYS FROM %s",$matches[1]), $connection);
						$info = mysql_fetch_assoc(mysql_query(sprintf("SHOW KEYS FROM %s",$matches[1]), $connection));
						if ($info['Key_name'] == 'PRIMARY') { continue; }
					}
					if (preg_match('/^ALTER TABLE (.+?) ADD INDEX (.+?) /', $line, $matches)) {
						$res = mysql_query(sprintf("SHOW KEYS FROM %s",$matches[1]), $connection);
						$info = mysql_fetch_assoc(mysql_query(sprintf("SHOW INDEX FROM %s",$matches[1]), $connection));
						if ($info['Key_name'] == 'PRIMARY') { continue; }
					}
					if (preg_match('/^ALTER TABLE (.+?) ADD PRIMARY KEY/', $line, $matches)) {
						$res = mysql_query(sprintf("SHOW KEYS FROM %s",$matches[1]), $connection);
						$info = mysql_fetch_assoc(mysql_query(sprintf("SHOW KEYS FROM %s",$matches[1]), $connection));
						if ($info['Key_name'] == 'PRIMARY') { continue; }
					}
					if (preg_match('/^ALTER TABLE (.+?) ADD (.+?) /', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW COLUMNS FROM %s LIKE '%s'", $matches[1],str_replace('`', '', $matches[2])), $connection)) > 0) { continue; }
					}
					if (preg_match('/^ALTER TABLE (.+?) DROP (.+?) /', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW COLUMNS FROM %s LIKE '%s'", $matches[1],str_replace('`', '', $matches[2])), $connection)) <= 0) { continue; }
					}
					if (preg_match('/^ALTER TABLE ([^\s]+) DEFAULT (.+?) /', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW TABLES LIKE '%s'", str_replace('`', '', $matches[1])), $connection)) <= 0) { continue; }
					}
					if (preg_match('/^ALTER TABLE (.+?) MODIFY (.+?) /', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW COLUMNS FROM %s LIKE '%s'", $matches[1],str_replace('`', '', $matches[2])), $connection)) <= 0) { continue; }
					}
					if (strpos($line, 'ALTER TABLE') !== false && strpos($line, 'DROP') !== false && strpos($line, 'PRIMARY') === false) {
						$params = explode(' ', $line);
						if ($params[3] == 'DROP') {
							if (mysql_num_rows(@mysql_query(sprintf("SHOW COLUMNS FROM $params[2] LIKE '$params[4]'", $matches[1],str_replace('`', '', $matches[2])), $connection)) <= 0) { continue; }
						}
					}
					if (preg_match('/^ALTER TABLE (.+?) MODIFY (.+?) /', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW COLUMNS FROM %s LIKE '%s'", $matches[1],str_replace('`', '', $matches[2])), $connection)) <= 0) { continue; }
					}

					$query .= $line;

					// If the line has a semicolon, consider it a complete query
					if (preg_match('/;\s*$/', $line)) {
						$query = str_replace("DROP TABLE IF EXISTS `oc_", "DROP TABLE IF EXISTS `" . DB_PREFIX, $query);
						$query = str_replace("CREATE TABLE `oc_", "CREATE TABLE `" . DB_PREFIX, $query);
						$query = str_replace("INSERT INTO `oc_", "INSERT INTO `" . DB_PREFIX, $query);

						$result = mysql_query($query, $connection);

						if (!$result) {
							die("Could not Execute: $query <br />" . mysql_error());
						}

						$query = '';
					}
				}
			}

			mysql_query("SET CHARACTER SET utf8", $connection);
			mysql_query("SET @@session.sql_mode = 'MYSQL40'", $connection);

			mysql_close($connection);
		}
	}
}
?>