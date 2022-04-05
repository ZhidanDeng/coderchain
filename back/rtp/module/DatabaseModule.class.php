<?php

/**
 * 数据库模块，用于数据库创建以及一系列数据库操作
 * @author rolealiu/刘昊臻,www.rolealiu.com
 * @updateTime 20160307
 */
namespace RTP\Module;

class DatabaseModule
{
	use \RTP\Traits\Singleton;
	private static $db_con;
	private $db_history;
	// 上一次操作结果
	private $last_result;
	private $last_sql;

	/**
	 * 创建对象时自动连接数据库
	 */
	protected function __construct()
	{
		self::connect();
	}

	/**
	 * 销毁对象时自动断开数据库连接
	 */
	function __destruct()
	{
		self::close();
	}

	/**
	 * 连接主机
	 */
	private function connect()
	{
		$conInfo = DB_TYPE . ':host=' . DB_URL . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8';

		// 是否保持持久化链接
		if(DB_PERSISTENT_CONNECTION)
		{
			$option = array(
					\PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
					\PDO::ATTR_PERSISTENT => TRUE,
					\PDO::ATTR_EMULATE_PREPARES => FALSE
			);
		}
		else
		{
			$option = array(
					\PDO::MYSQL_ATTR_INIT_COMMAND => "set names 'utf8'",
					\PDO::ATTR_EMULATE_PREPARES => FALSE
			);
		}
		// 尝试连接数据库
		try
		{
			self::$db_con = new \PDO($conInfo, DB_USER, DB_PASSWORD, $option);
			self::$db_con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
		catch(\PDOException $e)
		{
			if(DEBUG)
				print_r($e->getMessage());
			exit();
		}
	}

	/**
	 * 关闭主机连接
	 */
	public function close()
	{
		self::$db_con = NULL;
		self::$instance = NULL;
	}

	/**
	 * 执行无返回值的数据库操作并且返回受影响的记录条数
	 */
	public function execute($sql)
	{
		try
		{
			$this->last_sql = $sql;
			$result = self::$db_con->exec($sql);
			return $result;
		}
		catch(\PDOException $e)
		{
			$error_info = $this->getError();
			throw new ExceptionModule(12000, "database error: {$e -> getMessage()} in: {$error_info}");
		}

	}

	/**
	 * 执行操作并返回一条数据
	 */
	public function query($sql)
	{
		try
		{
			$this->last_sql = $sql;
			$this->db_history = self::$db_con->query($sql);
			$this->last_result = $this->db_history->fetch(\PDO::FETCH_ASSOC);
			return $this->last_result;
		}
		catch(\PDOException $e)
		{
			$error_info = $this->getError();
			throw new ExceptionModule(12000, "database error: {$e -> getMessage()} in: {$error_info}");
		}
	}

	/**
	 * 执行操作并返回多条数据(如果可能)
	 */
	public function queryAll($sql)
	{

		try
		{
			$this->last_sql = $sql;
			$this->db_history = self::$db_con->query($sql);
			$this->last_result = $this->db_history->fetchAll(\PDO::FETCH_ASSOC);
			return $this->last_result;
		}
		catch(\PDOException $e)
		{
			$error_info = $this->getError();
			throw new ExceptionModule(12000, "database error: {$e -> getMessage()} in: {$error_info}");
		}

	}

	/**
	 * prepare方式执行操作，返回一条数据，防止sql注入
	 */
	public function prepareExecute($sql, $params = NULL)
	{
		try
		{
			$this->last_sql = $sql;
			$this->db_history = self::$db_con->prepare($sql);
			if(is_null($params))
			{
				$this->db_history->execute();
			}
			else
			{
				$this->db_history->execute($params);
			}
			$this->last_result = $this->db_history->fetch(\PDO::FETCH_ASSOC);
			return $this->last_result;
		}
		catch(\PDOException $e)
		{
			$error_info = $this->getError();
			throw new ExceptionModule(12000, "database error: {$e -> getMessage()} in: {$error_info}");
		}
	}

	/**
	 * prepare方式执行操作，返回多条数据（如果可能），防止sql注入
	 */
	public function prepareExecuteAll($sql, $params = NULL)
	{
		try
		{
			$this->last_sql = $sql;
			$this->db_history = self::$db_con->prepare($sql);
			if(is_null($params))
			{
				$this->db_history->execute();
			}
			else
			{
				$this->db_history->execute($params);
			}
			$this->last_result = $this->db_history->fetchAll(\PDO::FETCH_ASSOC);
			return $this->last_result;
		}
		catch(\PDOException $e)
		{
			$error_info = $this->getError();
			throw new ExceptionModule(12000, "database error: {$e -> getMessage()} in: {$error_info}");
		}
	}

	/**
	 * 批量插入
	 */
	public function batchInsert($sql, $id = NULL, $params = NULL)
	{

		try
		{
			$this->last_sql = $sql;
			if(is_null($params))
			{
				$this->db_history = self::$db_con->prepare($sql);
				$this->getError();
				$this->db_history->execute();
			}
			else
			{
				$data = array();
				for($i = 0; $i < count($params); $i ++)
				{
					$result = array();
					if(! is_null($id))
					{
						$values .= "(" . substr(str_repeat('?,', count($params [$i]) + 1), 0, - 1) . ")" . ',';
						$result = array_values($params [$i]);
						array_unshift($result, $id);
					}
					else
					{
						$values .= "(" . substr(str_repeat('?,', count($params [$i])), 0, - 1) . ")" . ',';
						$result = array_values($params [$i]);
					}
					$data = array_merge($data, $result);
				}
				$values = rtrim($values, ',');
				$sql = rtrim($sql, ';');
				$this->db_history = self::$db_con->prepare($sql . $values);
				$this->db_history->execute($data);
			}
			$this->last_result = $this->db_history->fetch(\PDO::FETCH_ASSOC);
			return $this->last_result;
		}
		catch(\PDOException $e)
		{
			$error_info = $this->getError();
			throw new ExceptionModule(12000, "database error: {$e -> getMessage()} in: {$error_info}");
		}

	}

	/**
	 * prepare方式，以新的参数重新执行一次查询，返回一条数据
	 */
	public function prepareRexecute($params)
	{
		try
		{
			$this->db_history->execute($params);
			$this->last_result = $this->db_history->fetch(\PDO::FETCH_ASSOC);
			return $this->last_result;
		}
		catch(\PDOException $e)
		{
			$error_info = $this->getError();
			throw new ExceptionModule(12000, "database error: {$e -> getMessage()} in: {$error_info}");
		}

	}

	/**
	 * prepare方式，以新的参数重新执行一次查询，返回多条数据（如果可能）
	 */
	public function prepareRexecuteAll($params)
	{
		try
		{
			$this->db_history->execute($params);
			$this->last_result = $this->db_history->fetchAll(\PDO::FETCH_ASSOC);
			return $this->last_result;
		}
		catch(\PDOException $e)
		{
			$error_info = $this->getError();
			throw new ExceptionModule(12000, "database error: {$e -> getMessage()} in: {$error_info}");
		}

	}

	/**
	 * 获取上一次操作影响的行数
	 */
	public function getAffectRow()
	{
		if(is_null($this->db_history))
		{
			return FALSE;
		}
		else
		{
			return $this->db_history->rowCount();
		}
	}

	/**
	 * 获取最后执行的SQL语句
	 */
	public function getLastSQL()
	{
		return $this->last_sql;
	}

	/**
	 * 获取最后插入行的ID或序列值
	 */
	public function getLastInsertID()
	{
		return self::$db_con->lastInsertId();
	}

	/**
	 * 开始事务
	 */
	public function beginTransaction()
	{
		self::$db_con->beginTransaction();
	}

	/**
	 * 回滚事务
	 */
	public function rollback()
	{
		self::$db_con->rollback();
	}

	/**
	 * 提交事务
	 */
	public function commit()
	{
		self::$db_con->commit();
	}

	/**
	 * 获取错误
	 */
	public function getError()
	{
		$path_info = debug_backtrace(0,2)[1];
		if($path_info['args'])
		{
			foreach ($path_info['args'] as &$args)
			{
				if(is_array($args))
				{
					$args = print_r($args, TRUE);
				}
			}
		}
		$path_info['args'] = implode(",", $path_info['args']);
		return $path_info['class'].$path_info['type'].$path_info['function']."(".$path_info['args'].")"." called at ".$path_info['file'].":".$path_info['line'];
	}

}

?>
