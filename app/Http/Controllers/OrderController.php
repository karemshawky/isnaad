<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\MakeOrderRequest;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * __construct function
     *
     * @param MakeOrderService $makeOrderService
     */
    public function __construct(protected OrderService $orderService) {}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\MakeOrderRequest  $request
     * @return \Illuminate\Validation\ValidationException|\Illuminate\Http\JsonResponse
     */
    public function store(MakeOrderRequest $request): ValidationException|JsonResponse
    {
        $this->orderService->makeOrder($request->validated());

        return response()->json(['message' => 'Order created successfully.'], JsonResponse::HTTP_CREATED);
    }
}
