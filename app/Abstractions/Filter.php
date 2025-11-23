<?php

namespace App\Abstractions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Carbon\Carbon;

abstract class Filter
{

    /**
     * The builder instance.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    public function __construct(private array $filters)
    {
    }

    /**
     * Apply the filters on the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        if ((array_key_exists('from', $this->filters) || array_key_exists('to', $this->filters)) && method_exists($this, 'betweenDates')) {
            $data = [
                $this->filters['dateFilter'] ?? 'created_at',
                Carbon::parse($this->filters['from'] ?? 1970 - 01 - 01)->format('Y-m-d 00:00:00'),
                Carbon::parse($this->filters['to'] ?? now())->format('Y-m-d 23:59:59')
            ];
            call_user_func_array([$this, 'betweenDates'], $data);
        }

        if ((array_key_exists('min_amount', $this->filters) || array_key_exists('max_amount', $this->filters)) && method_exists($this, 'betweenTotal')) {
            $data = [
                $this->filters['min_amount'] ?? 0,
                $this->filters['max_amount'] ?? 1000000
            ];
            call_user_func_array([$this, 'betweenTotal'], $data);
        }

        foreach ($this->filters as $filterMethod => $value) {
            if (method_exists($this, $filterMethod)) {
                call_user_func_array([$this, $filterMethod], [$value]);
            }
        }

        if (method_exists($this, 'sort')) {
            $data = [
                $this->filters['dateFilter'] ?? 'created_at'
            ];
            call_user_func_array([$this, 'sort'], $data);
        }

        return $this->builder;
    }
}
