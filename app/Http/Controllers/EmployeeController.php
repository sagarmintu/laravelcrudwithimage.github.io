<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('id', 'ASC')->paginate(5);
        return view("employee.list", ['employees' => $employees]);
    }

    public function create()
    {
        return view("employee.create");
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required',
            'image' => 'sometimes|image:gif,png,jpg.jpeg'
        ]);

        if($validator->passes())
        {
            //Option-01

            // $employee = new Employee();
            // $employee->name = $request->name;
            // $employee->email = $request->email;
            // $employee->address = $request->address;
            // $employee->save();

            //Option-02

            $employee = new Employee();
            $employee->fill($request->post())->save();

            //Option-03
            //Employee::create($request->post())->save();

            // Upload Image
            if($request->image)
            {
                $ext = $request->image->getClientOriginalExtension();
                $newFileName = time().'.'.$ext;
                $request->image->move(public_path().'/uploads/employees/',$newFileName); // This will save the image inside the folder
                $employee->image = $newFileName;
                $employee->save();
            }

            return redirect()->route('employees.index')->with('success', 'Employee Created Sucessfully !!!');
        }
        else
        {
            // return with error
            return redirect()->route('employees.create')->withErrors($validator)->withInput();
        }
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        // dd($employee);
        return view("employee.edit", ['employee' => $employee]);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required',
            'image' => 'sometimes|image:gif,png,jpg.jpeg'
        ]);

        if($validator->passes())
        {
            $employee = Employee::find($id);
            $employee->name = $request->name;
            $employee->email = $request->email;
            $employee->address = $request->address;
            $employee->save();

            if($request->image)
            {
                $oldImage = $request->image;
                $ext = $request->image->getClientOriginalExtension();
                $newFileName = time().'.'.$ext;
                $request->image->move(public_path().'/uploads/employees/',$newFileName); // This will save the image inside the folder
                $employee->image = $newFileName;
                $employee->save();

                File::delete(public_path().'/uploads/employees/'.$oldImage);
            }

            return redirect()->route('employees.index')->with('success', 'Employee Updated Sucessfully !!!');
        }
        else
        {
            // return with error
            return redirect()->route('employees.edit', $id)->withErrors($validator)->withInput();
        }
    }

    public function destroy($id, Request $request)
    {
        $employee = Employee::findOrFail($id);
        File::delete(public_path().'/uploads/employees/'.$employee->image);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee Deleted Sucessfully !!!');
    }
}