@extends('layouts.app')

@section('title', 'Dashboard - TAREvent')

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>
                        {{ __('Dashboard') }}
                    </h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ __("You're logged in!") }}
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-calendar-event fs-1 text-primary mb-3"></i>
                                    <h5 class="card-title">Events</h5>
                                    <a href="{{ route('events.index') }}" class="btn btn-primary btn-sm">
                                        Browse Events
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="bi bi-chat-dots fs-1 text-info mb-3"></i>
                                    <h5 class="card-title">Forum</h5>
                                    <a href="{{ route('forums.index') }}" class="btn btn-info btn-sm">
                                        Visit Forum
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-circle fs-1 text-success mb-3"></i>
                                    <h5 class="card-title">Profile</h5>
                                    <a href="{{ route('profile.edit') }}" class="btn btn-success btn-sm">
                                        Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
