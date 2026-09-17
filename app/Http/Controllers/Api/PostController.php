<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Jobs\EnviarAvisoPorCorreo;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return PostResource::collection(
            Post::query()->where('publicado', true)->latest()->paginate(10)
        );
    }

    public function show(Post $post)
    {
        return new PostResource($post);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'titulo' => ['required', 'max:120'],
            'contenido' => ['required'],
            'categoria_id' => ['required', 'exists:categorias,id'],
        ]);

        $datos['user_id'] = $request->user()->id;
        $post = Post::create($datos);

        EnviarAvisoPorCorreo::dispatch($post);

        return (new PostResource($post->fresh()))
            ->response()
            ->setStatusCode(201);
    }
}