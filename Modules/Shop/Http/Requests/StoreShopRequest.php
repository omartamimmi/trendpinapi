<?php

namespace Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShopRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
            ],
            'status' => [
                'string',
            ],

            'category' =>[
                'array',
                'required'
            ],
            'lat'=>[
                'required_if:type,in_person',
            ],
            'lng'=>[
                'required_if:type,in_person',

            ],
            'address'=>[
                'required_if:type,in_person',

            ],
            'exact_address'=>[
                'required_if:type,in_person',

            ],
            'video'=>[],
            'gallery'=>[
                'required'
            ],
            'image_id'=>[
                'required'
            ],
            'open_hours'=>[
                'required'
            ],
            'open_hours.*.enable'  => [
                'required_if:open_hours.*.enable,1'
            ],
            'open_hours.*.hours'  => [
                'exclude_unless:open_hours.*.enable,1',
            ],
            'enable_open_hours'=>[
                'required'
            ],
            'open_hours.*.hours.*.from'  => [
                'exclude_unless:open_hours.*.enable,1',
                // 'date_format:H:i'
                'required'
            ],
            'open_hours.*.hours.*.to'  => [
                'exclude_unless:open_hours.*.enable,1',
                // 'date_format:H:i',
                // 'after_or_equal:open_hours.*.hours.*.from'
                'required'

            ],
            "enable_discount"=>[

            ],
            "discount_type"=>[
                'required_if:enable_discount,1'

            ],
            "discount_percentage"=>[
                'required_if:discount_type,percentage'
            ],
            "discount_description"=>[
                'required_if:discount_type,other'
            ],
            "discount_items"=>[
                'required_if:discount_type,items'
            ],
            'location_id'=>[

            ],
            'phone_number'=>[

            ],
            'title_ar'=>[
                'string',
                'max:255',
                'nullable'
            ],
            'description_ar' => [
                'string',
                'max:1000',
                'nullable'
            ],
            'branch_id'=>[
                'integer',
                'nullable'
            ],
            'type'=>[
                'required',
                Rule::in(['in_person', 'online']),
            ],
            'website_link'=>[], 
            'insta_link'=>[],
            'facebook_link'=>[],
            // 'tags' =>[
            //     'array',
            //     'required'
            // ],

        ];
    }

    public function bodyParameters()
    {
        return [
            'title' => [
                'description' => 'User\'s name',
                'example' => 'John Doe'
            ],
            'description' => [
                'description' => 'describe for shop',
                'example' => 'this shop is brand clothes'
            ],
            'status' => [
                'description' => 'User\'s password',
                'example' => 'aDD87.778'
            ],
            'days' => [
                'description' => 'Confirm that the user accepts the terms & conditions',
                'example' => 1
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
            'title.required' => __('shop::word.The Title field is required'),
            'title.max' => __('shop::word.The Title field cannot be more than :max characters'),
            'title.string' => __('shop::word.The Title field must be a valid input'),
            'description.required' => __('shop::word.The Description field is required'),
            'description.string' => __('shop::word.The Description field must be a valid input'),
            'description.max' => __('shop::word.The Description field cannot be more than :max characters'),
            'status.required' => __('shop::word.The Status field is required'),
            'status.sting' => __('The Status field must be a valid input'),
            'days.required' => __('shop::word.The Days & Conditions field is required'),
            'days.boolean' => __('The Days & Conditions field must be 0 or 1'),
            'lat.required' => __('shop::word.The Lat field is required'),
            'lng.required' => __('shop::word.The Lng field is required'),
            'image_id.required' => __('shop::word.The Image field is required'),
            'gallery.required' => __('shop::word.The Gallery field is required'),
            'open_hours.required' => __('shop::word.The Open Hours field is required'),
            'category.required' => __('shop::word.The category field is required'),
            'discount_type.required' => __('shop::word.The Discount Type field is required'),
        ];
    }

}
