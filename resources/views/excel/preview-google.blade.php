@extends('layouts.theme')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">📄 Google Sheets Embed Preview</h4>
        </div>
        <div class="card-body">
            <iframe 
                src="https://docs.google.com/spreadsheets/d/e/2PACX-1vRIiH76JEbXm6fHnmSkCZJZ84yw-PcwcDrd7ZmQaBFch-agUbqjKmpwCbV8_A_3-w/pubhtml"
                width="100%" 
                height="600px" 
                frameborder="0"
                allowfullscreen>
            </iframe>
        </div>
    </div>
</div>
@endsection
