<?php
/**
 * netbeans(8.X)でリモートPHPUnitを実行する。
 * Vagrant前提
 * 
 * ホスト側で以下が必要
 *   php + ssh2
 * 
 */
class NetBeansRemoteTestSuite
{
    /**
     * @var string 引数(colors)
     */
    const ARGS_COLORS = "--colors";
    
    /**
     * @var string 引数(bootstrap)
     */
    const ARGS_BOOTSTRAP = "--bootstrap";

    /**
     * @var string 引数(configuration)
     */
    const ARGS_CONFIGURATION = "--configuration";

    /**
     * @var string 引数(run)
     */
    const ARGS_RUN = "--run";

    /**
     * @var string 引数(--log-junit)
     */
    const ARGS_LOG_JUNIT = "--log-junit";

    /**
     * @var string 引数(--coverage-clover)
     */
    const ARGS_COVERAGE_CLOVER = "--coverage-clover";

    /**
     * @var string 引数(--include-path)
     */
    const ARGS_INCLUDE_PATH = "--include-path";
    
    /**
     * @var string 引数(--filter)
     */
    const ARGS_FILTER = "--filter";

    /**
     * @var string 引数(--list-groups)
     */
    const ARGS_LIST_GROUPS = "--list-groups";
    
    /**
     * @var string 引数(NetBeansSuite.php)
     */
    const ARGS_NETBEANS = "NetBeansSuite\.php";

    /**
     * @var NetBeansRemoteTestConfigInterface コンフィグ
     */
    private $config = null;

    /**
     * @var array 引数
     */
    private $argv   = array();
   
    /**
     * @var resource sshコネクションリソース 
     */
    private $conn = null;
    
    /**
     * @var array 結果の置換対象配列
     */
    private $replacedResultList = array();
    
    /**
     * コンストラクタ
     * @param NetBeansRemoteTestConfigInterface $config コンフィグ
     * @param array $argv 引数
     */
    public function __construct(NetBeansRemoteTestConfigInterface $config, array $argv)
    {
        $this->config = $config;
        $this->argv   = $argv;
    }

    /**
     * 実行
     */
    public function run()
    {
        try {
            //実行可能判断
            $this->checkInvokable();
            
            //netbeansの実行コマンドからリモートコマンドを生成
            $args = $this->generateRemoteCommandFromNetBeans();
            
            //接続
            $this->connectGuest();
            
            //phpunit実行
            $this->invokeRemotePhpUnit($args);
            
            //結果を配置
            $this->disposeResult();
            
            //終了
            $this->close();
        }        
        //NG
        catch (Exception $e) {
            //終了
            $this->close();
            
            $message = "[Error]" . $e->getMessage() . "\n\n"
                     . "[Trace]" . $e->getTraceAsString() . "\n\n"
                     . "[argv]" . var_export($this->argv, true) . "\n\n";

            $this->showConsole($message);
            exit(1);
        }
    }

    /**
     * 実行可能か判断
     * 
     * @throws RuntimeException
     */
    protected function checkInvokable()
    {
        //ssh2モジュールがない
        if (!function_exists("ssh2_connect")) {
            throw new RuntimeException(
                "Please enable ssh2 module."
            );
        }
    }

    /**
     * NetBeansの実行コマンドからリモート実行用のコマンドを生成
     * 
     * @return array 引数配列
     */
    private function generateRemoteCommandFromNetBeans()
    {
        $res   = array();

        //bootstrap
        if (strlen($this->config->getGuestPhpUnitBootstrapPath()) > 0) {
            $res[] = self::ARGS_BOOTSTRAP . " " . $this->config->getGuestPhpUnitBootstrapPath();
        }
        
        //config
        if (strlen($this->config->getGuestPhpUnitConfigPath()) > 0) {
            $res[] = self::ARGS_CONFIGURATION . " " . $this->config->getGuestPhpUnitConfigPath();
        }
        
        //include_path
        if (strlen($this->config->getGuestPhpUnitIncludePath()) > 0) {
            $res[] = self::ARGS_INCLUDE_PATH . " " . $this->config->getGuestPhpUnitIncludePath();
        }
        
        $list  = array(
            self::ARGS_LOG_JUNIT,
            self::ARGS_FILTER,
            self::ARGS_COVERAGE_CLOVER,
            self::ARGS_LIST_GROUPS,
        );
        
        $count = count($this->argv);
        for ($iii = 1; $iii < $count; ++$iii) {

            $target = $this->argv[$iii];
            
            //だいぶ強引だけど・・・
            if (in_array($target, $list)) {
                ++$iii;
                $target .= " " . $this->argv[$iii];
            }
            
            $arg = $this->parseArgument($target);
            
            if (strlen($arg) > 0) {
                $res[] = $arg;
            }
        }
        
        return $res;
    }
    
