<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::query()->orderBy('name');

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return ClientResource::collection($query->get());
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:clients,name'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        $client = Client::create($request->all());

        ActivityLogService::logCreated($client, $request);

        return response()->json(new ClientResource($client), 201);
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client): JsonResponse
    {
        return response()->json(new ClientResource($client));
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, Client $client): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:clients,name,' . $client->id],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $oldAttributes = $client->getAttributes();
        $client->update($request->all());

        ActivityLogService::logUpdated($client, $oldAttributes, $request);

        return response()->json(new ClientResource($client));
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Request $request, Client $client): JsonResponse
    {
        // Check if there are projects linked to this client
        if ($client->projects()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer un client lié à des projets.'
            ], 422);
        }

        ActivityLogService::logDeleted($client, $request);
        $client->delete();

        return response()->json(null, 204);
    }
}
