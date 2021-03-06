<?php

/**
 * Discuz & Tencent Cloud
 * This is NOT a freeware, use is subject to license terms
 */

namespace App\Commands\Users;

use App\Censor\Censor;
use App\Events\Users\Registered;
use App\Events\Users\Saving;
use App\Exceptions\TranslatorException;
use App\Models\User;
use App\Validators\UserValidator;
use Carbon\Carbon;
use Discuz\Contracts\Setting\SettingsRepository;
use Discuz\Foundation\EventsDispatchTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class RegisterUser
{
    use EventsDispatchTrait;

    /**
     * The user performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * The attributes of the new user.
     *
     * @var array
     */
    public $data;

    /**
     * @param User $actor The user performing the action.
     * @param array $data The attributes of the new user.
     */
    public function __construct(User $actor, array $data)
    {
        $this->actor = $actor;
        $this->data = $data;
    }

    /**
     * @param Dispatcher $events
     * @param Censor $censor
     * @param SettingsRepository $settings
     * @param UserValidator $validator
     * @return User
     * @throws ValidationException
     * @throws TranslatorException
     */
    public function handle(Dispatcher $events, Censor $censor, SettingsRepository $settings, UserValidator $validator)
    {
        $this->events = $events;

        $password = Arr::get($this->data, 'password');
        $password_confirmation = Arr::get($this->data, 'password_confirmation');

        // 敏感词校验
        $censor->checkText(Arr::get($this->data, 'username'), 'username');

        // 注册原因
        if ($settings->get('register_validate', 'default', false)) {
            if (!Arr::has($this->data, 'register_reason')) {
                throw new TranslatorException('setting_fill_register_reason');
            }
        }

        $user = User::register(Arr::only($this->data, ['username', 'password', 'register_ip', 'register_port', 'register_reason']));

        // 注册验证码(无感模式不走验证码，开启也不走)
        $captcha = '';  // 默认为空将不走验证
        if ((bool)$settings->get('register_captcha') &&
            (bool)$settings->get('qcloud_captcha', 'qcloud') &&
            ($settings->get('register_type', 'default') != 2)) {
            $captcha = [
                Arr::get($this->data, 'captcha_ticket', ''),
                Arr::get($this->data, 'captcha_rand_str', ''),
                Arr::get($this->data, 'register_ip', ''),
            ];
        }

        // 付费模式，默认注册时即到期
        if ($settings->get('site_mode') == 'pay') {
            $user->expired_at = Carbon::now();
        }
        // 审核模式，设置注册为审核状态
        if ($settings->get('register_validate') || $censor->isMod) {
            $user->status = 2;
        }

        $this->events->dispatch(
            new Saving($user, $this->actor, $this->data)
        );

        // 密码为空的时候，不验证密码，允许创建密码为空的用户(但无法登录，只能用其它方法登录)
        $attrs_to_validate = array_merge($user->getAttributes(), compact('password', 'password_confirmation', 'captcha'));
        if ($password === '') {
            unset($attrs_to_validate['password']);
        }
        $validator->valid($attrs_to_validate);

        $user->save();

        $user->raise(new Registered($user, $this->actor, $this->data));

        $this->dispatchEventsFor($user, $this->actor);

        return $user;
    }
}
