<?php
/**
 * 快速开发函数库
 */

use RTP\Module;

$filePaths = NULL;

/**
 * 快捷数据库操作函数
 */
function get_database($is_new_instance = false)
{
	return $is_new_instance ? Module\DatabaseModule::getNewInstance() : Module\DatabaseModule::getInstance();
}

/**
 * 快捷Redis操作函数
 */
function get_redis($is_new_instance = false)
{
	return $is_new_instance ? Module\RedisModule::getNewInstance() : Module\RedisModule::getInstance();
}

/**
 * 直接连接redis并返回连接实例
 */
function connect_redis($redis_url, $redis_port, $redis_password)
{
	//尝试连接数据库
	$redis_con = new \Redis;
	if (!$redis_con -> pconnect($redis_url, $redis_port))
		return FALSE;
	if (!$redis_con -> auth($redis_password))
		return FALSE;
	return $redis_con;
}

/**
 * 快捷完成请求函数，用于一次性按顺序返回所有信息，无须担心Cookie放置位置。
 * 注意，需要配合P()函数使用
 */
function quick_flush()
{
	ob_start();
	$output_flush = Module\OutputStorageModule::getAll();
	if (is_null($output_flush))
		return;
	foreach ($output_flush as $value)
	{
		echo $value;
	}
	//输出缓冲区并且清除缓冲区内容
	ob_end_flush();
	Module\OutputStorageModule::clean();
}

/**
 * 快捷输入函数
 */
function quick_input($param_name, $default_value = NULL)
{
	switch (strtolower(AT))
	{
		case 'auto' :
		{
			if (is_null($_GET[$param_name]) || $_GET[$param_name] == '')
			{
				if (is_null($_POST[$param_name] || $_POST[$param_name] == ''))
					return $default_value;
				else
					return $_POST[$param_name];
			}
			else
				return $_GET[$param_name];
		}
		case 'post' :
		{
			if (is_null($_POST[$param_name]) || $_POST[$param_name] == '')
				return $default_value;
			else
				return $_POST[$param_name];
		}
		case 'get' :
		{
			if (is_null($_GET[$param_name]) || $_GET[$param_name] == '')
				return $default_value;
			else
				return $_GET[$param_name];
		}
		default :
			return NULL;
	}
}

/**
 * 安全输入函数,获取参数并且对参数进行过滤
 */
function securely_input($param_name, $default_value = NULL)
{
	switch (strtolower(AT))
	{
		case 'auto' :
		{
			if (is_null($_GET[$param_name]) || $_GET[$param_name] == '')
			{
				if (is_null($_POST[$param_name]) || $_POST[$param_name] == '')
					return $default_value;
				else
					return clean_format($_POST[$param_name]);
			}
			else
				return clean_format($_GET[$param_name]);
		}
		case 'post' :
		{
			if (is_null($_POST[$param_name]) || $_POST[$param_name] == '')
				return $default_value;
			else
				return clean_format($_POST[$param_name]);
		}
		case 'get' :
		{
			if (is_null($_GET[$param_name]) || $_GET[$param_name] == '')
				return $default_value;
			else
				return clean_format($_GET[$param_name]);
		}
		default :
			return NULL;
	}
}

/**
 * 快捷输出函数:output,默认数组输出json,字符串直接输出
 */
function quick_output($output)
{
	echo is_array($output) ? json_encode($output) : $output;
}

/**
 * 结束输出函数:output,默认数组输出json,字符串直接输出，并且输出之后停止程序
 */
function exit_output($output)
{
	exit(is_array($output) ? json_encode($output) : $output);
}

/**
 * 结束输出函数,将json字符串加密后返回
 */
function exit_output_des($output, $key)
{
	if (is_array($output))
		$output = json_encode($output);
	$result = bin2hex(des_encrypt($output, $key));
	$return_json = array("result" => $result);
	return exit_output($return_json);
}

/**
 * 快捷序列化输出函数，需要配合quickFlush()函数使用
 */
function serial_print($output, $distinct = FALSE)
{
	if ($distinct)
		if (Module\OutputStorageModule::isExist($output))
			return;
	Module\OutputStorageModule::set($output);
}

/**
 * 快速引入文件函数
 */
function quick_require($filePath)
{
	global $filePaths;
	if (is_null($filePaths))
		$filePaths = array();

	if (!isset($filePaths[$filePath]))
	{
		if (is_file($filePath))
		{
			//require不使用函数形式是因为参数带括号会降低运行速度
			require $filePath;
			$filePaths[$filePath] = TRUE;
			return TRUE;
		}
		else
		{
			$filePaths[$filePath] = FALSE;
			return FALSE;
		}
	}
}

/**
 * 快捷Session操作函数:session
 */
function quick_session(&$key, &$value)
{
	if (session_status() == 1)
		session_start();
	if (isset($_SESSION[$key]))
	{
		if (isset($value))
			$_SESSION[$key] = $value;
		return $_SESSION[$key];
	}
	else
		$_SESSION[$key] = $value;
}

/**
 * 格式清除函数
 */
function clean_format(&$value)
{
	return htmlspecialchars(stripcslashes(trim($value)));
}

