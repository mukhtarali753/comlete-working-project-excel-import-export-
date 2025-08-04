@extends('layouts.theme')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Edit Student</h2>
    <form action="{{ route('students.update', $student->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="tab" value="{{ Session::get('selected_tab', 'users') }}">
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input name="name" value="{{ $student->name }}" class="w-full border rounded p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input name="email" type="email" value="{{ $student->email }}" class="w-full border rounded p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Date of Birth</label>
            <input name="dob" type="date" value="{{ $student->dob }}" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Age</label>
            <input name="age" type="number" value="{{ $student->age }}" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Address</label>
            <input name="address" value="{{ $student->address }}" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Course</label>
            <input name="course" value="{{ $student->course }}" class="w-full border rounded p-2">
        </div>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Update</button>
    </form>
</div>
@endsection