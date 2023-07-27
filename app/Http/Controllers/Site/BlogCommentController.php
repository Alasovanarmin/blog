<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogCommentController extends Controller
{
    public function index($id, Request $request)
    {
        $blog = Blog::query()
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->where('status', 1)
            ->first();

        if (!$blog) {
            return response([
                'message' => "Blog not found",
                'data' => null
            ], 404);
        }

        $limit = $request->limit ?? 10;
        $page = $request->page ?? 1;

        $offset = $limit * ($page - 1);

        $comments = BlogComment::query()
            ->from('blog_comments as bc')
            ->select(
                'bc.id',
                'bc.comment',
                'bc.created_at',
                'u.name as username',
                DB::raw(
                    "CASE WHEN u.id = ". $request->user()->id. " THEN 1 ELSE 0 END as can_edit"
                )
            )
            ->leftJoin('users as u','u.id','bc.user_id')
            ->where('blog_id', $id);

        $total = $comments->count();

        $comments = $comments->limit($limit)->offset($offset)
            ->get();

        return response([
            'message' => "Comment retrieved",
            'data' => [
                'total' => $total,
                'comments' => $comments,
            ]
        ]);
    }

    public function store($id, Request $request)
    {
        $blog = Blog::query()
            ->whereNull("deleted_at")
            ->where("status", 1)
            ->where("id", $id)
            ->first();

        if (!$blog) {
            return response([
                'message' => "Blog not found",
                'data' => null
            ], 404);
        }

        BlogComment::query()
            ->create([
                'user_id' =>$request->user()->id,
                'blog_id' =>$id,
                'comment' =>$request->comment
            ]);
        return response([
            'message' => "Comment created",
            'data' => null
        ]);
    }

    public function update($id, Request $request)
    {
        $comment = BlogComment::query()
            ->where('user_id', $request->user()->id)
            ->where('id',$id)
            ->first();

        if (!$comment) {
            return response([
                'message' => "Comment not found",
                'data' => null
            ], 404);
        }

        $comment->update([
            'comment' => $request->comment
        ]);

        return response([
            "message" => "Comment updated",
            "data" => null
        ], 200);
    }

    public function delete($id,Request $request)
    {
        $comment = BlogComment::query()
            ->where('user_id',$request->user()->id)
            ->where('id',$id)
            ->first();

        if (!$comment) {
            return response([
                'message' => "Comment not found",
                'data' => null
            ], 404);
        }

        $comment->delete();

        return response([
            'message' => "Comment deleted",
            'data' => null
        ]);
    }
}
