<?php

namespace App\Traits;

trait RequestHandlerTrait
{
    public function handleFileStoreRequest($request)
    {
        return $request->validated();
    }

    public function handleFileUpdateRequest($request)
    {
        return $request->validated();
    }

    public function handleSheetStoreRequest($request)
    {
        return $request->validated();
    }

    public function handleSheetUpdateRequest($request)
    {
        return $request->validated();
    }
}













