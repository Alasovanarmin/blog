<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\CategoryCreateRequest;
use App\Models\category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(CategoryCreateRequest $request)
    {
        Category::query()
            ->create([
                'name' => $request->name
            ]);

        return response([
            "message" => "Category created",
            "data" => null
        ], 201);
    }

    public function index(Request $request)
    {
        $categories = Category::query()
            ->get();

        return response([
            "message" => "Category retrieved successfully",
            "data" => $categories
        ], 200);
    }

    public function show($id)
    {
        $category = Category::query()
            ->where('id', $id)
            ->first();

        if (!$category) {
            return response([
                'message' => "Category not found",
                'data' => null
            ], 404);
        }

        return response([
            'message' => 'Category retrieved',
            'data' => $category
        ]);
    }

    public function update($id, Request $request)
    {
        $category = Category::query()
            ->where('id', $id)
            ->first();

        if (!$category) {
            return response([
                'message' => "Blog not found",
                'data' => null
            ], 404);
        }

        $category->update([
            'name' => $request->name
        ]);

        return response([
            "message" => "Category updated",
            "data" => null
        ], 200);
    }

    public function delete($id)
    {
        $category = Category::query()
            ->where('id', $id)
            ->first();

        if (!$category) {
            return response([
                'message' => 'Category not found',
                'data' => null
            ], 404);
        }
        $category->delete();

        return response([
            'message' => 'Category successfully deleted',
            'data' => null
        ], 200);
    }
}
