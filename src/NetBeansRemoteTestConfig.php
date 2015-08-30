<?php
include_once 'NetBeansRemoteTestConfigInterface.php';

/**
 * Configクラス
 */
class NetBeansRemoteTestConfig implements NetBeansRemoteTestConfigInterface
{
    /**
     * @var array 設定配列 
     */
    private $config = array();
    
    /**
     * コンストラクタ
     * @param array $config 設定配列
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;
    }
    
    /**
     * {@inheriteDocs}
     */
    public function getGuestSyncedWorkDir()
    {
        return $this->getGuestSyncedDir() . "/" . $this->getSyncedRelativeWorkDir();
    }

    /**
     * {@inheriteDocs}
     */
    public function getHostSyncedWorkDir()
    {
        return $this->getHostSyncedDir() . "/" . $this->getSyncedRelativeWorkDir();
    }
    
    /**
     * {@inheriteDocs}
     */
    public function getGuestIpAddr() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestPhpUnitBootstrapPath() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestPhpUnitConfigPath() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestPhpUnitIncludePath()
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }
    
    /**
     * {@inheriteDocs}
     */
    public function getGuestPhpUnitPath() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestInternalEncoding()
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }
    
    /**
     * {@inheriteDocs}
     */
    public function getGuestPort() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestProjectRoot() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestLoginUserId() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestLoginUserPassword() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }
    
    /**
     * {@inheriteDocs}
     */
    public function getSyncedRelativeWorkDir() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getHostProjectRoot() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestSyncedDir() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getHostSyncedDir() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * {@inheriteDocs}
     */
    public function getGuestTestRootDir() 
    {
        return $this->getConfigFromMethodName(__METHOD__);
    }

    /**
     * 任意の設定を取得
     * 
     * @param string $property プロパティ名
     * @return string 設定
     */
    public function getConfig($property)
    {
        if (!isset($this->config[$property])) {
            return "";
        }
        
        return $this->config[$property];
    }

    /**
     * method名から設定を取得
     * 
     * @param string $method メソッド名
     * @return string 設定
     */
    private function getConfigFromMethodName($method)
    {
        //fullnameならクラス名をネグる
        if (false !== strpos($method, '::')) {
            $method = substr($method, strpos($method, '::') + 2);
        }
        
        //getterメソッドの接頭子をネグった名前を取得
        $property = preg_replace('/[A-Z]/', '.\0', substr($method, strlen("get")));
        $property = ltrim(strtolower($property), ".");
        return $this->getConfig($property);
    }
}
