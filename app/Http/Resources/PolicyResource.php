<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\UrlHelper;

class PolicyResource extends JsonResource
{
    // public $status;
    public $message;
    public $resource;


    public function __construct( $message, $resource)
    {
        parent::__construct($resource);
        // $this->status  = $status;
        $this->message = $message;
    }


    public function toArray(Request $request): array
    {
        return [
            'message'   => $this->message,
            'data'      => UrlHelper::replaceLocalhostUrl(json_decode(json_encode($this->resource), true))
        ];
    }
}
