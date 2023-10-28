import FriendlyCaptchaPlugin from './plugin/captcha/friendly-captcha.plugin';

const PluginManager = window.PluginManager;

if (window.friendlyCaptchaActive) {
    PluginManager.register('FriendlyCaptcha', FriendlyCaptchaPlugin, '[data-friendly-captcha]');
}
