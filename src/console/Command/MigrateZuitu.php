<?php

/**
 * @author QiangYu
 *
 *  迁移最土团购程序的数据库数据到 bzfshop
 *
 */
use Console\Modal\DstDataMapper;
use Console\Modal\SrcDataMapper;
use Console\Modal\TableMigrate;
use Core\Helper\Image\StorageImage as StorageImageHelper;
use Core\Helper\Utility\Time;
use Core\Modal\SqlMapper as DataMapper;
use Core\Service\Goods\Gallery as GoodsGalleryService;


/**
 * 抓取商品的图片到本地，并且自动生成缩率图
 *
 */
function fetchGoodsImage($goods_id, $imageUrl)
{
    global $f3;

    printLog('start to fetch goods_id [' . $goods_id . '] imageUrl[' . $imageUrl . ']');

    // 抓取图片，伪装成浏览器防止被某些服务器阻止
    $webInstance = \Web::instance();
    $webInstance->engine('curl');
    $request = $webInstance->request(
        $imageUrl,
        array(
             'user_agent' =>
                 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729)'
        )
    );

    if (!$request || (isset($request['http_code']) && 200 != $request['http_code'])) {
        // 抓取失败，什么都不做
        printLog('can not fetch [' . $imageUrl . ']', 'fetchGoodsImage', \Core\Log\Base::ERROR);
        goto out_release_res;
    }

    // 上传目录
    $dataPathRoot = $f3->get('sysConfig[data_path_root]');

    $saveFilePath = $dataPathRoot . '/upload/image/' . date("Y/m/d");
    if (!file_exists($saveFilePath)) {
        if (!mkdir($saveFilePath, 0755, true)) {
            printLog('can not mkdir [' . $saveFilePath . ']', 'fetchGoodsImage', \Core\Log\Base::ERROR);
            goto out_release_res;
        }
    }

    //保存文件
    $saveFilePath .= '/' . date("YmdHis") . '_' . rand(1, 10000) . strtolower(strrchr($imageUrl, '.'));
    file_put_contents($saveFilePath, $request['body']);

    printLog('save to image : ' . $saveFilePath);

    // 保存 goods_gallery 记录
    $imageFileRelativeName = str_replace($dataPathRoot . '/', '', $saveFilePath);

    $pathInfoArray              = pathinfo($imageFileRelativeName);
    $imageThumbFileRelativeName =
        $pathInfoArray['dirname'] . '/' . $pathInfoArray['filename'] . '_'
        . $f3->get('sysConfig[image_thumb_width]') . 'x' . $f3->get('sysConfig[image_thumb_height]') . '.jpg';

    //生成缩略图
    StorageImageHelper::resizeImage(
        $dataPathRoot,
        $imageFileRelativeName,
        $imageThumbFileRelativeName,
        $f3->get('sysConfig[image_thumb_width]'),
        $f3->get('sysConfig[image_thumb_height]')
    );

    //保存 goods_gallery 记录
    $goodsGalleryService = new GoodsGalleryService();

    // ID 为0，返回一个新建的 dataMapper
    $goodsGallery = $goodsGalleryService->_loadById('goods_gallery', 'img_id=?', 0);

    $goodsGallery->goods_id     = $goods_id;
    $goodsGallery->img_url      = $imageFileRelativeName;
    $goodsGallery->img_desc     = '最土转化图片';
    $goodsGallery->img_original = $imageFileRelativeName;
    $goodsGallery->thumb_url    = $imageThumbFileRelativeName;

    $goodsGallery->save();

    printLog('success fetch [' . $goods_id . '] [' . $imageUrl . ']', 'fetchGoodsImage');

    out_release_res:
    unset($request);
    unset($webInstance);
}


class MigrateZuitu implements \Clip\Command
{

