<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner; //Eloquent
use Illuminate\Support\Facades\DB; //クエリビルダ
use Carbon\Carbon;
use SebastianBergmann\CodeCoverage\Driver\Driver;

class OwnersController extends Controller
{

    
    //コントローラ側でのガード設定
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {   
        $date_now = Carbon::now();
        $date_parse = Carbon::parse(now());
        echo $date_now->year;
        echo $date_parse;
        

        // Eloquent/Collection
        $e_all = Owner::all();
        // Support/Collection
        $q_get = DB::table('owners')->select('name', 'created_at')->get();
        //stdClass
        // $q_first = DB::table('owners')->select('name')->first();
        // Support/Collection
        // $c_test = collect([
        //     'name' => 'テスト'
        // ]);

        // var_dump($q_first); 
        // dd($e_all, $q_get, $q_first, $c_test);


        return view('admin.owners.index', compact('e_all', 'q_get'));
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
