<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::latest()->get();
        return response()->json([
            'message' => 'Items retrieved successfully',
            'data' => $items,
        ], 200);
    }

    public function store(StoreItemRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $item = Item::create($data);

        return response()->json([
            'message' => 'Item created successfully',
            'data' => $item,
        ], 201);
    }

    public function show(string $id)
    {
        $item = Item::find($id);

        if (! $item) {
            return response()->json([
                'message' => 'Item not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Item retrieved successfully',
            'data' => $item,
        ], 200);
    }

    public function update(UpdateItemRequest $request, string $id)
    {
        $item = Item::find($id);

        if (! $item) {
            return response()->json([
                'message' => 'Item not found',
            ], 404);
        }

        $item->update($request->validated());

        return response()->json([
            'message' => 'Item updated successfully',
            'data' => $item,
        ], 200);
    }

    public function destroy(string $id)
    {
        $item = Item::find($id);

        if (! $item) {
            return response()->json([
                'message' => 'Item not found',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item deleted successfully',
        ], 200);
    }
}