    /**
     * 初始化数据源
     */
    private function initDataSource()
    {
        global $f3;

        // 最土数据库
        //SrcDataMapper::setTablePrefix($f3->get('sysConfig[src_db_table_prefix]'));
        //SrcDataMapper::setDbEngine(
        //    new \Core\Modal\DbEngine($f3->get('sysConfig[src_db_pdo]'),
        //        $f3->get('sysConfig[src_db_username]'), $f3->get('sysConfig[src_db_password]'))
        //);

        // 目标 bzf 数据库
        //DstDataMapper::setTablePrefix($f3->get('sysConfig[dst_db_table_prefix]'));
        //DstDataMapper::setDbEngine(
        //    new \Core\Modal\DbEngine($f3->get('sysConfig[dst_db_pdo]'),
        //        $f3->get('sysConfig[dst_db_username]'), $f3->get('sysConfig[dst_db_password]'))
        //);

        // 系统缺省指向 bzf 数据库
        //DataMapper::setTablePrefix($f3->get('sysConfig[db_table_prefix]'));
        //DataMapper::setDbEngine(
        //    new \Core\Modal\DbEngine($f3->get('sysConfig[db_pdo]'),
        //        $f3->get('sysConfig[db_username]'), $f3->get('sysConfig[db_password]'))
        //);
    }


    /**
     * 清除一些无关的表
     */
    private function clearTable()
    {
        $tableMigrate = new TableMigrate();

        $table = new DstDataMapper('account_log');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('admin_log');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('affiliate_log');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('auction_log');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('auto_manage');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('back_goods');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('back_order');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('booking_goods');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('brand');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('comment');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('delivery_goods');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('delivery_order');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('email_list');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('email_sendlist');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('error_log');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('feedback');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('goods_activity');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('goods_article');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('group_goods');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('keywords');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('link_goods');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('order_action');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('order_goods');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('order_info');
        $tableMigrate->clearTable($table);
        $srcOrder = new SrcDataMapper('cartinfo');
        list($result) = $srcOrder->select('max(id) as mvalue', null, null, 0);
        $autoIncValue = $result->getAdhocValue('mvalue') + 1;
        $tableMigrate->setAutoIncValue($table, $autoIncValue);
        unset($srcOrder);
        unset($table);

        $table = new DstDataMapper('order_goods');
        $tableMigrate->clearTable($table);
        $srcOrder = new SrcDataMapper('order');
        list($result) = $srcOrder->select('max(id) as mvalue', null, null, 0);
        $autoIncValue = $result->getAdhocValue('mvalue') + 1;
        $tableMigrate->setAutoIncValue($table, $autoIncValue);
        unset($srcOrder);
        unset($table);

        $table = new DstDataMapper('order_refer');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('pack');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('package_goods');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        $table = new DstDataMapper('pay_log');
        $tableMigrate->clearTable($table);
        $tableMigrate->setAutoIncValue($table, 1);
        unset($table);

        unset($tableMigrate);
    }

    /**
     * 商品分类的转化
     */
    private function convertCategory()
    {
        $tableMigrate = new TableMigrate();

        // 清空数据
        $dstTable = new DstDataMapper('category');
        $tableMigrate->clearTable($dstTable);

        //转化数据
        $tableMigrate->convertTable(
            'category', // src 表
            array(array(" zone = 'group' ")), // src 表查询条件
            array('order' => 'id asc'), // src 表数据排序
            'category', // dst 表
            // src 和 dst 表的列对应
            array(
                 'id'         => 'cat_id',
                 'name'       => 'cat_name',
                 'sort_order' => 'sort_order',
                 'display'    => 'is_show',
            ),
            // src 数据做转化
            array(
                 'display' => function ($display, $record) {
                         if ('Y' == $display) {
                             return 1;
                         }
                         return 0;
                     },
            )
        );

        // 重设表的 AUTO_INCREMENT 值
        $tableMigrate->resetAutoIncValue($dstTable, 'cat_id');

        // 清理数据
        unset($tableMigrate);
        unset($dstTable);
        unset($result);
    }


