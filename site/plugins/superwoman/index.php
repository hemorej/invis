<?php
namespace Superwoman;
@include_once __DIR__ . '/vendor/autoload.php';

use Kirby;
use Kirby\Cms\Auth\Challenge;
use Kirby\Cms\User;
use OTPHP\TOTP;

class TotpChallenge extends Challenge
{
    /**
     * Checks whether the challenge is available
     * for the passed user and purpose
     *
     * @param \Kirby\Cms\User $user User the code will be generated for
     * @param string $mode Purpose of the code ('login', 'reset' or '2fa')
     * @return bool
     */
    public static function isAvailable(User $user, string $mode): bool
    {
        // check the plugin configuration and the requirements of your challenge
        return true;
    }

    public static function create(User $user, array $options): ?string
    {
        return null;
    }

    /**
     * Verifies the provided code against the created one
     *
     * @param \Kirby\Cms\User $user User to check the code for
     * @param string $code Code to verify
     * @return bool
     */
    public static function verify(User $user, string $code): bool
    {
        if(empty($user->secret()->value())){
            $otp = TOTP::create();
            kirby()->impersonate('kirby');
            kirby()->user($user->email())->update(['secret' => $otp->getSecret()]);

            $otp->setLabel(kirby()->site()->title() . ' Panel');
            $grCodeUri = $otp->getQrCodeUri('https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M', '[DATA]');
            echo "<img src='{$grCodeUri}'>";exit;
        }

        $otp = TOTP::create($user->secret());
        return $otp->verify($code);
    }
}

Kirby::plugin('superwoman/totp', [
    'authChallenges' => [
        'totp' => 'Superwoman\TotpChallenge'
    ],
    'translations' => [
        'en' => [
            'login.code.placeholder.totp' => '000 000',
            'login.code.text.totp' => 'Enter the code using your Authenticator app'
        ]
    ]
]);