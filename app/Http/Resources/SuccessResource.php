<?php

namespace App\Http\Resources;

class SuccessResource extends BaseResource
{
    /**
     * Transform the resource into a success response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function transformData($request)
    {
        return [
            'message' => $this->resource['message'] ?? 'Operation completed successfully',
        ];
    }
}
