{{extends file='misc_layout.tpl'}}
{{block name=misc_main_body}}

    <!-- 用 JS 设置页面的导航菜单 -->
    <script type="text/javascript">
        window.bz_set_nav_status.push(function ($) {
            $("#misc_tabbar li:has(a[href='{{bzf_make_url controller='/Misc/Cache'}}'])").addClass("active");
        });
        window.bz_set_breadcrumb_status.push({index: 1, text: '缓存管理', link: window.location.href});
    </script>
    <!-- 页面主体内容 -->
    <div class="row">
        <h4>缓存管理</h4>

        <!-- 快递公司列表 -->
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th width="10%">缓存项目</th>
                <th>操作参数</th>
                <th width="10%">操作</th>
            </tr>
            </thead>
            <tbody>

            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Cache/clearAllCache">
                <tr>
                    <td>
                    <span rel="tooltip" data-placement="top"
                          data-title="所有数据缓存和页面缓存">全部缓存</span>
                    </td>
                    <td>&nbsp;</td>
                    <td>
                        <button type="submit" class="btn btn-small btn-success">清除</button>
                    </td>
                </tr>
            </form>

            <tr>
                <td colspan="3" class="well">&nbsp;</td>
            </tr>

            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Cache/clearDataCache">
                <tr>
                    <td>
                    <span rel="tooltip" data-placement="top"
                          data-title="memcache/apc/本地文件 中的缓存">数据缓存</span>
                    </td>
                    <td>&nbsp;</td>
                    <td>
                        <button type="submit" class="btn btn-small btn-success">清除</button>
                    </td>
                </tr>
            </form>

            <tr>
                <td colspan="3" class="well">&nbsp;</td>
            </tr>

            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Cache/clearHomePage">
                <tr>
                    <td>
                    <span rel="tooltip" data-placement="top"
                          data-title="网站首页缓存">网站首页</span>
                    </td>
                    <td>&nbsp;</td>
                    <td>
                        <button type="submit" class="btn btn-small btn-success">清除</button>
                    </td>
                </tr>
            </form>

            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Cache/clearGoodsCategory">
                <tr>
                    <td>
                    <span rel="tooltip" data-placement="top"
                          data-title="商品的树形分类页面缓存">商品类目</span>
                    </td>
                    <td>&nbsp;</td>
                    <td>
                        <button type="submit" class="btn btn-small btn-success">清除</button>
                    </td>
                </tr>
            </form>

            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Cache/clearGoodsCacheById">
                <tr>
                    <td>
                    <span rel="tooltip" data-placement="top"
                          data-title="商品详情页面的缓存">商品详情</span>
                    </td>
                    <td>
                        <div class="control-group">
                            <div class="controls">
                                <span class="input-label">商品ID</span>
                                <input class="span1" type="text" pattern="[0-9]+" name="goods_id"
                                       data-validation-pattern-message="商品ID应该是全数字"
                                       data-validation-required-message="商品ID不能为空"/>
                            </div>
                        </div>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-small btn-success">清除</button>
                    </td>
                </tr>
            </form>

            <form class="form-horizontal form-horizontal-inline" method="POST"
                  action="Cache/clearArticleCacheById">
                <tr>
                    <td>
                    <span rel="tooltip" data-placement="top"
                          data-title="文章显示页面的缓存">文章显示</span>
                    </td>
                    <td>
                        <div class="control-group">
                            <div class="controls">
                                <span class="input-label">文章ID</span>
                                <input class="span1" type="text" pattern="[0-9]+" name="article_id"
                                       data-validation-pattern-message="文章ID应该是全数字"
                                       data-validation-required-message="文章ID不能为空"/>
                            </div>
                        </div>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-small btn-success">清除</button>
                    </td>
                </tr>
            </form>

            </tbody>
        </table>
    </div>
    <!-- /页面主体内容 -->

{{/block}}