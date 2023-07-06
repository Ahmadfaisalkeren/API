<?php

namespace App\Http\Controllers\API;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::orderBy('id', 'desc')->get();

        if ($students->count() > 0) {
            return response()->json(
                [
                    'status' => 200,
                    'students' => $students,
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'status' => 404,
                    'students' => 'No records found',
                ],
                404,
            );
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'course' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'required|digits:12',
            'image' => 'nullable|mimes:jpg,png,jpeg,svg|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 422,
                    'errors' => $validator->messages(),
                ],
                422,
            );
        } else {
            $imagePath = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('students', $imageName, 'public');
            }

            $student = Student::create([
                'name' => $request->name,
                'course' => $request->course,
                'email' => $request->email,
                'phone' => $request->phone,
                'image' => $imagePath,
            ]);

            if ($student) {
                return response()->json(
                    [
                        'status' => 200,
                        'message' => 'Student created successfully',
                    ],
                    200,
                );
            } else {
                return response()->json(
                    [
                        'status' => 500,
                        'message' => 'Something went wrong',
                    ],
                    500,
                );
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $student = Student::find($id);
        if ($student) {
            return response()->json(
                [
                    'status' => 200,
                    'student' => $student,
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'status' => 404,
                    'message' => 'No such student found',
                ],
                404,
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $student = Student::find($id);
        if ($student) {
            return response()->json(
                [
                    'status' => 200,
                    'student' => $student,
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'status' => 404,
                    'message' => 'No such student found',
                ],
                404,
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'course' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|digits:12',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:3000',
        ]);

        $student = Student::find($id);

        if ($student) {
            $oldImage = $student->image; // Retrieve the old image path

            // Update the student data
            $student->name = $request->name;
            $student->course = $request->course;
            $student->email = $request->email;
            $student->phone = $request->phone;

            // Handle the new image if provided
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($oldImage) {
                    Storage::delete($oldImage);
                }

                // Store the new image
                $image = $request->file('image');
                $path = $image->store('students', 'public');

                // Update the student's image field
                $student->image = $path;
            }

            $student->save();

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Student updated successfully',
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'status' => 500,
                    'message' => 'No such student found',
                ],
                500,
            );
        }
    }

    public function updateImage(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3000',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 422,
                    'errors' => $validator->messages(),
                ],
                422,
            );
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json(
                [
                    'status' => 404,
                    'message' => 'No such student found',
                ],
                404,
            );
        }

        $oldImage = $student->image; // Retrieve the old image path

        // Handle the new image if provided
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($oldImage) {
                Storage::delete($oldImage);
            }

            // Store the new image
            $image = $request->file('image');
            $path = $image->store('students', 'public');

            // Update the student's image field
            $student->image = $path;
            $student->save();

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Student image updated successfully',
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'status' => 422,
                    'errors' => ['image' => ['The image field is required.']],
                ],
                422,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student->delete();

            return response()->json(
                [
                    'status' => 200,
                    'message' => 'Student deleted successfully',
                ],
                200,
            );
        } else {
            return response()->json(
                [
                    'status' => 404,
                    'message' => 'No such student found!',
                ],
                404,
            );
        }
    }
}
