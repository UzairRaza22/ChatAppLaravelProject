<?php

namespace App\Http\Resources;

class EmailVerificationResource extends BaseResource
{
    /**
     * Transform the resource data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function transformData($request)
    {
        return [
            'verified' => $this->resource['verified'],
            'email' => $this->resource['email'],
        ];
    }
}
