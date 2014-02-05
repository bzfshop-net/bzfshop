<?php

/**
 * @author QiangYu
 *
 * 所有 Service 的基类
 *
 * */

namespace Core\Service;

use Core\Helper\Utility\QueryBuilder;
use Core\Helper\Utility\Validator;
use Core\Modal\SqlMapper as DataMapper;

class BaseService
{

    /**
     * 验证参数是否合法，通过则返回，不通过直接抛 InvalidParameterException()
     *
     * @param object $validator validator对象
     *
     * */
    protected function validate(Validator $validator)
    {
        $hasError = $validator->hasErrors();

        if (!$hasError) {
            // 没有错误，成功返回
            return;
        }

        // 有错误，收集错误信息，抛出异常
        $errorMsg   = '';
        $errorArray = $validator->getAllErrors();
        foreach ($errorArray as $errorField => $errorMsg) {
            $errorMsg .= '{[' . $errorField . '][' . $errorMsg . ']}';
        }

        throw new \InvalidArgumentException($errorMsg);
    }

    /**
     * 单表查询
     *
     * 根据 id 取得记录信息，只有用 load 加载的数据支持 CRUD 操作
     * 比如，可以 record->save()做更新，record->erase() 做删除操作
     *
     * @return object 记录信息
     *
     * @param string $table  表名
     * @param string $filter 查询条件，例如 'user_id = ?'
     * @param int    $id     数字 ID，如果 ID为0，则返回一个新建立的 DataMapper 对象，方便之后做 save() 操作
     * @param int    $ttl    缓存时间
     *
     * */
    public function _loadById($table, $filter, $id, $ttl = 0)
    {

        $id = $id ? : 0; //如果没有 ID 则设置为 0
        // 参数验证
        $validator = new Validator(array('table' => $table, 'filter' => $filter, 'id' => $id, 'ttl' => $ttl));
        $table     = $validator->required()->validate('table');
        $filter    = $validator->required()->validate('filter');
        $id        = $validator->digits()->min(0)->validate('id');
        $ttl       = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        $dataMapper = new DataMapper($table);
        if ($id > 0) {
            $dataMapper->loadOne(array($filter, $id), null, $ttl);
        }
        return $dataMapper;
    }

    /**
     * 多表联合查询
     *
     * 根据 id 取得记录信息，注意：这里返回的记录不支持 CRUD 操作
     *
     * @return array 记录信息
     *
     * @param array  $tableArray 表名数组，例如：array('user' ,'order_info' => 'oi')
     * @param string $filter     查询条件，例如 'user_id = ?'
     * @param int    $id         数字 ID
     * @param int    $ttl        缓存时间
     *
     * */
    public function _fetchById(array $tableArray, $filter, $id, $ttl = 0)
    {

        // 参数验证
        $validator  =
            new Validator(array('tableArray' => $tableArray, 'filter' => $filter, 'id' => $id, 'ttl' => $ttl));
        $tableArray = $validator->requireArray(false)->validate('tableArray');
        $filter     = $validator->required()->validate('filter');
        $id         = $validator->required()->digits()->validate('id');
        $ttl        = $validator->digits()->min(0)->validate('ttl');
        $this->validate($validator);

        $result = $this->_fetchArray($tableArray, '*', array(array($filter, $id)), null, 0, 1, $ttl);
        if (empty($result)) {
            return null;
        }

        return $result[0];
    }

