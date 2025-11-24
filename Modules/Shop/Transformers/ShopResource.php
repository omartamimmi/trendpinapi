<?php

namespace Modules\Shop\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $currentDay = intval(date('N'));
        $currentTime = Carbon::now()->setTimezone('Europe/Oslo');
        $openStatus = false;
        $openHours =  null;
        $enableDiscount =  null;
        $discountType =  null;
        $discount =  null;
        $checkInterest = DB::table('user_has_interest_in_shops')
        ->where('user_id', Auth::guard('sanctum')->id())
        ->where('shop_id', $this['shop']->id)->get();

        $checkedToNotify =false;
        if(!$checkInterest->isEmpty()){
            $checkedToNotify =true;
        }

        if(!empty($this['shop']->meta)){
             $timeRange = $this['shop']->meta->open_hours;
            
                if(isset($this['shop']->meta->open_hours[$currentDay])){
                    $timeRange = $timeRange[$currentDay]['hours'][0];
                    $timeRangeStart = Carbon::now()->setTimezone('Europe/Oslo')->setTimeFromTimeString($timeRange['from']);
                    $timeRangeEnd = Carbon::now()->setTimezone('Europe/Oslo')->setTimeFromTimeString($timeRange['to']);
                    if (str_contains($timeRangeEnd->format('g:i A'), 'AM')){
                        $timeRangeEnd = $timeRangeEnd->addDay(1);
                    }

                    // dd($timeRangeEnd->toDateTimeString(), $timeRangeStart->toDateTimeString(), $timeRange['to']);
                    // $timeRangeEnd = Carbon::createFromFormat('H:i', $timeRange['to'])->format('g:i');
                    // $timeRangeStart = Carbon::createFromFormat('H:i', $timeRange['from'])->format('g:i');
                    // $isOpen = 
                    // Carbon::now()->dayOfWeek === $currentDay &&
                    // Carbon::now()->between(
                    // $timeRangeStart,
                    // $timeRangeEnd
                    // );
                    // $openStatus = $isOpen;
                    // if ($currentTime->greaterThanOrEqualTo($openingTime) && $currentTime->lessThan($closingTime)) {
                        // dd($currentTime->greaterThanOrEqualTo($timeRangeStart) && $currentTime->lessThan($timeRangeEnd));
                    if ($timeRangeEnd < $timeRangeStart) {
                        // Adjust the closing time to the next day
                        $timeRangeEnd->addDay();
                    }

                    if( $currentTime->greaterThanOrEqualTo($timeRangeStart->subHours()) && $currentTime->lessThan($timeRangeEnd)){
                        $openStatus = true;
                    }
                }

                

                $openHours = ($this['shop']->meta->open_hours) ? json_encode($this['shop']->meta->open_hours) : null;
                $enableDiscount = ($this['shop']->meta->enable_discount) ? $this['shop']->meta->enable_discount : null;
                $discountType = ($this['shop']->meta->discount_type) ? $this['shop']->meta->discount_type : null;
                $discount = ($this['shop']->meta->discount) ? $this['shop']->meta->discount : null;
        }

        $parsedUrl = parse_url($this['shop']->featured_image);
        $newUrl = env('APP_URL');

        return [
                'id' => $this['shop']->id,
                'title' => $this['shop']->title,
                'title_ar'=>$this['shop']->title_ar,
                'description'=>$this['shop']->description,
                'description_ar'=>$this['shop']->description_ar,
                'status'=>$this['shop']->status,
                'featured_image'=>!empty($parsedUrl) ? $newUrl. $parsedUrl['path'] : null,
                'open_status'=>$openStatus,
                'galleryUrls'=>$this['shop']->getGallery(),
                'enable_discount'=>$enableDiscount,
                'discount_type'=>$discountType,
                'discount'=>$discount,
                'open_hours'=>$openHours,
                'categories'=> $this['categories'],
                'location'=>$this['shop']->location,
                'gallery'=>$this['shop']->gallery,
                'image_id'=>$this['shop']->image_id,
                'check_to_notify' =>$checkedToNotify,
                'phone_number' => $this['shop']->phone_number,
                'is_main_branch' => $this['shop']->is_main_branch,
                'main_branch_id' => $this['shop']->main_branch_id,
                'branches' => $this['branches'],
                'type'=>$this['shop']->type,
                'website_link'=>$this['shop']->website_link,
                'insta_link'=>$this['shop']->insta_link,
                'facebook_link'=>$this['shop']->facebook_link,
                'tag_shops' => $this['shop']->tag_shops
        ];
    }
}
