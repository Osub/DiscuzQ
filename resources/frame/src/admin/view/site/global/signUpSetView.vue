<template>
  <div class="sign-up-set-box">

    <Card header="新用户注册：">
      <CardRow description="设置是否允许游客注册成为会员">
        <el-checkbox v-model="checked">允许新用户注册</el-checkbox>
      </CardRow>
    </Card>

    <Card header="注册模式：">
      <CardRow description="开启无感模式后，微信下将自动注册。开启手机号模式后，将用手机号的方式注册登录。开启
用户名模式后，将以用户名的方式注册登录。">
        <el-radio v-model="register_type" :label="0"> 用户名模式 </el-radio>
        <el-radio v-model="register_type" :label="1" :disabled="qcloud_sms">手机号模式</el-radio>
        <el-radio v-model="register_type" :label="2" :disabled="qcloud_wx">无感模式</el-radio>
      </CardRow>
    </Card>

    <Card header="新用户审核：">
      <CardRow description="设置新注册的用户是否需要审核">
        <el-checkbox v-model="register_validate">新用户注册审核</el-checkbox>
      </CardRow>
    </Card>

    <Card header="启用验证码：">
      <CardRow description="启用验证码需先在腾讯云设置中开启验证码服务">
        <el-checkbox v-model="register_captcha" :disabled='disabled'>新用户注册启用验证码</el-checkbox>
      </CardRow>
    </Card>

    <Card header="注册密码最小长度：">
      <CardRow description="新用户注册时密码最小长度，0或不填为不限制">
        <el-input v-model="pwdLength" type="number"  clearable></el-input>
      </CardRow>
    </Card>

    <Card header="密码字符类型：">
      <CardRow description="新用户注册时密码中必须存在所选字符类型，不选则为无限制">
        <el-checkbox-group v-model="checkList">
          <el-checkbox label=0>数字</el-checkbox>
          <el-checkbox label=1>小写字母</el-checkbox>
          <el-checkbox label=2>符号</el-checkbox>
          <el-checkbox label=3>大写字母</el-checkbox>
        </el-checkbox-group>
      </CardRow>
    </Card>

   <!-- <Card header="微信无感注册和登录">
      <CardRow description="使用微信授权信息代替帐号密码，适用于主要在微信内使用的场景。请先设置公众号接口和
扫码登录配置">
        <el-checkbox label="大写字母">开启微信授权无感注册和登录</el-checkbox>
      </CardRow>
    </Card>-->

    <Card class="footer-btn">
      <el-button type="primary" size="medium" @click="submission">提交</el-button>
    </Card>

  </div>
</template>

<script>
import signUpSetCon from '../../../controllers/site/global/signUpSetCon';
import '../../../scss/site/module/globalStyle.scss';
export default {
    name: "sign-up-set-view",
  ...signUpSetCon
}
</script>
