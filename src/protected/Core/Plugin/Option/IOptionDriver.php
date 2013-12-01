<?php
/**
 * @author QiangYu
 *
 * Option 的操作接口定义
 *
 */

namespace Core\Plugin\Option;


interface IOptionDriver
{
    /**
     * 判断 OptionValue 是否存在
     *
     * @param string $optionName
     *
     * @return bool
     */
    public function isOptionValueExist($optionName);

    /*************************** 这里是单值操作， key-->value 1:1对应 ********************************/

    /**
     * 取得 Option 对应的值
     *
     * @param string  $optionName
     * @param integer $ttl 缓存时间
     *
     * @return mixed
     */
    public function getOptionValue($optionName, $ttl = 0);

    /**
     * 保存 Option 的值
     *
     * @param string $optionName
     * @param mixed  $optionValue
     *
     * @return mixed
     */
    public function saveOptionValue($optionName, $optionValue);

    /**
     * 删除 option 值
     *
     * @param string $optionName
     *
     * @return mixed
     */
    public function removeOptionValue($optionName);

    /*************************** /这里是单值操作， key-->value 1:1对应 ********************************/

    /*************************** 这里是多值操作， key-->value 1:N 对应 ********************************/

    /**
     * 返回一组 Option 值
     *
     * @param  string $optionName
     * @param int     $ttl
     *
     * @return array
     */
    public function fetchOptionValueArray($optionName, $ttl = 0);

    /**
     * 删除一组所有的 option
     *
     * @param string $optionName
     *
     * @return mixed
     */
    public function removeOptionValueArray($optionName);

    /**
     * 新建或者更新一个 option
     *
     * @param int    $optionId
     * @param string $optionName
     * @param string $optionValue
     *
     * @return mixed
     */
    public function saveOptionValueById($optionId, $optionName, $optionValue);

    /**
     * 删除一个 option
     *
     * @param int $optionId
     *
     * @return mixed
     */
    public function removeOptionValueById($optionId);

    /*************************** /这里是多值操作， key-->value 1:N 对应 ********************************/

}