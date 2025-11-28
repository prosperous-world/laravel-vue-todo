<?php

namespace App\Http\Controllers;

use App\Http\Requests\Todo\StoreTodoRequest;
use App\Http\Requests\Todo\UpdateTodoRequest;
use App\Models\Tag;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TodoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $todos = Todo::with('tags')
            ->where('user_id', $user->id)
            ->search($request->query('search'))
            ->status($request->query('status'))
            ->byTag($request->query('tag'))
            ->dueBetween($request->query('due_from'), $request->query('due_to'))
            ->sortByParam($request->query('sort'))
            ->paginate(10);

        return response()->json($todos);
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validated();
        $data['user_id'] = $user->id;

        $todo = Todo::create($data);

        if (!empty($data['tags'])) {
            $tags = $this->syncTags($data['tags']);
            $todo->tags()->sync($tags);
        }

        return response()->json($todo->load('tags'), 201);
    }

    public function show(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($request, $todo);

        return response()->json($todo->load('tags'));
    }

    public function update(UpdateTodoRequest $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($request, $todo);

        $data = $request->validated();

        $todo->update($data);

        if (array_key_exists('tags', $data)) {
            $tags = $this->syncTags($data['tags'] ?? []);
            $todo->tags()->sync($tags);
        }

        return response()->json($todo->load('tags'));
    }

    public function destroy(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($request, $todo);

        $todo->delete();

        return response()->json([], 204);
    }

    public function toggle(Request $request, Todo $todo): JsonResponse
    {
        $this->authorizeTodo($request, $todo);

        $todo->update([
            'completed' => !$todo->completed,
        ]);

        return response()->json($todo->fresh('tags'));
    }

    protected function authorizeTodo(Request $request, Todo $todo): void
    {
        abort_unless($todo->user_id === $request->user()->id, 403);
    }

    protected function syncTags(array $tags): array
    {
        $ids = [];

        foreach ($tags as $name) {
            $slug = Str::slug($name);

            $tag = Tag::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );

            $ids[] = $tag->id;
        }

        return $ids;
    }
}

