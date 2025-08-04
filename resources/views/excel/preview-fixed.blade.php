@extends('layouts.theme')

@section('content')
<div class="container mt-5">
    <div class="card shadow mx-auto" style="max-width: 1000px;">
        <div class="card-header bg-success text-white text-center">
            <h4 class="mb-0">excell Sheet Preview</h4>
        </div>

        <div class="card-body d-flex justify-content-center">
            <iframe 
                src="https://docs.google.com/spreadsheets/d/1i1Ld7aKiVfc8pxLsQruoFmBVhn4KeZk5/edit?usp=sharing&ouid=113861650165440238643&rtpof=true&sd=true"
                width="800" 
                height="500" 
                frameborder="0"
                style="border: 4px solid #ccc; border-radius: 8px;"
                allowfullscreen>
            </iframe>
        </div>
    </div>
</div>
@endsection
