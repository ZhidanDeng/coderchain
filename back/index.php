<?php
/**
 * RIPPLE TECHNOLOGY PHP FRAMEWORK(RTP) 2.0
 * 此文件为统一入口，所有的请求将发送到此入口，并且由框架的路由机制进行分发
 * 详细的参数请参看框架起始文件
 * @author rolealiu/刘昊臻
 * @version 2.0 beta2
 * @updateDate 20160829
 */

//是否初次部署，设定为TRUE将在所有用户自行创建的用户目录下新建空白的index.html文件防止部分服务器开启的目录查看功能，上线前设为false提高性能
define('FIRST_DEPLOYMENT', FALSE);

//定义请求方式(AJAX-Type)，GET/POST/AUTO,默认为POST
define('AT', 'AUTO');

//是否开启纠错模式，开启之后将会输出所有错误信息，请在上线之前禁用DEBUG!
define('DEBUG', TRUE);

// 测试模式
define('TEST',FALSE);

//主机地址
define('DB_URL', '127.0.0.1');

//连接数据库的用户名
define('DB_USER', 'root');

//连接数据库的密码，推荐使用随机生成的字符串
define('DB_PASSWORD', '1149691788');

//数据库类型，用于PDO数据库连接
define('DB_TYPE', 'mysql');

//数据库名
define('DB_NAME', 'dbCoderChain');

// redis
define('REDIS_URL', '119.91.150.124');

//Redis端口
define('REDIS_PORT', '6379');



// 静态资源路径，有待优化
define('STATIC_FONTS', __DIR__.'/data/fonts');

define('STATIC_CODES', __DIR__.'/data/codes');



define('STATIC_FILES', __DIR__.'/data/coderchain-static-files');

define('STATIC_PROJECTS', __DIR__.'/data/coderchain-static-files/projects');

define('STATIC_TMP', __DIR__.'/data/coderchain-static-files/tmp');

define('STATIC_AVATARS', __DIR__.'/data/coderchain-static-files/avatars');

define('CODER_LOG_PATH', __DIR__ . '/data/log/coderchain');

define('NULS_API', 'http://119.91.150.124:9999');

define('IPFS_API', 'http://119.91.150.124:5001/api/v0');

define('IPFS_VIEW', 'http://119.91.150.124:8080');

define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))).'/';

//数据库是否需要保持长期连接（长连接）,多线程高并发环境下请开启,默认关闭
define('DB_PERSISTENT_CONNECTION', FALSE);

header('Content-type:text/html;charset=utf-8');
Header('Access-Control-Allow-Headers:Content-Type');
header('Access-Control-Allow-Credentials:true');//是否支持cookie跨域
//引入框架
session_start();

require './rtp/rtp.inc.php';
?>