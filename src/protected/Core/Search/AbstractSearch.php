<?php
/**
 * ISearch 的基类
 *
 * @author QiangYu
 */

namespace Core\Search;


abstract class AbstractSearch implements ISearch
{
    public function init($paramArray)
    {
        return true;
    }

}