    /**
     * 供货商账号转化
     */
    private function convertSuppliers()
    {
        $tableMigrate = new TableMigrate();

        // 清空数据
        $dstTable = new DstDataMapper('suppliers');
        $tableMigrate->clearTable($dstTable);

        //转化数据
        $tableMigrate->convertTable(
            'partner', // src 表
            array(), // src 表查询条件
            array('order' => 'id asc'), // src 表数据排序
            'suppliers', // dst 表
            // src 和 dst 表的列对应
            array(
                 'id'       => 'suppliers_id',
                 'username' => 'suppliers_name',
                 'password' => 'password',
                 'title'    => 'suppliers_desc',
                 'phone'    => 'phone',
                 'address'  => 'address',
                 'group_id' => 'ec_salt', // 随便选了一个没用的列，目的是用 function() 来设置 ec_salt 的值
            ),
            // src 数据做转化
            array(
                 'group_id' => function ($ec_salt, $record) {
                         return '@4!@#$%@'; // 注意：这里是最土系统里面写死的值
                     },
            )
        );

        // 重设表的 AUTO_INCREMENT 值
        $tableMigrate->resetAutoIncValue($dstTable, 'suppliers_id');

        // 清理数据
        unset($tableMigrate);
        unset($dstTable);
        unset($result);
    }


