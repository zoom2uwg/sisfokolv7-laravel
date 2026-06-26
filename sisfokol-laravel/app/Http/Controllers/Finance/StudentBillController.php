<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;

class StudentBillController extends Controller
{
    public function index()
    {
        $bills = StudentBill::with(['student', 'paymentItem'])->latest()->paginate(20);

        return view('finance.student-bills.index', compact('bills'));
    }

    public function create()
    {
        $academicYears = AcademicYear::all();
        $students = Student::all();
        $paymentItems = PaymentItem::all();

        return view('finance.student-bills.create', compact('academicYears', 'students', 'paymentItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:tahun_ajaran,id',
            // [2026-06-25 | AI-Agent] Update students -> siswa
            'student_id' => 'required|exists:siswa,id',
            'payment_item_id' => 'required|exists:payment_items,id',
            'period' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        StudentBill::create([
            ...$request->only('academic_year_id', 'student_id', 'payment_item_id', 'period', 'amount', 'due_date'),
            'remaining' => $request->amount,
            'status' => 'unpaid',
        ]);

        return redirect()->route('finance.student-bills.index')->with('success', 'Tagihan berhasil ditambahkan.');
    }

    public function destroy(StudentBill $studentBill)
    {
        $studentBill->delete();

        return redirect()->route('finance.student-bills.index')->with('success', 'Tagihan berhasil dihapus.');
    }
}

