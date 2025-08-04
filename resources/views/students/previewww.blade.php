@extends('layouts.theme')

@section('content')
<style>
    .excel-table {
        border-collapse: collapse;
        width: 100%;
        font-family: Calibri, sans-serif;
        font-size: 14px;
    }

    .excel-table th, .excel-table td {
        border: 1px solid #d0d7de;
        padding: 8px 12px;
        text-align: left;
    }

    .excel-table thead {
        background-color: #f3f6f9;
        font-weight: bold;
    }

    .excel-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .excel-table tbody tr:hover {
        background-color: #e6f7ff;
    }

    .excel-wrapper {
        overflow-x: auto;
    }
</style>

<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold uppercase tracking-wide">User Table - Excel Style Preview</h1>
            <a href="{{ route('students.download') }}" class="bg-white text-green-600 font-bold px-4 py-2 rounded hover:bg-green-100">
                Download
            </a>
        </div>

        <div class="p-6 excel-wrapper">
            <h2 class="text-lg font-semibold mb-4">1. User Data</h2>

            <h3 class="font-medium mb-2">Address Data</h3>
            <table class="excel-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Date of Birth</th>
                        <th>Age</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->dob->format('Y-m-d') }}</td>
                        <td>{{ $student->age }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex justify-end mt-6">
                <a href="{{ route('students.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Next
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
