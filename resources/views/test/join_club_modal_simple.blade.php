@extends('layouts.app')

@section('title', 'Join Club Modal Example')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        Join Club Modal – Minimal Example
                    </h5>
                </div>

                <div class="card-body text-center">
                    <p class="mb-4">
                        This page demonstrates how any module can reuse
                        the <strong>Join Club modal</strong>.
                    </p>

                    {{-- 最关键的一行 --}}
                    <button
                        class="btn btn-success btn-lg"
                        onclick="window.openJoinClubModal(1)">
                        Request to Join Club (ID = 1)
                    </button>

                    <hr class="my-4">

                    <p class="text-muted mb-1">Current User:</p>
                    <p class="mb-0">
                        {{ auth()->user()->name ?? 'Guest' }}
                        ({{ auth()->user()->role ?? 'not logged in' }})
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- 引入 Join Club Modal（只需这一行） --}}
@include('clubs.join_modal')
@endsection
