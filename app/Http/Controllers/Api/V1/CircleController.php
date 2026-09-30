<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCircleRequest;
use App\Http\Requests\UpdateCircleRequest;
use App\Http\Resources\CircleResource;
use App\Models\Circle;
use App\Support\GroupActivityWriter;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CircleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CircleResource::collection(
            Circle::query()->accessibleTo(request()->user())->latest('created_at')->paginate(25)
        );
    }

    public function store(StoreCircleRequest $request): CircleResource
    {
        $circle = Circle::query()->create([
            ...$request->validated(),
            'creator_id' => $request->user()->getKey(),
            'members' => array_values(array_unique([
                $request->user()->first_name ?: $request->user()->email,
                ...($request->validated('members') ?? []),
            ])),
            'member_user_ids' => array_values(array_unique([
                (string) $request->user()->getKey(),
                ...($request->validated('member_user_ids') ?? []),
            ])),
        ]);

        $this->recordCircleEvent($circle, 'Un cercle d’amis a été créé');

        return new CircleResource($circle);
    }

    public function show(string $id): CircleResource
    {
        return new CircleResource(Circle::query()->accessibleTo(request()->user())->findOrFail($id));
    }

    public function update(UpdateCircleRequest $request, string $id): CircleResource
    {
        $circle = Circle::query()->where('creator_id', $request->user()->getKey())->findOrFail($id);
        $circle->update($request->validated());

        $this->recordCircleEvent($circle, 'Le cercle d’amis a été modifié');

        return new CircleResource($circle->refresh());
    }

    public function destroy(string $id): Response
    {
        $circle = Circle::query()->where('creator_id', request()->user()->getKey())->findOrFail($id);
        $this->recordCircleEvent($circle, 'Le cercle a été supprimé');
        $circle->delete();

        return response()->noContent();
    }

    private function recordCircleEvent(Circle $circle, string $title): void
    {
        GroupActivityWriter::record($circle->member_user_ids ?? [(string) $circle->creator_id], [
            'category' => 'circle',
            'group_id' => (string) $circle->getKey(),
            'group_name' => $circle->name,
            'title' => $title,
            'description' => count($circle->members ?? []).' membre(s) dans le groupe.',
            'actor' => request()->user()->first_name ?: request()->user()->email,
            'icon' => 'group',
            'href' => '/circles',
        ]);
    }
}
