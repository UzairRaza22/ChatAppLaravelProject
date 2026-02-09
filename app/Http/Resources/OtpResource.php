<?php

namespace App\Http\Resources;

class OtpResource extends BaseResource
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
            'expires_at' => $this->resource['expires_at'],
        ];
    }
}
