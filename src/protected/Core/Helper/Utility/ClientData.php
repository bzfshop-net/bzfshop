<?php

/**
 * @author QiangYu
 *
 * 有一些数据是传递给客户端让客户端显示的，这个工具类就是提供客户端数据的存储和取出
 *
 * 目前我们把这些数据存放在 session 中
 *
 * */

namespace Core\Helper\Utility;

final class ClientData
{

    /**
     * client data 是否发生过变化
     *
     * @var bool
     */
    private static $isClientDataChange = false;

    /**
     * Key 确保全局唯一
     *
     * @var string
     */
    public static $clientDataKey = 'bzf_client_data_fwetwtgw13';

    /**
     * 通知客户端 client data 发生了变化
     *
     * @var string
     */
    public static $notifyClientDataChangeKey = 'bzf_client_data_change_timestamp';

    /**
     * 取得客户端数据数组
     *
     * @return array
     */
    public static function getClientDataArray()
    {
        global $f3;
        return $f3->get('SESSION[' . ClientData::$clientDataKey . ']');
    }

    /**
     * @param array $clientDataArray
     *
     * 保存客户端数据数组
     *
     */
    public static function saveClientDataArray($clientDataArray)
    {
        global $f3;
        $f3->set('SESSION[' . ClientData::$clientDataKey . ']', $clientDataArray);

        // 标记数据发生了变化
        ClientData::$isClientDataChange = true;
    }

    /**
     * 清空所有的客户端数据
     */
    public static function clearClientData()
    {
        ClientData::saveClientDataArray(null);
    }

    /**
     * 保存客户端数据
     *
     * @param string $key
     * @param mixed  $data
     */
    public static function saveClientData($key, $data)
    {
        $clientDataArray = ClientData::getClientDataArray();
        if (null == $clientDataArray) {
            $clientDataArray = array();
        }

        if (null == $data) {
            // null 表明清除数据
            unset($clientDataArray[$key]);
        } else {
            $clientDataArray[$key] = $data;
        }

        ClientData::saveClientDataArray($clientDataArray);
    }

    /**
     * 取得客户端数据
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function getClientData($key)
    {
        $clientDataArray = ClientData::getClientDataArray();
        if (empty($clientDataArray)) {
            return null;
        }

        if (array_key_exists($key, $clientDataArray)) {
            return $clientDataArray[$key];
        }

        return null;
    }

    /**
     * 通知客户端数据发生了变化，客户端根据这个通知主动来取得数据
     */
    public static function notifyClientDataChange()
    {
        if (ClientData::$isClientDataChange) {
            global $f3;
            setcookie(
                ClientData::$notifyClientDataChangeKey,
                time(),
                0,
                $f3->get('BASE') . '/',
                Route::getCookieDomain()
            );
        }
    }
}