    /**
     * 引数を解析とか
     * 
     * @param string $arg 引数
     * @return string 結果
     * 
     * @throws RuntimeException
     */
    private function parseArgument($arg)
    {
        // netbeansからのbootstrapは受け付けてない
        if (preg_match("/^". self::ARGS_BOOTSTRAP ."/", $arg)) {
            throw new RuntimeException(
                "Error: It does not support the specification of the bootstrap.\nPlease specify in this script."
            );
        }
        
        // netbeansからのconfigも受け付けない
        if (preg_match("/^". self::ARGS_CONFIGURATION ."/", $arg)) {
            throw new RuntimeException(
                "Error: It does not support the specification of the configuration.\nPlease specify in this script."
            );
        }

        // colorsはそれしかないから
        if (preg_match("/^". self::ARGS_COLORS ."/", $arg)) {
            return $arg;
        }
        
        // list-groupsも
        if (preg_match("/^". self::ARGS_LIST_GROUPS ."/", $arg)) {
            return $arg;
        }
        
        //run
        if (preg_match("/^". self::ARGS_RUN . "\=/", $arg)) {
            return self::ARGS_RUN . "=" . $this->config->getGuestTestRootDir();
        }
        
        //log-junit
        if (preg_match("/^". self::ARGS_LOG_JUNIT ."/", $arg)) {
            $this->replacedResultList[self::ARGS_LOG_JUNIT] = $arg;
            
            $tmp = explode(" ", $arg);
            return self::ARGS_LOG_JUNIT . " " . $this->config->getGuestSyncedWorkDir() . "/" . basename(trim($tmp[1]));
        }
        
        //coverage
        if (preg_match("/^". self::ARGS_COVERAGE_CLOVER ."/", $arg)) {
            $this->replacedResultList[self::ARGS_COVERAGE_CLOVER] = $arg;
            
            $tmp = explode(" ", $arg);
            return self::ARGS_COVERAGE_CLOVER . " " . $this->config->getGuestSyncedWorkDir() . "/" . basename(trim($tmp[1]));
        }

        //filter
        if (preg_match("/^". self::ARGS_FILTER ."/", $arg)) {
            $tmp = explode(" ", $arg);
            
            //%\b～\b%で囲まれてくるので適切に直す
            $target = substr($tmp[1], 1, -1);
            $target = mb_convert_encoding($target, $this->config->getGuestInternalEncoding(), mb_detect_encoding($arg));
            $target = str_replace("\\b", "", $target);
            return self::ARGS_FILTER . " " . "\"/" . $target . "/\"" ;
        }

        //NetBeansSuite.php
        if (preg_match("/". self::ARGS_NETBEANS ."$/", $arg)) {
            $dir = $this->config->getHostSyncedWorkDir();
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            
            $dest = $dir . "/" . basename($arg);
            
            if (!copy($arg, $dest)) {
                throw new RuntimeException(
                    "Error:failed to copy NetBeansSuite. from[$arg] to[$dest]"
                );
            }
            
            return $this->config->getGuestSyncedWorkDir() . "/" . basename($arg);
        }
    }
    
    /**
     * Guestに接続する。
     * 
     * @throws RuntimeException
     */
    private function connectGuest()
    {
        $ip   = $this->config->getGuestIpAddr();
        $port = $this->config->getGuestPort();
        
        $conn = ssh2_connect($ip, $port, array('hostkey' => 'ssh-rsa'));
        if (!$conn) {
            throw new RuntimeException(
                "Error: Connection failure to host. [$ip:$port]"
            );
        }
        
        $id = $this->config->getGuestLoginUserId();
        $pw = $this->config->getGuestLoginUserPassword();

        if (!ssh2_auth_password($conn, $id, $pw)) {
            throw new RuntimeException(
                "Error: Authentication failure to host. [$id]"
            );
        }
        
        $this->conn = $conn;
    }

    /**
     * PHPUnitをリモート実行する。
     * 
     * @param array $args 組み立てた引数
     * @throws RuntimeException
     */
    private function invokeRemotePhpUnit(array $args)
    {
        $phpunit = $this->config->getGuestPhpUnitPath();
        $arg     = implode(" ", $args);
        $command = $phpunit . " " . $arg;

        $this->showConsole("\n[execution]\n$command");

        $stream = ssh2_exec($this->conn, $command);
        $streamError = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        // Enable blocking for both streams
        stream_set_blocking($stream, true);
        stream_set_blocking($streamError, true);
        
        $res = stream_get_contents($stream);
        $resError = stream_get_contents($streamError);

        $this->showConsole("\n[result]\n$res");
        
        // Close the streams
        fclose($streamError);
        fclose($stream);
       
        // Output error
        if ($resError) {
            throw new RuntimeException($resError);
        }
    }

    /**
     * 実行結果をNetBeansが読み取れる位置に配置する。
     * 
     * @throws RuntimeException
     */
    private function disposeResult()
    {
        $config = $this->config;
        $dispose = function ($target) use ($config) {
            $tmp = explode(" ", $target);
            $file = trim($tmp[1]);
            $from = $config->getHostSyncedWorkDir() . "/" . basename($file); 

            $data = file_get_contents($from);
            unlink($from);
            $data = str_replace($config->getGuestSyncedDir(), $config->getHostSyncedDir(), $data);

            if (!file_put_contents($file, $data)) {
                throw new RuntimeException(
                    "Error:failed to move files. src[$from] dest[$file]"
                );
            }
        };
        
        //log-junitの結果
        if (isset($this->replacedResultList[self::ARGS_LOG_JUNIT])) {
            $dispose($this->replacedResultList[self::ARGS_LOG_JUNIT]);
        }
        

        //coverage-cloverの結果
        if (isset($this->replacedResultList[self::ARGS_COVERAGE_CLOVER])) {
            $dispose($this->replacedResultList[self::ARGS_COVERAGE_CLOVER]);
        }
    }
    
    /**
     * 終了処理
     * @return void
     */
    private function close()
    {
        if (is_resource($this->conn)) {
            $this->conn = null;
        }
    }

    /**
     * コンソールにメッセージを表示
     * 
     * @param string $message 表示メッセージ
     */
    private function showConsole($message)
    {
        echo $message . "\n";
    }
}