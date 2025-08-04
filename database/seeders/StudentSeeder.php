<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $students = [
            [
                'name' => 'Ali Khan',
                'email' => 'ali@example.com',
                'dob' => '2000-01-15',
                'age' => 25,
                'address' => 'Lahore, Pakistan',
                'course' => 'Computer Science',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Sara Ahmed',
                'email' => 'sara@example.com',
                'dob' => '1999-07-20',
                'age' => 26,
                'address' => 'Karachi, Pakistan',
                'course' => 'Information Technology',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Umar Riaz',
                'email' => 'umar@example.com',
                'dob' => '2001-05-10',
                'age' => 23,
                'address' => 'Islamabad, Pakistan',
                'course' => 'Software Engineering',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Fatima Noor',
                'email' => 'fatima@example.com',
                'dob' => '1998-12-25',
                'age' => 27,
                'address' => 'Multan, Pakistan',
                'course' => 'Data Science',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Zain Ali',
                'email' => 'zain@example.com',
                'dob' => '2002-03-30',
                'age' => 22,
                'address' => 'Peshawar, Pakistan',
                'course' => 'Artificial Intelligence',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        Student::insert($students);
    }
}
