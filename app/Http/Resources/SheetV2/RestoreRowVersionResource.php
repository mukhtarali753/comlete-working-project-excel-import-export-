<?php

namespace App\Http\Resources\SheetV2;

use Illuminate\Http\Resources\Json\JsonResource;

class RestoreRowVersionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'message' => $this->message,
            'row_id' => $this->row_id,
            'restored_version' => $this->restored_version,
        ];
    }
}