    /**
     * 取得一组结果的列表
     *
     * @return array 结果列表
     *
     * @param mixed  $table        需要查询的数据表，可以是单个表，例如：'user'，
     *                      也可以是多个表的数组，例如：array('user' ,'order_info' => 'oi')
     * @param string $fields       需要 select 的字段
     * @param array  $condArray    查询条件数组，例如：
     *                             array(
     *                             array('supplier_id = ?', $supplier_id)
     *                             array('is_on_sale = ?', 1)
     *                             array('supplier_price > ? or supplier_price < ?', $priceMin, $priceMax)
     * )
     *
     * @param array  $optionArray  目前不支持 Having 查询，留待以后扩展
     *
     * @param int    $offset       用于分页的开始 >= 0
     * @param int    $limit        每页多少条
     * @param int    $ttl          缓存多少时间
     *
     */
    public function _fetchArray(
        $table,
        $fields = '*',
        array $condArray = null,
        array $optionArray = null,
        $offset = 0,
        $limit = 10,
        $ttl = 0
    ) {
        // 构造参数验证数组
        $validatorArray           = array();
        $validatorArray['table']  = $table;
        $validatorArray['fields'] = $fields;
        $validatorArray['offset'] = $offset;
        $validatorArray['limit']  = $limit;
        $validatorArray['ttl']    = $ttl;
        if (null != $condArray) {
            $validatorArray['condArray'] = $condArray;
        }
        if (null != $optionArray) {
            $validatorArray['optionArray'] = $optionArray;
        }

        // 参数验证
        $validator = new Validator($validatorArray, '');
        $table     = $validator->required()->validate('table');
        $fields    = $validator->required()->validate('fields');
        $offset    = $validator->digits()->min(0)->validate('offset');
        $limit     = $validator->digits()->min(0)->validate('limit');
        $ttl       = $validator->digits()->min(0)->validate('ttl');

        if (null != $condArray) {
            $condArray = $validator->requireArray(false)->validate('condArray');
        }
        if (null != $optionArray) {
            $optionArray = $validator->requireArray(false)->validate('optionArray');
        }

        $this->validate($validator);

        // 构造查询条件
        $filter = null;
        if (!empty($condArray)) {
            $filter = QueryBuilder::buildAndFilter($condArray);
        }

        if (null == $optionArray) {
            $optionArray = array();
        }

        $optionArray['offset'] = $offset;
        if ($limit > 0) {
            $optionArray['limit'] = $limit;
        }

        // 创建 DataMapper
        $dataMapper = new DataMapper($table);

        if (is_string($table)) { //简单的单表查询
            $table = array($table);
        }

        if (is_array($table)) { // 复杂的多表查询
            return $dataMapper->selectComplex($table, $fields, $filter, $optionArray, $ttl);
        }

        throw new \InvalidArgumentException('table should be string or array');
    }

    /**
     *
     * 取得一组记录的数目，用于分页
     *
     * @return int 查询条数
     *
     * @param mixed $table        需要查询的数据表，可以是单个表，例如：'user'，
     *                      也可以是多个表的数组，例如：array('user' ,'order_info' => 'oi')
     *
     * @param array $condArray    查询条件数组，例如：
     *                            array(
     *                            array('supplier_id = ?', $supplier_id)
     *                            array('is_on_sale = ?', 1)
     *                            array('supplier_price > ? or supplier_price < ?', $priceMin, $priceMax)
     * )
     * 这些查询条件最终会用 and 拼接起来
     * @param array $optionArray  目前不支持 Having 查询，留待以后扩展
     * @param int   $ttl          缓存多少时间
     *
     */
    public function _countArray($table, array $condArray = null, array $optionArray = null, $ttl = 0)
    {
        // 构造参数验证数组
        $validatorArray          = array();
        $validatorArray['table'] = $table;
        $validatorArray['ttl']   = $ttl;
        if (null != $condArray) {
            $validatorArray['condArray'] = $condArray;
        }
        if (null != $optionArray) {
            $validatorArray['optionArray'] = $optionArray;
        }

        // 参数验证
        $validator = new Validator($validatorArray, '');
        $table     = $validator->required()->validate('table');
        $ttl       = $validator->digits()->min(0)->validate('ttl');

        if (null != $condArray) {
            $condArray = $validator->requireArray(false)->validate('condArray');
        }
        if (null != $optionArray) {
            $optionArray = $validator->requireArray(false)->validate('optionArray');
        }

        $this->validate($validator);

        // 构造查询条件
        $filter = null;
        if (!empty($condArray)) {
            $filter = QueryBuilder::buildAndFilter($condArray);
        }

        // 创建 DataMapper
        $dataMapper = new DataMapper($table);

        if (is_string($table)) { //简单的单表查询            
            $table = array($table);
        }

        if (is_array($table)) { // 复杂的多表查询
            return $dataMapper->selectCount($table, $filter, $optionArray, $ttl);
        }

        throw new \InvalidArgumentException('table should be string or array');
    }

}