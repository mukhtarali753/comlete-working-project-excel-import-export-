@extends('layouts.theme')

@section('title', 'Student Data')

@section('content')

<div class="container mx-auto p-4">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Student Table</h2>

        <div class="flex justify-end mb-4 space-x-2">
            <button onclick="openAddModal()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                <i class="fas fa-plus mr-2"></i>Add Student
            </button>



            <button onclick="openPreviewModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-eye mr-2"></i>Preview Excel
            </button>



            {{-- <a href="{{ route('students.download') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-download mr-2"></i>Download
            </a> --}}
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow rounded">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-center">D-O-B</th>
                        <th class="px-4 py-2 text-center">Age</th>
                        <th class="px-4 py-2 text-center">Address</th>
                        <th class="px-4 py-2 text-center">Course</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        {{-- <tr class="border-b" data-id="{{ $student->id }}"> --}}
                            <td class="px-4 py-2">{{ $student->id }}</td>
                            <td class="px-4 py-2">{{ $student->name }}</td>
                            <td class="px-4 py-2">{{ $student->email }}</td>
                            <td class="px-4 py-2">{{ $student->dob }}</td>
                            <td class="px-4 py-2 text-center">{{ $student->age }}</td>
                            <td class="px-4 py-2 text-center">{{ $student->address }}</td>
                            <td class="px-4 py-2 text-center">{{ $student->course }}</td>
                            <td class="px-4 py-2 text-center">
                               
                               
                             <button onclick="openEditModal('{{ $student->id }}', '{{ addslashes($student->name) }}', '{{ addslashes($student->email) }}', '{{ $student->dob }}', '{{ $student->age }}', '{{ addslashes($student->address) }}', '{{ addslashes($student->course) }}')" class="text-green-500 hover:text-green-700">
                                    <i class="fas fa-edit"></i>
                             </button>


                                
                             
                             <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 ml-2" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>



                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ======================= Add Student Modal ======================= --}}
<div id="addStudentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-11/12 max-w-xl p-6 h-[400px] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Add Student</h2>
            <button onclick="closeAddModal()" class="text-gray-600 hover:text-black text-xl font-bold">close ×</button>
        </div>
        <form action="{{ route('students.store') }}" method="POST" class="space-y-3">
            @csrf

            
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input name="name" class="w-full border rounded p-2 text-sm" required>
            </div>


            <div>
                <label class="block text-sm font-medium">Email</label>
                <input name="email" type="email" class="w-full border rounded p-2 text-sm" required>
            </div>


            <div>
                <label class="block text-sm font-medium">Date of Birth</label>
                <input name="dob" type="date" class="w-full border rounded p-2 text-sm">
            </div>
            

            <div>
                <label class="block text-sm font-medium">Age</label>
                <input name="age" type="number" class="w-full border rounded p-2 text-sm">
            </div>


            <div>
                <label class="block text-sm font-medium">Address</label>
                <input name="address" class="w-full border rounded p-2 text-sm">
            </div>


            <div>
                <label class="block text-sm font-medium">Course</label>
                <input name="course" class="w-full border rounded p-2 text-sm">
            </div>


            <div class="flex justify-end">
                <button type="submit" class="bg-green-500 text-white px-4 py-1.5 rounded hover:bg-green-600 text-sm">Save</button>
            </div>


        </form>
    </div>
</div>

{{-- ======================= Edit Student Modal ======================= --}}
<div id="editStudentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
   
    <div class="bg-white rounded-lg w-11/12 max-w-xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Edit Student</h2>
            <button onclick="closeEditModal()" class="text-gray-600 hover:text-black text-xl font-bold">close ×</button>
        </div>


        <form id="editStudentForm" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            
            
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input name="name" id="edit-name" class="w-full border rounded p-2">
            </div>


            <div>
                <label class="block text-sm font-medium">Email</label>
                <input name="email" id="edit-email" class="w-full border rounded p-2">
            </div>


            <div>
                <label class="block text-sm font-medium">Date of Birth</label>
                <input name="dob" id="edit-dob" type="date" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium">Age</label>
                <input name="age" id="edit-age" type="number" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium">Address</label>
                <input name="address" id="edit-address" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium">Course</label>
                <input name="course" id="edit-course" class="w-full border rounded p-2">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Update</button>
            </div>
            
        </form>
    </div>
</div>

