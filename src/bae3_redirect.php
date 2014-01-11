<?php

/**
 * 因为 BAE3 环境采用 app.conf 配置，它的 rewrite 规则非常弱，很多功能都无法实现，所以我们只能使用一个 跳转 php 来操作了
 *
 * 比如我们无法配置 /manage 跳转到 /manage/ ， 所以只能自己来操作了
 *
 */

header('Location:' . $_SERVER['REQUEST_URI'] . '/');
