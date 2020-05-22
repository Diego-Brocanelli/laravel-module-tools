<?php
namespace Bnw\Skeleton\Http\Controllers;

class NomeController extends Controller
{
    public function show()
    {
        return view('skeleton::folder.test-view')->with([
            'title' => 'Teste Dois'
        ]);
    }
}