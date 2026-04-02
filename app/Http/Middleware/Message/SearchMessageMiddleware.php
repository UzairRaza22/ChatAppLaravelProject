<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;

class SearchMessageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Validate search parameters
        $validator = validator($request->all(), [
            'query' => ['required', 'string', 'min:1', 'max:255'],
            'channel_id' => ['nullable', 'string', 'regex:/^[a-f\d]{24}$/i'],
            'workspace_id' => ['nullable', 'string', 'regex:/^[a-f\d]{24}$/i'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get validated parameters
        $params = [
            'query' => $request->get('query'),
            'channel_id' => $request->get('channel_id'),
            'workspace_id' => $request->get('workspace_id'),
            'per_page' => $request->get('per_page', 20),
            'page' => $request->get('page', 1),
        ];

        // If channel_id is provided, validate user membership
        if (!empty($params['channel_id'])) {
            $channel = \App\Models\Channel::where('_id', $params['channel_id'])->first();
            
            if (!$channel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Channel not found.',
                    'errors' => null,
                    'data' => null
                ], 404);
            }

            $user = $request->user();
            $userId = (string) $user->_id;
            $members = collect($channel->members ?? []);

            // Check if user is a member OR the creator of the channel
            $isCreator = (string) $channel->created_id === $userId;
            
            $senderIsMember = $isCreator || $members->contains(function ($member) use ($userId) {
                // Handle different member structures
                if (is_string($member)) {
                    return (string) $member === $userId;
                }
                if (is_array($member)) {
                    return (string) ($member['user_id'] ?? $member['_id'] ?? $member['id'] ?? '') === $userId;
                }
                if (is_object($member)) {
                    return (string) (data_get($member, 'user_id') ?? data_get($member, '_id') ?? data_get($member, 'id')) === $userId;
                }
                return false;
            });

            if (!$senderIsMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a member of this channel.',
                    'errors' => null,
                    'data' => null
                ], 403);
            }

            // Add channel to request for filtering
            $request->attributes->set('channel', $channel);
        }

        // Perform search using Scout
        $searchQuery = \App\Models\Message::search($params['query']);

        // Apply filters if provided
        if (!empty($params['channel_id'])) {
            $searchQuery->where('channel_id', $params['channel_id']);
        }

        if (!empty($params['workspace_id'])) {
            $searchQuery->where('workspace_id', $params['workspace_id']);
        }

        // Execute search with pagination
        $searchResults = $searchQuery->paginate($params['per_page'], 'page', $params['page']);

        // Load relationships for better response
        $searchResults->load(['sender', 'channel']);

        // Add search results to request for controller to use
        $request->merge([
            'search_results' => $searchResults,
            'search_params' => $params
        ]);

        return $next($request);
    }
}
