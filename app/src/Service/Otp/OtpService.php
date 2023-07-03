<?php

namespace App\Service\Otp;

use App\Dtos\OtpRequest;
use App\Entity\OtpCode;
use App\Repository\OtpCodeRepository;

/**
 *
 */
class OtpService
{
    public function __construct(private OtpCodeRepository $otpCodeRepository)
    {
    }

    /**
     * @return string|null
     */
    public static function generate(int $len = 6) : ?string
    {
        if(empty($alphabet)) $alphabet = "123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len ; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }


    public function checkOtpValidity(?OtpCode $OtpCode): bool {
        if(!$OtpCode) return true;
        $now = new \DateTime('now');
        return !($OtpCode->getExpiredAt() > $now );
    }

    public function create(?OtpRequest $request): OtpCode {
        $otpCode = new OtpCode();
        $otpCode->setCode($request->getCode());
        $otpCode->setWebserviceReference($request->getWebserviceref());
        $otpCode->setPhone($request->getNumber());
        $otpCode->setIsExpired(false);
        $otpCode->setCreatedAt(new \DateTime('now'));
        $otpCode->setModifiedAt(new \DateTime('now'));
        $now = new \DateTime('now');
        $expireOn = $now->modify('+2 day');
        $otpCode->setExpiredAt($expireOn);
        $this->otpCodeRepository->add($otpCode, true);

        return $otpCode;
    }

    public function getByPhone(string $phone){
        return $this->otpCodeRepository->findOneBy(['phone' => $phone]);
    }

}
