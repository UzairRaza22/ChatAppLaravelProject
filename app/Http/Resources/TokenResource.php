<?php

namespace App\Http\Resources;

class TokenResource extends BaseResource
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
            'token' => $this->resource['token'],
            'old_token_revoked' => $this->resource['old_token_revoked'] ?? false,
        ];
    }
}
