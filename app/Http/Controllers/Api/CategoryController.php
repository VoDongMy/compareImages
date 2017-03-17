<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseController
{
    public function __construct(Request $request) {
      parent::__construct($request);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCategoies(Request $request)
    {
        $rules = [
            'limit' => 'regex:/^[0-9]+$/',
            'page' => 'regex:/^[0-9]+$/',
            'order_by' => 'in:asc,desc',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $orderBy = $request->has('order_by')? $request->limit : 'asc';
            $user = $this->user;
            if (empty($user)) {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => false,
                    'data' => $messages
                ], 200);
            }
            $categories = Category::orderBy('created_at', $orderBy);
            
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => (object)['total' => $categories->count(), 'items' => $categories->get()]
                    ], 200);
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function postCreate(Request $request)
    {
        $rules = [
            'name' => 'required|alpha',        
            ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $orderBy = $request->has('order_by')? $request->limit : 'asc';
            $user = $this->user;
            if (empty($user)) {
                $messages['error'] = 'User token is invalid.';
                return response()->json([
                    'status' => false,
                    'data' => $messages
                ], 200);
            }
            $category = new Category;
            $category->name = $request->name;
            $category->user_id = $user->id;
            $category->save();
            
            return $this->response([
                    'status_code' => 200,
                    'messages'    => 'request success',
                    'data'        => $category
                    ], 200);
        }
        return $this->response([
                    'status_code' => 400,
                    'messages'    => $validator->messages()->first(),
                    'data'        => array()
                    ], 400);    
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
