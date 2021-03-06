<?php
/**
 * 共享目录访问控制，包括能否共享
 *
 * @author app <app@miniyun.cn>
 * @link http://www.miniyun.cn
 * @copyright 2014 Chengdu MiniYun Technology Co. Ltd.
 * @license http://www.miniyun.cn/license.html 
 * @since 1.6
 */
class SharesAccessFilter {
    
    /**
     * 获取用户全部的共享目录
     * @param integer $userId
     * @return array
     */
    public function handleGetAllSharesFolder($userId) {
        $files            = MiniFile::getInstance()->getShares($userId);
        $paths            = array();
        foreach ($files as $file) {
            $fileMeta     = MiniFileMeta::getInstance()->getFileMeta($file['file_path'],'shared_folders');
            if ($fileMeta === NULL) {
                continue;
            }
            $metaValue = unserialize($fileMeta['meta_value']);
            array_push($paths, $metaValue['path']);
            array_push($paths, $file['file_path']);
        }
        return $paths;
    }
    
    /**
     * 是否能被共享
     * @param array $sharePaths    - 数组，有包含user_id的path组成
     * @param string $path         - 包含user_id的path
     * @return bool
     */
    public function canShared($sharePaths, $path) {
        $paths = CUtils::assemblyPaths($path);
        $keys  = array_keys($paths);
        if (count($keys) > 0) {
            unset($paths[$keys[0]]);
        }
        //
        // 检查path，如果存在于$paths中则父目录存在共享
        //
        foreach ($sharePaths as $k => $sharePath) {
            if (isset($paths[$sharePath])) {
                return false;
            }
        }
        
        //
        // 检查子目录是否存在共享，如果存在则返回false
        //
        $path .= '/';
        foreach ($sharePaths as $k => $sharePath) {
            if (strpos($sharePath, $path) === 0) {
                return false;
            }
        }
        
        return  true;
    }

}