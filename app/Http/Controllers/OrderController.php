<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MakeOrderRequest;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\MakeOrderRequest  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(MakeOrderRequest $request): JsonResponse|ValidationException
    {
        Order::create($request->validated());

        return response()->json(['message' => 'Order created successfully.'], JsonResponse::HTTP_CREATED);
    }
}
