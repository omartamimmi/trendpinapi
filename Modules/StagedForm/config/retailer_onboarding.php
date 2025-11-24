<?php
use Illuminate\Validation\Rule;

return [
    'modules'=> [
        'business' => [
            'class' => 'Modules\Business\app\Models\Business',
            'fillable'=> [
                'retailer_name',
                'title',
                'description',
                'title_ar',
                'description_ar',
                'featured_image',
                'email',
                'license_file'
            ]
        ],
        'group' => [
            'class' => 'Modules\Business\app\Models\Group',
            'fillable'=> [
                'name',
                'business_id'
            ]
        ],
        'brand' => [
            'class' => 'Modules\Business\app\Models\Brand',
            'fillable'=> [
                'business_id',
                'name',
            ]
        ],
        'branch' => [
            'class' => 'Modules\Business\app\Models\Branch',
            'fillable'=> [
                'brand_id',
                'name',
            ]
        ],
    ],
    'steps' => [
        1 => [
            'retailer_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'retailer_logo' => 'required|string|max:255',
            'retailer_license' => 'required|string',
            'retailer_categories' => 'required|array',
            'retailer_email' => 'required|email',
            'email' => 'required|email',
            'retailer_password' => 'required|min:8',
            'retailer_phone_number' => [
                'phone:AUTO',
                'required',
                'string'
            ], 
        ],
        2 => [
            'payment_method' => [
                'required',
                Rule::in(['iban','cliq'])
            ],
            'bank_name' => 'required_if:payment_method,iban|string',
            'iban'      => 'required_if:payment_method,iban|string',
            'cliq_phone' => [
                'required_if:payment_method,cliq',
                'required_if:alias_type,phone',
                'string',
            ],
            'cliq_alias' => [
                'required_if:payment_method,cliq',
                'required_if:alias_type,alias',
                'string',
            ]
        ],
        3 => [
            'brand_type' => ['required', Rule::in(['single', 'group'])],
            'brand_name' => [
                'required_if:brand_type,single',
                'string',
                'max:255',
            ],
            'brand_logo' => [
                'required_if:brand_type,single',
                'integer',
                'max:2048',
            ],
            'group_name' => [
                'required_if:brand_type,group',
                'string',
                'max:255',
            ],
            'group_logo' => [
                'required_if:brand_type,group',
                'image',
                'max:2048',
            ],
            'brands' => [
                'required_if:brand_type,group',
                'array',
            ],
            'brands.*.name' => [
                'required_if:brand_type,group',
                'string',
                'max:255',
            ],
            'brands.*.logo' => [
                'required_if:brand_type,group',
                'image',
                'max:2048',
            ],
            'business_id' => [

            ],
            'brand_id' => []
        ],
    ],
];
  