<?php

namespace Modules\User\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\User\Rules\NotUrl;
use Modules\User\Rules\IsCurator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


class SendNotificationBasedLocation extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'token' => [
            //     'required'
            // ],
            'lat'=>[
                'required'
            ],
            'lng'=>[
                'required'
            ]
        ];
    }

    public function bodyParameters()
    {
        return [
            'first_name' => [
                'description' => 'User\'s first name',
                'example' => 'John'
            ],
            'last_name' => [
                'description' => 'User\'s last name',
                'example' => 'Doe'
            ],
            'email' => [
                'description' => 'User\'s email',
                'example' => 'john.doe@example.com'
            ],
            'phone' => [
                'description' => 'User\'s phone number',
                'example' => '+27113456789'
            ],
            'vaccinated' => [
                'description' => 'Confirm that the use is vaccinated or not',
                'example' => 'no'
            ],
            'vaccination_license' => [
                'description' => 'User\'s vaccination license',
                'example' => 'http://lorempixel.com/640/480/'
            ],
            'payout_accounts' => [
                'description' => 'User\'s Payout information',
                'example' => '[{"paypal": "", "bank_transfer": ""}]'
            ],
            'avatar_id' => [
                'description' => 'User\'s avatar image id',
                'example' => 1
            ],
            'sms_notifcation' => [
                'description' => 'Confirm whether the user wants to receive sms notifications or not',
                'example' => 0
            ],
            'address' => [
                'description' => 'User\'s address',
                'example' => '8888 South Vista'
            ],
            'address2' => [
                'description' => 'User\'s secondary address',
                'example' => 'Suite 961'
            ],
            'birthday' => [
                'description' => 'User\'s birthday',
                'example' => '1996-09-09'
            ],
            'city' => [
                'description' => 'User\'s city',
                'example' => 'West Judge'
            ],
            'state' => [
                'description' => 'User\'s state',
                'example' => 'NewMexico'
            ],
            'country' => [
                'description' => 'User\'s country',
                'example' => 'Falkland Islands (Malvinas)'
            ],
            'zip_code' => [
                'description' => 'User\'s zip code',
                'example' => '95473'
            ],
            'bio' => [
                'description' => 'User\'s bio',
                'example' => 'Fuga totam reiciendis qui architecto fugiat nemo. Consequatur recusandae qui cupiditate eos quod.'
            ],
            'business_name' => [
                'description' => 'User\'s business name',
                'example' => 'ViaVii'
            ],
            'time_zone' => [
                'description' => 'User\'s avatar image id',
                'example' => 'Europe/Paris'
            ],
            'lang' => [
                'description' => 'Page Language (Default is "en")',
                'example' => 'ar'
            ]
        ];
    }

    /**
     * Get the validation messages that apply to the rule.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'first_name.required' => __('The First Name field is required'),
            'first_name.max' => __('The First Name field cannot be more than :max characters'),
            'first_name.string' => __('The First Name field must be a valid input'),
            'last_name.required' => __('The Last Name field is required'),
            'last_name.max' => __('The Last Name field cannot be more than :max characters'),
            'last_name.string' => __('The Last Name field must be a valid input'),
            'email.required' => __('The Email field is required'),
            'email.email' => __('The Email is Invalid'),
            'email.max' => __('The Email field cannot be more than :max characters'),
            'email.unique' => __('The Email has already been taken'),
            'phone.required' => __('The Phone field is required'),
            'phone.phone' => __('The Phone must be a valid phone number'),
            'vaccinated.string' => __('The vaccinated field must be yes or no'),
            'vaccination_license.required_if' => __('The vaccination license field is required when vaccinated is yes'),
            'payout_accounts.array' => __('The Payouts Accounts field must be an array'),
            'avatar_id.required_if' => __('The Avatar field is required'),
            'sms_notifcation.boolean' => __('The sms notification field must be 0 or 1'),
            'address.string' => __('The Address field must be a valid input'),
            'address.max' => __('The Address field cannot be more than :max characters'),
            'address2.string' => __('The Address2 field must be a valid input'),
            'address2.max' => __('The Address2 field cannot be more than :max characters'),
            'birthday.date' => __('The Birthday field must be a date'),
            'birthday.date_format' => __('The Birthday field format must be "Y-m-d"'),
            'city.string' => __('The City field must be a valid input'),
            'city.max' => __('The City field cannot be more than :max characters'),
            'state.string' => __('The State field must be a valid input'),
            'state.max' => __('The State field cannot be more than :max characters'),
            'country.string' => __('The Country field must be a valid input'),
            'country.max' => __('The Country field cannot be more than :max characters'),
            'zip_code.string' => __('The Zip Code field must be a valid input'),
            'zip_code.max' => __('The Zip Code field cannot be more than :max characters'),
            'bio.string' => __('The Bio field must be a valid input'),
            'bio.max' => __('The Bio field cannot be more than :max characters'),
            'business_name.string' => __('The Business Name field must be a valid input'),
            'business_name.max' => __('The Business Name field cannot be more than :max characters'),
            'time_zone.string' => __('The Time Zone field must be a valid input'),
            'time_zone.max' => __('The Time Zone field cannot be more than :max characters'),
            'lang.string' => __('The Lang field must be a valid input'),
            'lang.max' => __('The Lang field cannot be more than :max characters'),
        ];
    }
}
