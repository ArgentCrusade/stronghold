<?php

namespace ArgentCrusade\Stronghold;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;

class CredentialsDetector
{
    /**
     * Detect authentication method.
     *
     * @param Request $request
     *
     * @return CredentialsDetectorResult
     */
    public function detect(Request $request)
    {
        if ($this->validates($request, ['email' => $this->emailValidationRule($request)])) {
            return new CredentialsDetectorResult(
                CredentialsDetectorResult::EMAIL,
                $request->input('email')
            );
        } elseif ($this->validates($request, ['phone' => $this->phoneValidationRule($request)])) {
            return new CredentialsDetectorResult(
                CredentialsDetectorResult::PHONE,
                $this->formatPhone($request->input('phone'))
            );
        }

        throw new \InvalidArgumentException('Unknown authentication type.');
    }

    /**
     * Email field validation rules.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function emailValidationRule(Request $request)
    {
        return 'required|string|email';
    }

    /**
     * Phone field validation rules.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function phoneValidationRule(Request $request)
    {
        return 'required|string|phone:RU';
    }

    /**
     * Determines whether the given request validates agains given rules.
     *
     * @param Request $request
     * @param array   $rules
     *
     * @return bool
     */
    protected function validates(Request $request, array $rules)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), $rules);

        return $validator->passes();
    }

    /**
     * Format given phone number into E.164 format.
     *
     * @param string $number
     *
     * @return string
     */
    protected function formatPhone(string $number)
    {
        try {
            return PhoneNumber::make($number)->formatE164();
        } catch (\Exception $e) {
            return $number;
        }
    }
}
