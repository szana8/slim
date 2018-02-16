<?php

namespace App\Http\Controllers;

use App\Category;
use App\Issue;
use Illuminate\Http\Request;

class IssuesController extends Controller
{
    /**
     * IssuesController constructor.
     */
    public function __construct()
    {
        $this->middleware(['web','auth'])->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Category $category
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category)
    {
        if ($category->exists) {
            $issues = $category->issues()->latest()->get();
        }
        else {
            $issues = Issue::latest()->get();
        }

        return view('issue.index', compact('issues'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('issue.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'summary' => 'required',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id'
        ]);

        $issue = Issue::create([
            'user_id' => auth()->id(),
            'category_id' => request('category_id'),
            'summary' => request('summary'),
            'description' => request('description')
        ]);

        return redirect($issue->path());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function show($category, Issue $issue)
    {
        return view('issue.show', compact('issue'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function edit(Issue $issue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Issue $issue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Issue  $issue
     * @return \Illuminate\Http\Response
     */
    public function destroy(Issue $issue)
    {
        //
    }
}
