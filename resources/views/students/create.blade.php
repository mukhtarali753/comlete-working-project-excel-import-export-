@extends('layouts.theme')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Add Student</h2>
    <form action="{{ route('students.store') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="tab" value="{{ Session::get('selected_tab', 'users') }}">
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input name="name" class="w-full border rounded p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input name="email" type="email" class="w-full border rounded p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Date of Birth</label>
            <input name="dob" type="date" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Age</label>
            <input name="age" type="number" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Address</label>
            <input name="address" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Course</label>
            <input name="course" class="w-full border rounded p-2">
        </div>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Save</button>
    </form>
</div>
@endsection