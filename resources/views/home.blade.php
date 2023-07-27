@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="text-danger">
                WELCOME BACK,  {{ explode(' ', Auth::user()->name)[0] }}
            </h1>
            <h2 class="text-warning">Call Histories</h2>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Call ID</th>
                            <th>Caller Number</th>
                            <th>Session ID</th>
                            <th>Duration (seconds)</th>
                            <th>Hangup Cause</th>
                            <th>Recorded</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($callHistories as $history)
                        <tr>
                            <td>{{ $history->id }}</td>
                            <td>{{ $history->callerNumber }}</td>
                            <td>{{ $history->sessionId }}</td>
                            <td>{{ $history->durationInSeconds }}</td>
                            <td>{{ $history->hangupCause }}</td>
                            <td>{{ $history->recorded ? 'Yes' : 'No' }}</td>
                            <td>{{ $history->created_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            
        </div>
    </div>
</div>
@endsection


