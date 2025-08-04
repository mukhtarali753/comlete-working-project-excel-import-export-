<?php

namespace App\Http\Controllers;

use App\Exports\StudentsExport;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::all();
        $selectedTab = $request->input('tab', Session::get('selected_tab', 'users'));

        return view('students.index', compact('students', 'selectedTab'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email',
            'dob' => 'nullable|date',
            'age' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:255',
        ]);

        Student::create($validated);
        Session::put('selected_tab', $request->input('tab', 'users'));

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }



    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email,' . $id,
            'dob' => 'nullable|date',
            'age' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
            'course' => 'nullable|string|max:255',
        ]);

        $student->update($validated);
        Session::put('selected_tab', $request->input('tab', 'users'));

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }




    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        Session::put('selected_tab', 'users');

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }




    public function savePreview(Request $request)
{
    $type = $request->input('type');
    $data = $request->input('data', []);

    if ($type === 'users' && is_array($data)) {
        foreach ($data as $row) {
           
            if (empty($row['name']) && empty($row['email'])) {
                continue;
            }

            
            if (!empty($row['id'])) {
                $student = Student::find($row['id']);
                if ($student) {
                    $student->update([
                        'name' => $row['name'] ?? '',
                        'email' => $row['email'] ?? '',
                        'dob' => $row['dob'] ?? null,
                        'age' => $row['age'] ?? null,
                        'address' => $row['address'] ?? '',
                        'course' => $row['course'] ?? '',
                    ]);
                }
            } else {
                
                Student::create([
                    'name' => $row['name'] ?? '',
                    'email' => $row['email'] ?? '',
                    'dob' => $row['dob'] ?? null,
                    'age' => $row['age'] ?? null,
                    'address' => $row['address'] ?? '',
                    'course' => $row['course'] ?? '',
                ]);
            }
        }
    }

    Session::put('selected_tab', $type);
    return redirect()->route('students.index')->with('success', 'Data updated successfully.');
}




    public function download()
    {
        return Excel::download(new StudentsExport, 'StudentData.xlsx');
    }
}