{{-- ======================= Preview Modal ======================= --}}
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-11/12 max-w-6xl">
        <div class="bg-gray-600 text-white p-4 flex justify-between items-center">
            <div><i class="fas fa-file-excel mr-2"></i>Excel Preview</div>
            <div>
                <a href="{{ route('students.download') }}" class="text-white mr-2"><i class="fas fa-download"></i></a>
                <button onclick="closePreviewModal()" class="text-white"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="p-4 excel-scroll">
            <div class="excel-box">
                <div class="flex justify-between mb-4">
                    <div>
                        <button id="delete-user-rows" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 hidden">
                            <i class="fas fa-trash mr-2"></i>Delete Selected
                        </button>
                    </div>
                    <button onclick="savePreview('users')" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        <i class="fas fa-save mr-2"></i>Save
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-users"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date of Birth</th>
                            <th>Age</th>
                            <th>Address</th>
                            <th>Course</th>
                        </tr>
                    </thead>
                    <tbody id="user-rows">
                        @foreach ($students as $student)
                            <tr>
                                <td><input type="checkbox" class="select-row" data-id="{{ $student->id }}"></td>
                                <td><input name="data[{{ $loop->index }}][id]" value="{{ $student->id }}" readonly></td>
                                <td><input name="data[{{ $loop->index }}][name]" value="{{ $student->name }}"></td>
                                <td><input name="data[{{ $loop->index }}][email]" value="{{ $student->email }}"></td>
                                <td><input name="data[{{ $loop->index }}][dob]" value="{{ $student->dob }}"></td>
                                <td>
                                    <input name="data[{{ $loop->index }}][age]" value="{{ $student->age }}"
                                           class="{{ $student->age < 18 ? 'bg-red-200' : '' }}">
                                </td>
                                <td><input name="data[{{ $loop->index }}][address]" value="{{ $student->address }}"></td>
                                <td><input name="data[{{ $loop->index }}][course]" value="{{ $student->course }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ======================= Scripts ======================= --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#select-all-users').change(function () {
            $('#user-rows .select-row').prop('checked', this.checked);
            updateDeleteButton('user');
        });

        $(document).on('change', '.select-row', function () {
            updateDeleteButton('user');
        });

        $('#add-user-row').click(function () {
            const index = $('#user-rows tr').length;
            $('#user-rows').append(`
                <tr>
                    <td><input type="checkbox" class="select-row"></td>
                    <td><input name="data[${index}][id]" value="" readonly></td>
                    <td><input name="data[${index}][name]" value=""></td>
                    <td><input name="data[${index}][email]" value=""></td>
                    <td><input name="data[${index}][dob]" value=""></td>
                    <td><input name="data[${index}][age]" value=""></td>
                    <td><input name="data[${index}][address]" value=""></td>
                    <td><input name="data[${index}][course]" value=""></td>
                </tr>
            `);
        });

        $('#delete-user-rows').click(function () {
            $('#user-rows .select-row:checked').closest('tr').remove();
            updateDeleteButton('user');
        });

        function updateDeleteButton(type) {
            const count = $(`#${type}-rows .select-row:checked`).length;
            $(`#delete-${type}-rows`).toggle(count > 0).text(`Delete Selected (${count})`);
        }
    });

    function openAddModal() {
        $('#addStudentModal').removeClass('hidden');
    }

    function closeAddModal() {
        $('#addStudentModal').addClass('hidden');
    }

    function openEditModal(id, name, email, dob, age, address, course) {
        $('#editStudentForm').attr('action', '{{ url("students") }}/' + id);
        $('#edit-name').val(name);
        $('#edit-email').val(email);
        $('#edit-dob').val(dob);
        $('#edit-age').val(age);
        $('#edit-address').val(address);
        $('#edit-course').val(course);
        $('#editStudentModal').removeClass('hidden');
    }

    function closeEditModal() {
        $('#editStudentModal').addClass('hidden');
    }

    function openPreviewModal() {
        $('#previewModal').removeClass('hidden');
    }

    function closePreviewModal() {
        $('#previewModal').addClass('hidden');
    }

    function savePreview(type) {
        const form = $('<form>', {
            action: '{{ route("students.savePreview") }}',
            method: 'POST'
        }).append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        })).append($('<input>', {
            type: 'hidden',
            name: 'type',
            value: type
        }));

        $(`#${type}-rows tr`).each(function (index) {
            $(this).find('input').each(function () {
                form.append($('<input>', {
                    type: 'hidden',
                    name: `data[${index}][${$(this).attr('name').match(/\[([^\]]*)\]/)[1]}]`,
                    value: $(this).val()
                }));
            });
        });

        $('body').append(form);
        form.submit();
    }
</script>


<style>
    #previewModal table {
        border-collapse: collapse;
        width: 100%;
        font-size: 14px;
    }
    #previewModal th,
    #previewModal td {
        border: 1px solid #ccc;
        padding: 8px;
    }
    #previewModal th {
        background-color: #f0f0f0;
        font-weight: bold;
        text-align: center;
    }
    #previewModal tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .bg-red-200 {
    background-color: #fee2e2; /* Light red background for preview */
}
    #previewModal input {
        border: none;
        width: 100%;
        padding: 6px;
        background-color: transparent;
    }
    #previewModal input:focus {
        outline: 2px solid #2563eb;
        background-color: #fff;
    }
    #previewModal thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .excel-box {
        border-radius: 8px;
        overflow: hidden;
    }
    .excel-scroll {
        max-height: 500px;
        overflow-y: auto;
    }
</style>

@endsection
