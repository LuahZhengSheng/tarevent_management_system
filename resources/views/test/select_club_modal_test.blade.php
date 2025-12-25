@extends('layouts.app')

@section('title', 'Select Club Modal Test')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-people-fill me-2"></i>
                        Select Club Modal Test
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-4">
                        This page demonstrates the <strong>Select Club Modal</strong> functionality.
                        Click the button below to open the modal and select a club to join.
                    </p>

                    <div class="d-grid gap-2">
                        <button
                            class="btn btn-success btn-lg"
                            onclick="window.openSelectClubModal(function(clubId) {
                                console.log('Join request submitted for club:', clubId);
                                alert('Join request submitted for club ID: ' + clubId);
                            })">
                            <i class="bi bi-people me-2"></i>
                            Open Select Club Modal
                        </button>
                    </div>

                    <hr class="my-4">

                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle me-2"></i>
                            Current User Info
                        </h6>
                        <p class="mb-0">
                            <strong>Name:</strong> {{ auth()->user()->name ?? 'Guest' }}<br>
                            <strong>Role:</strong> {{ auth()->user()->role ?? 'not logged in' }}<br>
                            <strong>User ID:</strong> {{ auth()->id() ?? 'N/A' }}
                        </p>
                    </div>

                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Features
                        </h6>
                        <ul class="mb-0">
                            <li>List all clubs with join status</li>
                            <li>Search clubs by name or description</li>
                            <li>Status badges: Available, Member, Pending, Rejected</li>
                            <li>3-day cooldown for rejected requests</li>
                            <li>Select club and submit join request</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include Select Club Modal --}}
@include('clubs.select_club_modal')
@endsection