    /**
     * 商品数据转化
     */
    private function convertGoods()
    {
        global $f3;

        // 在这里配置源商品图片的路径前缀
        $f3->set('srcImagePrefix', "http://img.bangzhufu.com/static/");

        $tableMigrate                    = new TableMigrate();
        $tableMigrate->batchProcessCount = 100; // 每次批处理 100 个商品，防止商品过多搞死系统

        // 清空数据
        $dstTable = new DstDataMapper('goods');
        $tableMigrate->clearTable($dstTable);

        $goodsGalleryTable = new DstDataMapper('goods_gallery');
        $tableMigrate->clearTable($goodsGalleryTable);
        $tableMigrate->setAutoIncValue($goodsGalleryTable, 1);

        $goodsAttrTable = new DstDataMapper('goods_attr');
        $tableMigrate->clearTable($goodsAttrTable);
        $tableMigrate->setAutoIncValue($goodsAttrTable, 1);

        //转化数据
        $currentTime = time();
        $tableMigrate->convertTable(
            'team', // src 表
            // 我们只迁移在线商品，早就下线的商品就算了
            array(array('begin_time < ? and end_time > ?', $currentTime, $currentTime)), // src 表查询条件
            array('order' => 'id asc'), // src 表数据排序
            'goods', // dst 表
            // src 和 dst 表的列对应
            array(
                 'id'              => 'goods_id',
                 'user_id'         => 'goods_sn', // 随便找了个列，目的是下面可以用 function 修改 goods_sn
                 'title'           => 'goods_name',
                 'summary'         => 'goods_brief',
                 'group_id'        => 'cat_id',
                 'partner_id'      => 'suppliers_id',
                 'team_price'      => 'shop_price',
                 'market_price'    => 'market_price',
                 'agent_price'     => 'suppliers_price',
                 'product'         => 'goods_name_short',
                 'condbuy'         => 'condbuy',
                 'image'           => null,
                 'image1'          => null,
                 'image2'          => null,
                 'agent_fare'      => 'suppliers_shipping_fee',
                 'farefree'        => 'shipping_free_number',
                 'detail'          => 'goods_desc',
                 'notice'          => 'goods_notice',
                 'sort_order'      => 'sort_order',
                 'seo_title'       => 'seo_title',
                 'seo_keyword'     => 'seo_keyword',
                 'seo_description' => 'seo_description',
                 'express_relate'  => 'shipping_fee',
                 'city_id'         => 'goods_number', // 随便找个字段对应 goods_number，目的是可以使用后面的处理函数
            ),
            // src 数据做转化
            array(
                 'user_id'        => function ($user_id, $record) {
                         global $f3;
                         //在这里生成 goods_sn
                         return $f3->get('sysConfig[goods_sn_prefix]') . $record->id;
                     },
                 'express_relate' => function ($express_relate, $record) {

                         // 最土系统 -1 表示免邮费，我们这里修改邮费为 0
                         if ($record['farefree'] < 0) {
                             return 0;
                         }

                         $dataArray      = unserialize($express_relate);
                         $maxShippingFee = 0;
                         // 取最大的快递费
                         foreach ($dataArray as $data) {
                             $maxShippingFee = ($data['price'] > $maxShippingFee) ? $data['price'] : $maxShippingFee;
                         }
                         return $maxShippingFee;
                     },
                 'farefree'       => function ($farefree, $record) {
                         // 最土 -1 表示免运费，我们这里一律改成不免邮费
                         if ($farefree < 0) {
                             return 0;
                         }
                         return $farefree;
                     },
                 'city_id'        => function ($city_id, $record) {
                         return 1000; // 所有库存缺省都设置为 1000
                     },
            ),
            // recordPreFunc
            function ($srcRecord) {

                global $f3;
                $srcImagePrefix = $f3->get('srcImagePrefix');

                $fetchImageArray = array();
                if (!empty($srcRecord->image)) {
                    $fetchImageArray[] = $srcImagePrefix . $srcRecord->image;
                }

                if (!empty($srcRecord->image1)) {
                    $fetchImageArray[] = $srcImagePrefix . $srcRecord->image1;
                }

                if (!empty($srcRecord->image2)) {
                    $fetchImageArray[] = $srcImagePrefix . $srcRecord->image2;
                }

                if (!empty($fetchImageArray)) {
                    // 设置 fetchImageArray 的值，用于后面图片抓取
                    $f3->set('fetchImageArray_' . $srcRecord->id, $fetchImageArray);
                }
            },
            // recordPostFunc
            function ($srcRecord, $dstRecord) {
                global $f3;
                $fetchImageArray = $f3->get('fetchImageArray_' . $srcRecord->id);
                if (empty($fetchImageArray)) {
                    return;
                }

                // 我们在这里做图片的抓取操作
                foreach ($fetchImageArray as $fetchImageUrl) {
                    fetchGoodsImage($srcRecord->id, $fetchImageUrl);
                    //usleep(200000); // 睡 200 ms，防止抓取太快服务器不响应
                }

                // 释放资源
                $f3->clear('fetchImageArray_' . $srcRecord->id);

                // 处理 condbuy 字段
                if (empty($srcRecord->condbuy)) {
                    return;
                }

                // 需要更新商品的选择
                $dstRecord->goods_type = $f3->get('sysConfig[condbuy_goods_type]');

                // 删除旧的数据
                $sql      = 'delete from ' . DataMapper::tableName('goods_attr') . ' where goods_id = ?';
                $dbEngine = DstDataMapper::getDbEngine();
                $dbEngine->exec($sql, $srcRecord->id);

                // 解析 condbuy {红色}{绿色}{蓝色}
                $condBuyArray = explode('}{', '}' . $srcRecord->condbuy . '{');
                foreach ($condBuyArray as $condBuyItem) {
                    if (empty($condBuyItem)) {
                        continue;
                    }
                    $dataMapper             = new DataMapper('goods_attr');
                    $dataMapper->goods_id   = $srcRecord->id;
                    $dataMapper->attr_id    = $f3->get('sysConfig[condbuy_attr_id]');
                    $dataMapper->attr_value = $condBuyItem;
                    $dataMapper->attr_price = 0;
                    $dataMapper->save();
                    unset($dataMapper);
                }
            }

        );

        // 重设表的 AUTO_INCREMENT 值
        $tableMigrate->resetAutoIncValue($dstTable, 'goods_id');

        // 清理数据
        unset($tableMigrate);
        unset($dstTable);
        unset($result);
    }

