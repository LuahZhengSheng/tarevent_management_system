<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Services\ClubFacade;
use Illuminate\Http\Request;

class ClubApiController extends Controller
{
    /**
     * Create a new club.
     *
     * @param Request $request
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, ClubFacade $facade)
    {
        $club = $facade->createClub($request->all(), auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Club created successfully.',
            'data' => $club,
        ], 201);
    }

    /**
     * Request to join a club.
     *
     * @param Club $club
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestJoin(Club $club, ClubFacade $facade)
    {
        $facade->requestJoin($club, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Join request submitted successfully.',
        ], 200);
    }

    /**
     * Approve a join request.
     *
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveJoin(Club $club, User $user, ClubFacade $facade)
    {
        $facade->approveJoin($club, $user, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Join request approved successfully.',
        ], 200);
    }

    /**
     * Reject a join request.
     *
     * @param Club $club
     * @param User $user
     * @param ClubFacade $facade
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectJoin(Club $club, User $user, ClubFacade $facade)
    {
        $facade->rejectJoin($club, $user, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Join request rejected successfully.',
        ], 200);
    }
}

