<?php

namespace Modules\BankOffer\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\CardType;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\BankOffer\app\Models\BankOfferBrand;
use Modules\Business\app\Models\Brand;

class BankOfferDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating demo banks...');

        // Create demo banks
        $banks = [
            [
                'name' => 'Arab Bank',
                'name_ar' => 'البنك العربي',
                'description' => 'One of the largest banks in Jordan',
                'status' => 'active',
            ],
            [
                'name' => 'Housing Bank',
                'name_ar' => 'بنك الإسكان',
                'description' => 'Housing Bank for Trade and Finance',
                'status' => 'active',
            ],
            [
                'name' => 'Jordan Ahli Bank',
                'name_ar' => 'البنك الأهلي الأردني',
                'description' => 'Jordan Ahli Bank',
                'status' => 'active',
            ],
            [
                'name' => 'Cairo Amman Bank',
                'name_ar' => 'بنك القاهرة عمان',
                'description' => 'Cairo Amman Bank',
                'status' => 'active',
            ],
            [
                'name' => 'Bank of Jordan',
                'name_ar' => 'بنك الأردن',
                'description' => 'Bank of Jordan',
                'status' => 'active',
            ],
        ];

        $createdBanks = [];
        foreach ($banks as $bankData) {
            $createdBanks[] = Bank::firstOrCreate(
                ['name' => $bankData['name']],
                $bankData
            );
        }

        $this->command->info('Created ' . count($createdBanks) . ' banks');

        // Create card types for each bank
        $this->command->info('Creating demo card types...');

        $cardTypes = [];
        $cardColors = [
            'from-yellow-500 to-amber-600',      // Gold
            'from-gray-700 to-gray-900',         // Platinum
            'from-blue-600 to-blue-700',         // Classic Blue
            'from-green-500 to-green-600',       // Green
            'from-rose-400 to-pink-500',         // Rose Gold
        ];

        foreach ($createdBanks as $index => $bank) {
            // Visa Gold
            $cardTypes[] = CardType::firstOrCreate(
                ['bank_id' => $bank->id, 'name' => 'Visa Gold'],
                [
                    'name_ar' => 'فيزا ذهبية',
                    'card_network' => 'visa',
                    'bin_prefixes' => ['4' . str_pad($bank->id, 5, '0', STR_PAD_LEFT)],
                    'card_color' => $cardColors[0],
                    'status' => 'active',
                ]
            );

            // Visa Platinum
            $cardTypes[] = CardType::firstOrCreate(
                ['bank_id' => $bank->id, 'name' => 'Visa Platinum'],
                [
                    'name_ar' => 'فيزا بلاتينية',
                    'card_network' => 'visa',
                    'bin_prefixes' => ['4' . str_pad($bank->id + 10, 5, '0', STR_PAD_LEFT)],
                    'card_color' => $cardColors[1],
                    'status' => 'active',
                ]
            );

            // Mastercard
            $cardTypes[] = CardType::firstOrCreate(
                ['bank_id' => $bank->id, 'name' => 'Mastercard World'],
                [
                    'name_ar' => 'ماستركارد وورلد',
                    'card_network' => 'mastercard',
                    'bin_prefixes' => ['5' . str_pad($bank->id, 5, '0', STR_PAD_LEFT)],
                    'card_color' => $cardColors[$index % count($cardColors)],
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('Created ' . count($cardTypes) . ' card types');

        // Get existing brands (any status)
        $brands = Brand::limit(10)->get();

        if ($brands->isEmpty()) {
            $this->command->warn('No brands found. Skipping offer creation.');
            return;
        }

        $this->command->info('Found ' . $brands->count() . ' brands for offers');

        // Create demo offers
        $this->command->info('Creating demo offers...');

        $offerTemplates = [
            [
                'title' => '20% Off on All Purchases',
                'title_ar' => '20% خصم على جميع المشتريات',
                'description' => 'Get 20% off when you pay with your bank card',
                'description_ar' => 'احصل على خصم 20% عند الدفع ببطاقتك البنكية',
                'offer_type' => 'percentage',
                'offer_value' => 20,
                'min_purchase_amount' => 10,
                'max_discount_amount' => 50,
            ],
            [
                'title' => '5 JOD Off on Orders Above 30 JOD',
                'title_ar' => '5 دينار خصم على الطلبات فوق 30 دينار',
                'description' => 'Flat 5 JOD discount on orders above 30 JOD',
                'description_ar' => 'خصم ثابت 5 دينار على الطلبات فوق 30 دينار',
                'offer_type' => 'fixed',
                'offer_value' => 5,
                'min_purchase_amount' => 30,
                'max_discount_amount' => null,
            ],
            [
                'title' => '10% Cashback',
                'title_ar' => '10% استرداد نقدي',
                'description' => 'Earn 10% cashback on your purchase',
                'description_ar' => 'احصل على استرداد نقدي 10% على مشترياتك',
                'offer_type' => 'cashback',
                'offer_value' => 10,
                'min_purchase_amount' => null,
                'max_discount_amount' => 20,
            ],
            [
                'title' => '15% Weekend Special',
                'title_ar' => '15% عرض نهاية الأسبوع',
                'description' => 'Special weekend discount for cardholders',
                'description_ar' => 'خصم خاص لنهاية الأسبوع لحاملي البطاقات',
                'offer_type' => 'percentage',
                'offer_value' => 15,
                'min_purchase_amount' => 20,
                'max_discount_amount' => 30,
            ],
        ];

        $offersCreated = 0;
        $brandsLinked = 0;

        foreach ($createdBanks as $bank) {
            // Get card types for this bank
            $bankCardTypes = CardType::where('bank_id', $bank->id)->get();

            foreach ($offerTemplates as $index => $template) {
                // Create offer for random card type or all cards
                $cardType = $index % 2 === 0 ? $bankCardTypes->random() : null;

                $offer = BankOffer::create([
                    'bank_id' => $bank->id,
                    'card_type_id' => $cardType?->id,
                    'title' => $bank->name . ' - ' . $template['title'],
                    'title_ar' => $bank->name_ar . ' - ' . $template['title_ar'],
                    'description' => $template['description'],
                    'description_ar' => $template['description_ar'],
                    'offer_type' => $template['offer_type'],
                    'offer_value' => $template['offer_value'],
                    'min_purchase_amount' => $template['min_purchase_amount'],
                    'max_discount_amount' => $template['max_discount_amount'],
                    'start_date' => now()->subDays(rand(1, 10)),
                    'end_date' => now()->addDays(rand(30, 90)),
                    'terms' => "Valid for {$bank->name} cardholders only. Cannot be combined with other offers.",
                    'terms_ar' => "صالح لحاملي بطاقات {$bank->name_ar} فقط. لا يمكن دمجه مع عروض أخرى.",
                    'redemption_type' => ['show_only', 'qr_code', 'in_app'][rand(0, 2)],
                    'status' => 'active',
                    'total_claims' => rand(0, 100),
                    'max_claims' => rand(500, 2000),
                ]);

                $offersCreated++;

                // Link random brands to this offer
                $selectedBrands = $brands->random(min(3, $brands->count()));

                foreach ($selectedBrands as $brand) {
                    // Get brand's branches
                    $branchIds = [];
                    if ($brand->branches && $brand->branches->count() > 0) {
                        $branchIds = $brand->branches->random(min(2, $brand->branches->count()))->pluck('id')->toArray();
                    }

                    BankOfferBrand::firstOrCreate(
                        [
                            'bank_offer_id' => $offer->id,
                            'brand_id' => $brand->id,
                        ],
                        [
                            'all_branches' => empty($branchIds),
                            'branch_ids' => empty($branchIds) ? null : $branchIds,
                            'status' => 'approved',
                            'requested_at' => now()->subDays(rand(5, 20)),
                            'approved_at' => now()->subDays(rand(1, 4)),
                        ]
                    );

                    $brandsLinked++;
                }
            }
        }

        $this->command->info("Created {$offersCreated} offers");
        $this->command->info("Linked {$brandsLinked} brand-offer relationships");
        $this->command->info('Demo data seeding complete!');
    }
}
