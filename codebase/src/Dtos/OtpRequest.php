<?php

namespace App\Dtos;

class OtpRequest
{
    public function __construct(private ?string $code, private ?string $number, private ?string $webserviceref){}

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return OtpRequest
     */
    public function setCode(string $code): ?OtpRequest
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return OtpRequest
     */
    public function setNumber(?string $number): ?OtpRequest
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebserviceRef(): ?string
    {
        return $this->webserviceref;
    }

    /**
     * @param string $webserviceref
     * @return OtpRequest
     */
    public function setWebserviceRef(?string $webserviceref): ?OtpRequest
    {
        $this->webserviceref = $webserviceref;
        return $this;
    }

}
