<?php 

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\CurlToolkit;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class Captchas extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $data = $request->request->all();

        if (empty($data['type'])) {
            return $this->error('500', '没有type字段');
        }
        if (empty($data['mobile'])) {
            return $this->error('500', '手机号为空');
        }

        $result = $this->getSmsService()->sendVerifySms('sms_bind', $data['mobile'], 0);
            
        $user = $this->getCurrentUser();
        $newToken = $this->getTokenService()->makeToken($data['type'], array(
            'times'    => 5,
            'duration' => 60 * 2,
            'userId'   => $user['id'],
            'data'     => array(
                'captcha_code' => $result['captcha_code'],
                'mobile'       => $data['mobile']
            )
        ));

        return array(
            'code'  => 0,
            'token' => $newToken
        );
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getSmsService()
    {
        return $this->getServiceKernel()->createService('Sms.SmsService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}