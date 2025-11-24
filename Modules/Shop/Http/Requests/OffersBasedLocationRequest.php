<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OffersBasedLocationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() :array
    {
        return [

            'category_ids' => [
                "nullable",
                "array"
            ],
            'tag_ids' => [

            ],
           'best_offer'=>[

           ],
           'lat_start'=>[],
           'lng_start'=>[],
           'lat_end'=>[],
           'lng_end'=>[],
        ];
    }

    public function bodyParameters()
    {
        return [
            'category_ids' => [
                'description' => 'Categories',
                'example' => 'food'
            ],
            'best_offer' => [
                'description' => 'Best Offers',
                'example' => '1'
            ],
            'lat' => [
                'description' => 'Latitude',
                'example' => '12.333'
            ],
            'lng' => [
                'description' => 'Longitude',
                'example' => '12.333'
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
            'lat.required' => __('validation.lat.required'),
            'lat.string' => __('validation.lat.string'),
            'lng.required' => __('validation.lat.required'),
            'lng.string' => __('validation.lat.string'),
            'best_offer.required' => __('validation.best_offer.required'),
            'best_offer.integer' => __('validation.best_offer.integer'),
        ];
    }

}
