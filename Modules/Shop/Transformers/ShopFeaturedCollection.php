<?php

namespace Modules\Shop\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ShopFeaturedCollection extends ResourceCollection
{
    public static $wrap = 'shops';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($shop) {
            $currentDay = intval(date('N'));
            $currentTime = Carbon::now()->setTimezone('Europe/Oslo')->format('g:i');
            $openStatus = false;
            $openHours =  null;
            $enableDiscount =  null;
            $discountType =  null;
            $discount =  null;
            if(!empty($shop->meta)){
                $timeRange = $shop->meta->open_hours;
                if(isset($shop->meta->open_hours[$currentDay]) && isset($timeRange[$currentDay]['hours'])){
                    $timeRange = $timeRange[$currentDay]['hours'][0];
                    if(!empty($timeRange['from']) && !empty($timeRange['to'])){
                //         $timeRangeStart = Carbon::now()->setTimeFromTimeString($timeRange['from']);
                //         $timeRangeEnd = Carbon::now()->setTimeFromTimeString($timeRange['to']);
                //         if (str_contains($timeRangeEnd->format('g:i A'), 'AM')){
                //             $timeRangeEnd = $timeRangeEnd->addDay();
                //         }
                //         $isOpen = 
                //         Carbon::now()->dayOfWeek === $currentDay &&
                //         Carbon::now()->between(
                //             $timeRangeStart->format('H:i:s'),
                //             $timeRangeEnd->format('H:i:s')
                //         );
                //         // dd($timeRangeStart->format('H:i:s'), $timeRangeEnd->format('H:i:s'));
                //         $openStatus = $isOpen;

                //         // if( $timeRangeStart <= $currentTime && $timeRangeEnd < $currentTime){
                //         //     $openStatus = true;
                //         // }
                        $currentTime = Carbon::now();

                        // Convert the shop's opening and closing times to Carbon instances
                        $openingTime = Carbon::now()->setTimezone('Europe/Oslo')->setTimeFromTimeString($timeRange['from']);
                        $closingTime = Carbon::now()->setTimezone('Europe/Oslo')->setTimeFromTimeString($timeRange['to']);

                        // Check if the closing time is on the next day (e.g., closing at 2 AM)
                        if ($closingTime < $openingTime) {
                            // Adjust the closing time to the next day
                            $closingTime->addDay();
                        }

                        // Check if the current time is within the opening and adjusted closing hours
                        if ($currentTime->greaterThanOrEqualTo($openingTime->subHours()) && $currentTime->lessThan($closingTime)) {
                            $openStatus = true;
                        } else {
                                                    $openStatus = false;
                        }
                    }
                }

                 $openHours = ($shop->meta->open_hours) ? json_encode($shop->meta->open_hours) : null;
                 $enableDiscount = ($shop->meta->enable_discount) ? $shop->meta->enable_discount : null;
                 $discountType = ($shop->meta->discount_type) ? $shop->meta->discount_type : null;
                 $discount = ($shop->meta->discount) ? $shop->meta->discount : null;
            }

            $parsedUrl = parse_url($shop->featured_image);
            $newUrl = env('APP_URL');
            return [
                'id' => $shop->id,
                'featured' => $shop->featured,
                'title' => $shop->title,
                'title_ar'=>$shop->title_ar,
                'description'=>$shop->description,
                'description_ar'=>$shop->description_ar,
                'status'=>$shop->status,
                'featured_image'=>!empty($parsedUrl) ? $newUrl. $parsedUrl['path'] : null,
                'open_status'=>$openStatus,
                'galleryUrls'=>$shop->gallery,
                'enable_discount'=>$enableDiscount,
                'discount_type'=>$discountType,
                'discount'=>$discount,
                'open_hours'=>$openHours,
                'location'=>$shop->location,
                'is_main_branch'=>$shop->is_main_branch,
                'main_branch_id'=>$shop->main_branch_id,
                'type'=>$shop->type,
                'website_link'=>$shop->website_link,
                'insta_link'=>$shop->insta_link,
                'facebook_link'=>$shop->facebook_link,
            ];
        });
    }
}