    //商品团购信息转化
    private function convertGoodsTeam()
    {
        global $f3;

        $tableMigrate = new TableMigrate();

        // 清空数据
        $dstTable = new DstDataMapper('goods_team');
        $tableMigrate->clearTable($dstTable);
        $tableMigrate->setAutoIncValue($dstTable, 1); // auto_increment 设置为 1

        //转化数据
        $currentTime = time();
        $tableMigrate->convertTable(
            'team', // src 表
            // 我们只迁移在线商品，早就下线的商品就算了
            array(array('begin_time < ? and end_time > ?', $currentTime, $currentTime)), // src 表查询条件
            array('order' => 'id asc'), // src 表数据排序
            'goods_team', // dst 表
            // src 和 dst 表的列对应
            array(
                 'id'              => 'goods_id',
                 'title'           => 'team_title',
                 'summary'         => 'team_summary',
                 'team_price'      => 'team_price',
                 'per_number'      => 'team_per_number',
                 'min_number'      => 'team_min_number',
                 'max_number'      => 'team_max_number',
                 'now_number'      => 'team_now_number',
                 'pre_number'      => 'team_pre_number',
                 'sort_order'      => 'team_sort_order',
                 'begin_time'      => 'team_begin_time',
                 'end_time'        => 'team_end_time',
                 'seo_title'       => 'team_seo_title',
                 'seo_keyword'     => 'team_seo_keyword',
                 'seo_description' => 'team_seo_description',
                 'disable'         => 'team_enable', // 随便找了个列对应
            ),
            // src 数据做转化
            array(
                 'disable'    => function ($disable, $record) {
                         return 1;
                     },
                 'begin_time' => function ($time, $record) {
                         return Time::localTimeToGmTime($time);
                     },
                 'end_time'   => function ($time, $record) {
                         return Time::localTimeToGmTime($time);
                     },
            )
        );

        // 清理数据
        unset($tableMigrate);
        unset($dstTable);
    }

    //商品推广渠道信息转化
    private function convertGoodsPromote()
    {
        global $f3;

        $tableMigrate = new TableMigrate();

        // 清空数据
        $dstTable = new DstDataMapper('goods_promote');
        $tableMigrate->clearTable($dstTable);
        $tableMigrate->setAutoIncValue($dstTable, 1); // auto_increment 设置为 1

        //转化数据
        $currentTime = time();
        $tableMigrate->convertTable(
            'team', // src 表
            // 我们只迁移在线商品，早就下线的商品就算了
            array(array('begin_time < ? and end_time > ?', $currentTime, $currentTime)), // src 表查询条件
            array('order' => 'id asc'), // src 表数据排序
            'goods_promote', // dst 表
            // src 和 dst 表的列对应
            array(
                 'id'                      => 'goods_id',
                 'group_360'               => '360tuan_category',
                 'group_360_end'           => '360tuan_category_end',
                 'seo_keyword'             => '360tuan_feature',
                 'group_renrenzhe'         => 'renrenzhe_category',
                 'qqcaibei_tag'            => 'qqcaibei_tag',
                 'qqcaibei_subtag'         => 'qqcaibei_subtag',
                 'sogou_category_1'        => 'sogoutuan_category_1',
                 'sogou_category_2'        => 'sogoutuan_category_2',
                 'sogou_category_3'        => 'sogoutuan_category_3',
                 '360_price'               => '360tequan_price',
                 '360pin_image'            => '360tuan_pin_images',
                 'sogou_order'             => 'sogoutuan_sort_order',
                 'is_360tegong'            => '360tegong_enable',
                 '360tegong_image'         => '360tegong_image',
                 '360tegong_title'         => '360tegong_title',
                 '360tegong_desc'          => '360tegong_desc',
                 '360tegong_price'         => '360tegong_price',
                 '360tegong_recommend'     => '360tegong_recommend',
                 '360tegong_advertisement' => '360tegong_advertisement',
                 '360tegong_sort'          => '360tegong_sort_order',
            ),
            // src 数据做转化
            null
        );

        // 清理数据
        unset($tableMigrate);
        unset($dstTable);
    }


    /**
     * 管理员账号转化
     */
    private function convertAdminUser()
    {
        $tableMigrate = new TableMigrate();

        // 清空数据
        $dstTable = new DstDataMapper('admin_user');
        $tableMigrate->clearTable($dstTable);
        $tableMigrate->setAutoIncValue($dstTable, 1); // auto_increment 设置为 1

        //转化数据
        $tableMigrate->convertTable(
            'user', // src 表
            array(array("manager = 'Y'")), // src 表查询条件
            array('order' => 'id asc'), // src 表数据排序
            'admin_user', // dst 表
            // src 和 dst 表的列对应
            array(
                 'email'    => 'email',
                 'username' => 'user_name',
                 'password' => 'password',
                 'id'       => 'ec_salt', // 随便选了一个没用的列，目的是用 function() 来设置 ec_salt 的值
                 'city_id'  => 'action_list', // 随便选了一个没用的列，目的是用 function() 来设置 action_list 的值
            ),
            // src 数据做转化
            array(
                 'id'      => function ($ec_salt, $record) {
                         return '@4!@#$%@'; // 注意：这里是最土系统里面写死的值
                     },
                 'city_id' => function ($city_id, $record) {
                         return 'all'; // 注意：这里给所有管理员所有的权限
                     },
            )
        );

        // 清理数据
        unset($tableMigrate);
        unset($dstTable);
        unset($result);
    }


