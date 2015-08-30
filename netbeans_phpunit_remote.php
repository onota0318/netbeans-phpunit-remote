<?php
require_once 'src/NetBeansRemoteTestConfig.php';
require_once 'src/NetBeansRemoteTestSuite.php';

$config = new NetBeansRemoteTestConfig(require_once 'config.php');
(new NetBeansRemoteTestSuite($config, $argv))->run();
