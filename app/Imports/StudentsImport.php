<?php
namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Throwable;

class StudentsImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation, SkipsOnError
{
    private $rowCount = 0;
    private $errors = [];

    public function model(array $row)
    {
        $this->rowCount++;

        if (empty($row['email'])) {
            $this->errors[] = "Row {$this->rowCount}: Email is required";
            return null;
        }

        if (Student::where('email', $row['email'])->exists()) {
            $this->errors[] = "Row {$this->rowCount}: Duplicate email - {$row['email']}";
            return null;
        }

        return new Student([
            'name' => $row['name'] ?? 'Unnamed',
            'email' => $row['email'],
            'age' => is_numeric($row['age'] ?? null) ? $row['age'] : null,
            'address' => $row['address'] ?? 'Not provided',
        ]);
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required|string',
            '*.email' => 'required|email',
            '*.age' => 'nullable|numeric',
            '*.address' => 'nullable|string',
        ];
    }

    public function onError(Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function headingRow(): int
    {
        return 1;
    }
}