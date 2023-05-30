<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PushNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var PushNotification|JsonResource $this */

        $order = optional();

        if (!empty($this->title)) {
            $order = Order::with('user:id,firstname,lastname,active,password,img')->select(['id', 'user_id'])->where('id', $this->title)->first();
        }

        return [
            'id'            => $this->when($this->id,          $this->id),
            'type'          => $this->when($this->type,        $this->type),
            'title'         => $this->when($this->title,       $this->title),
            'body'          => $this->when($this->body,        $this->body),
            'data'          => $this->when($this->data,        $this->data),
            'user_id'       => $this->when($this->user_id,     $this->user_id),
            'created_at'    => $this->when($this->created_at,  $this->created_at?->format('Y-m-d H:i:s')),
            'updated_at'    => $this->when($this->updated_at,  $this->updated_at?->format('Y-m-d H:i:s')),
            'read_at'       => $this->read_at,

            'user'          => UserResource::make($this->whenLoaded('user')),
            'client'        => UserResource::make($order?->user),
            'order'         => UserResource::make($order),
        ];
    }
}
