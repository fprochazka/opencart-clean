<?php

use Nette\Database\SqlLiteral;



/**
 *
 */
class DB extends Nette\Object
{

	/**
	 * @var \Nette\Database\Connection
	 */
	private $driver;

	/**
	 * @var \Nette\Database\Statement
	 */
	private $lastQuery;



	/**
	 * @param Nette\Database\Connection $connection
	 */
	public function __construct(Nette\Database\Connection $connection)
	{
		$this->driver = $connection;
		if (!$connection->getSupplementalDriver() instanceof Nette\Database\Drivers\MySqlDriver) {
			throw new Nette\InvalidStateException("Wrapper is compatible only with mysql!");
		}
	}



	/**
	 * @param string $sql
	 *
	 * @return stdClass
	 */
	public function query($sql)
	{
		$this->lastQuery = $result = $this->driver->query($sql);

		if (!Nette\Utils\Strings::match($sql, '~^\s*SELECT~')) {
			return TRUE;
		}

		$query = new stdClass();
		$query->rows = array_map(function ($row) { return (array)$row; }, $result->fetchAll());
		$query->row = reset($query->rows) ? : array();
		$query->num_rows = count($query->rows);

		return $query;
	}



	/**
	 * @param string $value
	 * @return string
	 */
	public function escape($value)
	{
		return addslashes($value); // I'm going to hell for this...
	}



	/**
	 * @return int
	 */
	public function countAffected()
	{
		return $this->lastQuery ? $this->lastQuery->rowCount() : 0;
	}



	/**
	 * @return int
	 */
	public function getLastId()
	{
		return $this->driver->lastInsertId();
	}




	/**
	 * @param string $table
	 * @param array|string $values
	 * @return integer
	 */
	public function insert($table, $values)
	{
		$this->driver->table($table)
			->insert(static::formatValues($values));
		return $this->getLastId();
	}



	/**
	 * @param string $table
	 * @param array|string $pairs
	 * @param string $where
	 * @return int
	 */
	public function update($table, $pairs, $where = NULL)
	{
		$where = func_get_args();
		array_shift($where); // table
		array_shift($where); // pairs

		$table = $this->driver->table($table);
		callback($table, 'where')->invokeArgs($where);
		return $table->update(static::formatValues($pairs));
	}



	/**
	 * @param array $values
	 *
	 * @return string
	 */
	private static function formatValues($values)
	{
		foreach ($values as $key => $value) {
			$type = NULL;
			if (strpos($key, '%') !== FALSE) {
				unset($values[$key]);
				list($key, $type) = explode('%', $key);
			}

			$values[$key] = $type === 'sql'
				? new SqlLiteral($value)
				: $value;
		}

		return $values;
	}


}
