<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('key') ) {
            $user = $this->user;
            if (!$user) {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => false,
                    'data' => $messages
                ], 200);
            }
            $categories = Category::orderBy('created_at', 'ASC')->get(array('id', 'name'));
            if ($categories) {
                return response()->json([
                    'status' => true,
                    'data' => $categories
                ], 200);

            } else {
                $messages['error'] = 'User not found.';
                return response()->json([
                    'status' => false,
                    'data' => $messages
                ], 200);
            }
        }
        $messages['error'] = 'User not found.';
        return response()->json([
            'status' => false,
            'data'   => $messages
        ], 200); ;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