/**
 * 换行输出数组信息
 */
function print_formatted(array $info)
{
	foreach ($info as $key => $value)
	{
		echo "$key:$value</br>";
	};
}

/**
 * 获取hash_key
 * @param $first_sail 附加加密参数1
 * @param $second_sail 附加加密参数2
 * @param $third_sail 附加加密参数3
 */
function get_hash_key($first_sail, &$second_sail = NULL, &$third_sail = NULL)
{
	$hash_key = '';
	$str_pool = 'NMqlzxcvdfghjQXCER67ty5HuasJKLZYTWmPASDFGk12iBpn34UIb9werV8';
	for ($i = 0; $i <= 6; $i++)
	{
		$hash_key .= $str_pool[rand(0, 58)];
	}

	//通过混合微秒数以及附加参数来获得不重复的key
	$hash_key .= sha1($first_sail . $second_sail . $third_sail);

	return $hash_key;
}

/**
 * 组合插入SQL语句
 * @param $table 表
 * @param $fields 字段数组
 */
function combine_insert_prepare_sql($table, $fields)
{
	$sql = "INSERT INTO `$table` (";
	$field_sql = '';
	foreach ($fields as $field)
	{
		$field_sql .= "`$field`,";
	}

	$value_sql = str_repeat('?,', count($fields));

	//将去除末尾逗号的字符串拼接在总字符串中
	$sql .= rtrim($field_sql, ',') . ') VALUES (' . rtrim($value_sql, ',') . ');';
	return $sql;
}

/**
 * 组合更新SQL语句
 * @param $table 表
 * @param $fields 字段数组
 * @param $where_params 筛选条件数组
 */
function combine_update_prepare_sql($table, $fields, $where_params)
{
	$sql = "UPDATE `$table` SET ";
	$field_sql = '';
	foreach ($fields as $field)
	{
		$field_sql .= "`$field` = ?,";
	}

	$where_sql = ' WHERE';
	foreach ($where_params as $param)
	{
		$where_sql .= " `$param` = ? AND";
	}

	//将去除末尾逗号的字符串拼接在总字符串中
	$sql .= rtrim($field_sql, ',') . rtrim($where_sql, 'AND') . ';';
	return $sql;
}

/**
 * 组合删除SQL语句
 * @param $table 表
 * @param $where_params 筛选条件数组
 */
function combine_delete_prepare_sql($table, $where_params)
{
	$sql = "DELETE FROM `$table`";

	$where_sql = ' WHERE';
	foreach ($where_params as $param)
	{
		$where_sql .= " `$param` = ? AND";
	}

	//将去除末尾逗号的字符串拼接在总字符串中
	$sql .= rtrim($where_sql, 'AND') . ';';
	return $sql;
}

/**
 * 组合查询SQL语句
 * @param $table 表
 * @param $fields 字段数组
 * @param $where_params 筛选条件数组
 * @param $order_params 排序条件
 * @param $limits 数量限制
 */
function combine_select_prepare_sql($table, $fields, $where_params, $order_params = array(), $limits = array())
{
	$sql = " FROM $table";

	$field_sql = 'SELECT ';
	foreach ($fields as $field)
	{
		$field_sql .= "`$field`,";
	}
	$where_sql = '';
	if($where_params)
	{
		$where_sql = ' WHERE';
		foreach ($where_params as $param)
		{
			$where_sql .= " `$param` = ? AND";
		}
		$where_sql = rtrim($where_sql, 'AND');
	}
	$order_sql = '';
	if($order_params)
	{
		$order_sql = ' ORDER BY ';
		foreach ($order_params as $k => $v)
		{
			$order_sql .= $k.' '.$v.",";
		}
		$order_sql = rtrim($order_sql, ',');
	}
	$limit_sql = '';
	if($limits)
	{
		$limit_sql = ' LIMIT '.$limits[0].",".$limits[1];
	}
	//将去除末尾逗号的字符串拼接在总字符串中
	$sql = rtrim($field_sql, ',') . $sql . $where_sql .$order_sql.$limit_sql. ';';
	return $sql;
}

/**
 * DES解密
 * @param str 解密字符串
 * @param str 解密key
 */
function des_decrypt($str, $key)
{
	$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
	$len = strlen($str);
	$block = mcrypt_get_block_size('des', 'ecb');
	$pad = ord($str[$len - 1]);
	return substr($str, 0, $len - $pad);
}

/**
 * DES加密
 * @param str 解密字符串
 * @param str 解密key
 */
function des_encrypt($str, $key)
{
	$block = mcrypt_get_block_size('des', 'ecb');
	$pad = $block - (strlen($str) % $block);
	$str .= str_repeat(chr($pad), $pad);
	return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
}

function getUserIp() {
    if(!empty($_SERVER["HTTP_CLIENT_IP"]))
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    else if(!empty($_SERVER["REMOTE_ADDR"]))
        $cip = $_SERVER["REMOTE_ADDR"];
    else
        $cip = "";

    $arrCip = explode(',', $cip);
    return urlencode($arrCip[0]);
}
?>
