<?php
/**
 * Config用interface
 */
interface NetBeansRemoteTestConfigInterface
{
    /**
     * Guest側synced_holder
     * @return string synced_holder
     */
    public function getGuestSyncedDir();
    
    /**
     * Host側synced_holder
     * @return string synced_holder
     */
    public function getHostSyncedDir();
    
    /**
     * Guest側の作業Dirを取得
     * @return string Dir
     */
    public function getGuestSyncedWorkDir();
    
    /**
     * Host側の作業Dirを取得
     * @return string Dir
     */
    public function getHostSyncedWorkDir();
    
    /**
     * 作業領域（ここは相対パス！！！）
     * Guest側・Host側のsynced_holderからパスを構築
     * @return string パス
     */
    public function getSyncedRelativeWorkDir();
    
    /**
     * Guest側テスト対象Dir
     * @return string 対象Dir
     */
    public function getGuestTestRootDir();
    
    /**
     * Guest側のプロジェクトルート
     * @return string プロジェクトルート
     */
    public function getGuestProjectRoot();

    /**
     * GuestのIPアドレス
     * @return string IPアドレス
     */
    public function getGuestIpAddr();
    
    /**
     * Guest側のポート
     * @return string ポート
     */
    public function getGuestPort();
    
    /**
     * Guest側のユーザー
     * @return string ユーザー
     */
    public function getGuestLoginUserId();
   
    /**
     * Guest側のパスワード
     * @return string パスワード
     */
    public function getGuestLoginUserPassword();
        
    /**
     * Guest側のPHPUnitのパス
     * @return string phpunitのパス
     */
    public function getGuestPhpUnitPath();
    
    /**
     * Guest側のPHPUnit --configurationのパス
     * @return string configパス
     */
    public function getGuestPhpUnitConfigPath();
    
    /**
     * Guest側のPHPUnit --bootstrapのパス
     * @return string bootstrapパス
     */
    public function getGuestPhpUnitBootstrapPath();
    
    /**
     * Guest側のPHPUnit --include_pathのパス
     * @return string include_pathパス
     */
    public function getGuestPhpUnitIncludePath();
    
    /**
     * Guest側のencoding
     * @return string encode
     */
    public function getGuestInternalEncoding();
    
    /**
     * Host側のプロジェクトルート
     * @return string プロジェクトルート
     */
    public function getHostProjectRoot();
}