    /**
     * 注册用户账号转化
     */
    private function convertUsers()
    {
        $tableMigrate = new TableMigrate();

        // 清空数据
        $dstTable = new DstDataMapper('users');
        $tableMigrate->clearTable($dstTable);
        $tableMigrate->clearTable(new DstDataMapper('user_account'));
        $tableMigrate->clearTable(new DstDataMapper('user_address'));
        $tableMigrate->clearTable(new DstDataMapper('user_bonus'));
        $tableMigrate->clearTable(new DstDataMapper('user_feed'));
        $tableMigrate->clearTable(new DstDataMapper('user_rank'));

        //转化数据
        $tableMigrate->convertTable(
            'user', // src 表
            array(), // src 表查询条件
            //array(array("manager = 'N'")), // src 表查询条件
            array('order' => 'id asc'), // src 表数据排序
            'users', // dst 表
            // src 和 dst 表的列对应
            array(
                 'id'          => 'user_id',
                 'email'       => 'email',
                 'username'    => 'user_name',
                 'password'    => 'password',
                 'money'       => 'user_money',
                 'sns'         => 'sns_login',
                 'login_time'  => 'last_login',
                 'create_time' => 'reg_time',
                 'score'       => 'ec_salt', // 随便选了一个没用的列，目的是用 function() 来设置 ec_salt 的值
            ),
            // src 数据做转化
            array(
                 'login_time'  => function ($login_time, $record) {
                         //抓换成 GMT 时间
                         return Time::localTimeToGmTime($login_time);
                     },
                 'create_time' => function ($create_time, $record) {
                         //抓换成 GMT 时间
                         return Time::localTimeToGmTime($create_time);
                     },
                 'score'       => function ($score, $record) {
                         return '@4!@#$%@'; // 注意：这里是最土系统里面写死的值
                     },
            )
        );

        // 重设表的 AUTO_INCREMENT 值
        $tableMigrate->resetAutoIncValue($dstTable, 'user_id');

        // 清理数据
        unset($tableMigrate);
        unset($dstTable);
        unset($result);
    }

    public function run(array $params)
    {
        ini_set('memory_limit', '1024M'); // 内存 1G 应该够用了

        global $f3;

        printLog('Begin initDataSource', 'MigrateZuiTu');
        $this->initDataSource();

        //快递数据的转化
        //printLog('Begin convertZone', 'MigrateZuiTu');
        //$this->convertZone();

        //商品分类的转化
        printLog('Begin convertCategory', 'MigrateZuiTu');
        $this->convertCategory();

        //供货商账号的转化
        printLog('Begin convertSuppliers', 'MigrateZuiTu');
        $this->convertSuppliers();

        //商品的转化
        printLog('Begin convertGoods', 'MigrateZuiTu');
        $this->convertGoods();

        //商品团购信息的转化
        printLog('Begin convertGoodsTeam', 'MigrateZuiTu');
        $this->convertGoodsTeam();

        //商品推广渠道信息转化
        printLog('Begin convertGoodsPromote', 'MigrateZuiTu');
        $this->convertGoodsPromote();

        //管理员账号转化
        printLog('Begin convertAdminUser', 'MigrateZuiTu');
        $this->convertAdminUser();

        //普通用户账号转化
        printLog('Begin convertUsers', 'MigrateZuiTu');
        $this->convertUsers();

        //清除一些无关的表
        printLog('Begin clearTable', 'MigrateZuiTu');
        $this->clearTable();

        printLog('Finish Migrate', 'MigrateZuiTu');
    }

    public function help()
    {
        echo "migrate zuitu database to bzf database\r\n";
    }
}
