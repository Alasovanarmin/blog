<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\BlogCreateRequest;
use App\Http\Requests\Dashboard\BlogUpdateRequest;
use App\Models\Blog;
use App\Models\BlogPhoto;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function store(BlogCreateRequest $request)
    {
        $blog = Blog::query()
            ->create([
                'name' => $request->name,
                'body' => $request->body,
                'category_id' => $request->category_id,
                'status' => $request->status ?? 1,
            ]);

        foreach ($request->photos ?? [] as $photo) {
            $fileName = time() . rand(1, 1000) . '.' . $photo->extension();
            $fileNameWithUpload = 'storage/uploads/blogs/' . $fileName;

            $photo->storeAs('public/uploads/blogs/', $fileName);

            BlogPhoto::query()
                ->create([
                    'blog_id' => $blog->id,
                    'photo' => $fileNameWithUpload
                ]);
        }
        return response([
            "message" => "Blog created",
            "data" => null
        ],201);
    }

    public function index(Request $request)
    {
        $blogs = Blog::query()
            ->from('blogs as b')
            ->select([
                'b.id',
                'b.name',
                'b.body',
                'b.created_at',
                'b.status',
                'c.name as category_name'
            ])
            ->leftJoin('categories as c','c.id','b.category_id')
            ->whereNull('deleted_at')
            ->with('photos')
            ->orderByDesc('created_at');

        if ($request->name == !null) {
            $blogs = $blogs->where('b.name', 'like', "%$request->name%");
        }

        if($request->body == !null) {
            $blogs = $blogs->where('b.body','like',"%$request->body%");
        }

        if($request->status == !null) {
            $blogs = $blogs->where('b.status',$request->status);
        }

        if($request->category_id == !null) {
            $blogs = $blogs->where('b.category_id',$request->category_id);
        }


        $blogs = $blogs->get();

        return response([
            "message" => "Blogs retrieved successfully",
            "data" => [
                'blogs' => $blogs
            ]
        ],200);
    }

    public function show($id)
    {
        $blog = Blog::query()
            ->from('blogs as b')
            ->select([
                'b.id',
                'b.name',
                'b.body',
                'b.created_at',
                'b.status',
                'c.name as category_name',
                'c.id as category_id'
            ])
            ->leftJoin('categories as c','c.id','b.category_id')
            ->whereNull('deleted_at')
            ->where('b.id',$id)
            ->with('photos')
            ->first();

        if(!$blog){
            return response([
                'message' => "Blog not found",
                'data' => null
            ], 404);
        }

        return response([
            'message' => 'Blog retrieved',
            'data' => $blog
        ], 200);
    }

    public function update($id,BlogUpdateRequest $request)
    {
        $blog = Blog::query()
            ->where('id', $id)
            ->first();

        if (!$blog) {
            return response([
                'message' => "Blog not found",
                'data' => null
            ], 404);
        }

        $blog->update([
            'name' => $request->name,
            'body' => $request->body,
            'category_id' => $request->category_id,
            'status' => $request->status ?? 1,
        ]);

        BlogPhoto::query()
            ->where('blog_id', $id)
            ->delete();

        foreach ($request->photos ?? [] as $photo) {
            $fileName = time() . rand(1, 1000) . '.' . $photo->extension();
            $fileNameWithUpload = 'storage/uploads/blogs/' . $fileName;

            $photo->storeAs('public/uploads/blogs/', $fileName);

            BlogPhoto::query()
                ->create([
                    'blog_id' => $blog->id,
                    'photo' => $fileNameWithUpload
                ]);
            return response([
                "message" => "Blog updated",
                "data" => null
            ], 200);
        }
    }

    public function delete($id)
    {
        $blog = Blog::query()
            ->where('id',$id)
            ->first();

        if(!$blog){
            return response([
                'message' => 'Blog not found',
                'data' => null
            ],404);
        }

        $blog->update(['deleted_at' => now()]);

        #Bir basa silmek istesek
        #$blog->delete();

        return response([
            'message' => 'Blog deleted',
            'data' => null
        ],200);
    }
}
