<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/1
 * @Time: 21:17
 */

namespace log\module;


class Logger
{
    

    /**
     * 文件路径
     * @var string
     */
    public $m_sFilePath;

    /**
     * 日志信息
     * @var string
     */
    public $m_sLogExInfo;

    private $m_sPath;


    /**
     * 构造函数
     * @param string $sPath     路径
     * @param string $sFileName 文件名
     */
    public function __construct($sPath) {
        $this->m_sPath = CODER_LOG_PATH . $sPath;
        $ip = getUserIp();
        if (!isset($this->m_sLogExInfo)) {
            $this->m_sLogExInfo = "IP:{$ip}";
        }
        $this->init();
    }

    public function init () {
        
        $dtDataYMD = date("YmdH");
        $this->m_sFilePath = $this->m_sPath.'/'.$dtDataYMD.".log";

        //创建日志路径
        try
        {
            if (!file_exists($this->m_sPath)) {
                if (!mkdir($this->m_sPath, 0777, true)) {
                    throw new \Exception("Create directory Failure : {$this->m_sPath}", __LINE__);
                }
            }

            //创建日志文件
            if ( !file_exists( $this->m_sFilePath ) ) {
                if ( !touch( $this->m_sFilePath ) ) {
                    throw new \Exception( "Create Log File Failure" , __LINE__);
                }
            }
            return false;
        }
        catch (\Exception $e)
        {
            return false;
        }


    }

    public function writeLog($sFile, $sFineLine, $iLogLevel, $sMsg) {

        $sData = date("Y-m-d H:i:s");


        $sLog = "[{$sData}[$sFile][{$iLogLevel}][line:{$sFineLine}][$this->m_sLogExInfo][$sMsg]\r\n";


        $handle = fopen( $this->m_sFilePath , "a+" );


        //写日志
        try
        {
            if( !fwrite( $handle , $sLog ) ) {
                //写日志失败
                fclose($handle);
                throw new \Exception( "Write Log to file Error.\n" );
            }

            //关闭文件
            fclose($handle);
            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }

    }
}