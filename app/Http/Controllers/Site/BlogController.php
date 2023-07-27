<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\BlogCreateRequest;
use App\Http\Requests\Dashboard\BlogUpdateRequest;
use App\Models\Blog;
use App\Models\BlogLike;
use App\Models\BlogPhoto;
use App\Models\BlogStar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    public function index(Request $request)
    {

        $limit = $request->limit ?? 10; // Max number of data in 1 page
        $page = $request->page ?? 1; // number of page

        $offset = $limit * ($page-1);

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
            ->orderByDesc('id');

        if ($request->name == !null) {
            $blogs = $blogs->where('b.name', 'like', "%$request->name%");
        }

        if ($request->body == !null) {
            $blogs = $blogs->where('b.body', 'like', "%$request->body%");
        }

        if ($request->status == !null) {
            $blogs = $blogs->where('b.status', $request->status);
        }

        if ($request->category_id == !null) {
            $blogs = $blogs->where('b.category_id', $request->category_id);
        }

        $total = $blogs->count();

        $blogs = $blogs->limit($limit)->offset($offset)->get();

        return response([
            "message" => "Blogs retrieved successfully",
            "data" => [
                'total' => $total,
                'blogs' => $blogs
            ]
        ],200);
    }

    public function show($id,Request $request)
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
            ->where('status',1)
            ->where('b.id',$id)
            ->with('photos')
            ->first();


        if(!$blog){
            return response([
                'message' => "Blog not found",
                'data' => null
            ], 404);
        }

        // Giriş etmiş istifadəçinin verdiyi stardır.
        $starAuthUser = BlogStar::query()
            ->where('blog_id',$id)
            ->where('user_id',$request->user()->id)
            ->first();

        $blog->starByMe = $starAuthUser->star ?? null;

        // Ümumi ortalama və User sayi

        $avgStars = BlogStar::query()
            ->select(
                DB::raw('AVG(star) as avarage'),
                DB::raw('COUNT(id) as total_user_count')
            )
            ->where('blog_id',$id)
            ->first();

        $blog->avarageOfStars = round($avgStars?->avarage,2);
        $blog->totalUserCount = $avgStars?->total_user_count;


        // Like and Dislike

        $likedByMe = BlogLike::query()
            ->where('blog_id',$id)
            ->where('user_id',$request->user()->id)
            ->first();

        $blog->likeByMe = $likedByMe?->is_like;

        $likeAndDislikeCount = BlogLike::query()
            ->select(
                DB::raw('SUM(is_like) as like_count'),
                DB::raw('COUNT(id) - SUM(is_like) as dislike_count')
            )
            ->where('blog_id',$id)
            ->first();

        $blog->like_count = (int)$likeAndDislikeCount->like_count;
        $blog->dislike_count = (int)$likeAndDislikeCount->dislike_count;

        return response([
            'message' => 'Blog retrieved',
            'data' => $blog
        ], 200);
    }

    public function rateStar($id, Request $request)
    {
        $blog = Blog::query()
            ->where('status',1)
            ->where('id',$id)
            ->whereNull('deleted_at')
            ->first();

        if(!$blog){
            return response([
                'message' => "Blog not found",
                'data' => null
            ], 404);
        }

        $checkStar = BlogStar::query()
            ->where('blog_id',$id)
            ->where('user_id',$request->user()->id)
            ->first();

        if($checkStar){
            $checkStar->update([
                'star' =>$request->star
            ]);
        } else{
            BlogStar::query()
                ->create([
                    'user_id' =>$request->user()->id,
                    'blog_id' =>$id,
                    'star' =>$request->star
                ]);
        }
        return response([
            'message' => 'Star rated',
            'data' => null
        ], 201);


    }

    public function like($id, Request $request)
    {
        /*
        * Request is_like 1 ve 0 gelir. 1- like 0- dislike
        * Eger like ve dislike onceden movcud deyilse create et.
        * Eger like movcuddursa ve like gelirse(request) like sileceyik.
        * Eger dislike movcuddursa ve dilike gelirse(request) dislike sileceyik.
        * Eger like movcuddursa ve dislike gelirse, ve ya dislike movcuddursa like gelirse update et.
       */

        $blog = Blog::query()
            ->where('status',1)
            ->where('id',$id)
            ->whereNull('deleted_at')
            ->first();

        if(!$blog){
            return response([
                'message' => "Blog not found",
                'data' => null
            ], 404);
        }

        $checkUserLikeOrDislike = BlogLike::query()
            ->where('blog_id',$id)
            ->where('user_id',$request->user()->id)
            ->first();

        if(!$checkUserLikeOrDislike){
            BlogLike::query()
                    ->create([
                        'blog_id' =>$id,
                        'user-id' =>$request->user()->id,
                        'is_like' =>$request->is_like
                    ]);
        }
        elseif ($checkUserLikeOrDislike->is_like == $request->is_like){
            $checkUserLikeOrDislike->delete();
        }
        else{
            $checkUserLikeOrDislike->update([
                'is_like' =>$request->is_like
            ]);
        }

        return response([
            'message' => 'Blog liked',
            'data' => null
        ], 201);
    }

}
