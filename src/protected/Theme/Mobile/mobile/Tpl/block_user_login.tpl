<form data-ajax="false" method="POST" action="{{bzf_make_url controller='/User/Login'}}">
    <div style="padding:10px 20px;">
        <h3>用户登录</h3>
        <!-- 记录跳转回来的 URL -->
        <input type="hidden" name="returnUrl" value="">
        <label for="user_name" class="ui-hidden-accessible">用户名:</label>
        <input type="text" name="user_name" value="" placeholder="填入用户名" data-theme="a" required="required"/>
        <label for="password" class="ui-hidden-accessible">密码:</label>
        <input type="text" name="password" value="" placeholder="填入密码" data-theme="a" required="required"/>
        {{nocache}}
            <img width="150" height="50" src="{{$captchaUrl|default}}">
        {{/nocache}}
        <label for="captcha" class="ui-hidden-accessible">验证码:</label>
        <input type="text" name="captcha" value="" placeholder="填入验证码" data-theme="a" required="required"/>
        <button type="submit" data-theme="e" data-icon="check">点击登录</button>
        <a data-role="button" data-theme="f"
           data-transition="slide" data-icon="arrow-r" data-iconpos="right"
           href="{{bzf_make_url controller='/User/Register'}}">现在去注册</a>
    </div>
</form>

