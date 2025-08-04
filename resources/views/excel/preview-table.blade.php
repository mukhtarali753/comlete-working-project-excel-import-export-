@extends('layouts.theme')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Excel Sheet Preview (as Table)</h4>
        </div>

        <div class="card-body">
            @if(count($rows))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle">
                        <tbody>
                            @foreach($rows as $rowIndex => $row)
                                <tr class="{{ $rowIndex === 0 ? 'fw-bold bg-light' : '' }}">
                                    @foreach($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning"> Excel file is empty or unreadable.</div>
            @endif
        </div>
    </div>
</div>
@endsection
